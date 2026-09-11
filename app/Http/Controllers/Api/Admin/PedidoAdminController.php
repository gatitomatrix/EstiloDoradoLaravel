<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PedidoAdminController extends Controller
{
    public function index(Request $request)
    {
        $per  = (int)($request->input('per_page', 10));
        $page = (int)($request->input('page', 1));

        $q = Pedido::query()
            ->leftJoin('clientes as c', 'c.id_cliente', '=', 'pedidos.id_cliente')
            ->select([
                'pedidos.*',
                DB::raw("TRIM(CONCAT(COALESCE(c.nombre,''),' ',COALESCE(c.apellido,''))) as cliente_nombre"),
                DB::raw('c.telefono as cliente_telefono'),
            ])
            ->orderByDesc('pedidos.id_pedido');

        if ($request->filled('cliente')) {
            $cliente = trim((string)$request->input('cliente'));
            $like = '%'.$cliente.'%';
            $q->where(function ($w) use ($like) {
                $w->whereRaw("TRIM(CONCAT(COALESCE(c.nombre,''),' ',COALESCE(c.apellido,''))) LIKE ?", [$like])
                  ->orWhere('c.nombre', 'like', $like)
                  ->orWhere('c.apellido', 'like', $like);
            });
        }

        if ($request->filled('estado')) {
            $q->where('pedidos.estado', $request->input('estado'));
        }

        if ($request->filled('forma_pago')) {
            $q->where('pedidos.forma_pago', $request->input('forma_pago'));
        }

        if ($request->filled('fecha_desde')) {
            $q->whereDate('pedidos.fecha_pedido', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $q->whereDate('pedidos.fecha_pedido', '<=', $request->input('fecha_hasta'));
        }

        if ($per <= 0) {
            $rows = $q->get()->map(fn($p) => $this->decorateRow($p));
            $this->attachItems($rows);
            $this->attachFechaEstado($rows);
            $this->attachCoords($rows);
            return response()->json([
                'data' => $rows,
                'meta' => ['total' => $rows->count()],
            ]);
        }

        $p = $q->paginate($per, ['*'], 'page', $page);
        $p->getCollection()->transform(fn($row) => $this->decorateRow($row));
        $this->attachItems($p->getCollection());
        $this->attachFechaEstado($p->getCollection());
        $this->attachCoords($p->getCollection());

        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'page'        => $p->currentPage(),
                'per_page'    => $p->perPage(),
                'total'       => $p->total(),
                'total_pages' => $p->lastPage(),
            ],
        ]);
    }

    public function novedades(Request $request)
    {
        $after = max(0, (int) $request->input('after_id', 0));
        $maxId = (int) (Pedido::query()->max('id_pedido') ?? 0);
        $pendientes = (int) Pedido::query()->where('estado', 'pendiente')->count();
        $nuevos = [];
        if ($after > 0) {
            $nuevos = Pedido::query()
                ->leftJoin('clientes as c', 'c.id_cliente', '=', 'pedidos.id_cliente')
                ->select([
                    'pedidos.id_pedido',
                    'pedidos.estado',
                    'pedidos.total',
                    'pedidos.forma_pago',
                    'pedidos.fecha_pedido',
                    DB::raw("TRIM(CONCAT(COALESCE(c.nombre,''),' ',COALESCE(c.apellido,''))) as cliente_nombre"),
                ])
                ->where('pedidos.id_pedido', '>', $after)
                ->orderBy('pedidos.id_pedido')
                ->limit(25)
                ->get()
                ->map(fn ($p) => [
                    'id_pedido' => (int) $p->id_pedido,
                    'estado' => $p->estado,
                    'total' => (float) $p->total,
                    'forma_pago' => $p->forma_pago,
                    'fecha_pedido' => $p->fecha_pedido,
                    'cliente_nombre' => $p->cliente_nombre,
                    'event' => 'pedido.created',
                ])
                ->all();
        }
        return response()->json([
            'max_id' => $maxId,
            'pendientes' => $pendientes,
            'nuevos' => $nuevos,
            'driver_hint' => 'poll',
        ]);
    }

    public function show($id)
    {
        $p = Pedido::with(['cliente','detalles.producto','historial'])->find($id);
        if (!$p) return response()->json(['message'=>'No encontrado'],404);
        $p->cliente_nombre = trim(($p->cliente->nombre ?? '').' '.($p->cliente->apellido ?? ''));
        $c = \App\Support\Celular::contactoPedido($p, $p->cliente?->telefono ?? null);
        $p->telefono_contacto = $c['telefono_contacto'];
        $p->celular_fmt = $c['celular_fmt'];
        $p->wa_url = $c['wa_url'];
        $urls = $this->buildComprobanteUrls($p);
        $p->pdf_url = $urls['pdf'] ?? null;
        $p->xml_url = $urls['xml'] ?? null;
        $p->cdr_url = $urls['cdr'] ?? null;
        $this->attachItems(collect([$p]));
        $this->attachFechaEstado(collect([$p]));
        $this->attachCoords(collect([$p]));
        return $p;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cliente'         => 'required|integer|exists:clientes,id_cliente',
            'fecha_pedido'       => 'nullable|date',
            'estado'             => 'required|in:pendiente,pagado,enviado,entregado,cancelado',
            'total'              => 'nullable|numeric|min:0',
            'forma_pago'         => 'nullable|in:tarjeta,yape,efectivo',
            'direccion_entrega'  => 'nullable|string',
            'comprobante_tipo'   => 'nullable|in:FA,BO,EF',
            'comprobante_serie'  => 'nullable|string|max:10',
            'comprobante_numero' => 'nullable|integer|min:0',
            'detalles'                   => 'required|array|min:1',
            'detalles.*.id_producto'     => 'required|integer|exists:productos,id_producto',
            'detalles.*.cantidad'        => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);
        $pedido = DB::transaction(function () use ($data) {
            $p = new Pedido();
            $p->id_cliente         = $data['id_cliente'];
            $p->fecha_pedido       = $data['fecha_pedido'] ?? now();
            $p->estado             = $data['estado'];
            $p->total              = 0;
            $p->forma_pago         = $data['forma_pago'] ?? null;
            $p->direccion_entrega  = $data['direccion_entrega'] ?? null;
            $p->comprobante_tipo   = $data['comprobante_tipo'] ?? null;
            $p->comprobante_serie  = $data['comprobante_serie'] ?? null;
            $p->comprobante_numero = $data['comprobante_numero'] ?? null;
            $p->sunat_xml          = null;
            $p->sunat_pdf          = null;
            $p->sunat_cdr          = null;
            $p->save();
            $total = 0.0;
            foreach ($data['detalles'] as $d) {
                $prod = Producto::find($d['id_producto']);
                $precio = array_key_exists('precio_unitario', $d) && $d['precio_unitario'] !== null
                    ? (float)$d['precio_unitario']
                    : (float)$prod->precio_venta;
                $cant = (int)$d['cantidad'];
                $sub  = $precio * $cant;
                DetallePedido::create([
                    'id_pedido'       => $p->id_pedido,
                    'id_producto'     => $prod->id_producto,
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                ]);
                $total += $sub;
            }
            $p->total = round($total, 2);
            $p->save();
            return $p;
        });
        return response()->json($this->decorateRow(
            Pedido::leftJoin('clientes as c','c.id_cliente','=','pedidos.id_cliente')
                ->select(['pedidos.*', DB::raw("TRIM(CONCAT(COALESCE(c.nombre,''),' ',COALESCE(c.apellido,''))) as cliente_nombre")])
                ->where('pedidos.id_pedido',$pedido->id_pedido)->first()
        ), 201);
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'estado'                    => 'required|in:pendiente,pagado,enviado,entregado,cancelado',
            'forma_pago'                => 'nullable|in:tarjeta,yape,efectivo',
            'nota_admin'                => 'nullable|string|max:800',
            'telefono_contacto'         => 'nullable|string|max:20',
            'confirmar_cambio_celular'  => 'nullable|boolean',
        ]);
        $p = Pedido::with('detalles')->find($id);
        if (!$p) return response()->json(['message'=>'Pedido no encontrado'],404);
        $antes = strtolower((string) $p->estado);
        $despues = strtolower((string) $data['estado']);

        $actualCel = \App\Support\Celular::desdePedido($p);
        if (! $actualCel) {
            $cliTel = Cliente::where('id_cliente', $p->id_cliente)->value('telefono');
            $actualCel = \App\Support\Celular::deCliente($cliTel);
        }
        $nuevoCel = null;
        $cambiaCel = false;
        if (array_key_exists('telefono_contacto', $data) && trim((string) $data['telefono_contacto']) !== '') {
            $nuevoCel = \App\Support\Celular::deCliente($data['telefono_contacto']);
            if (! $nuevoCel) {
                return response()->json([
                    'message' => 'Celular inválido. 9 dígitos que empiecen con 9. No uses el WhatsApp de la tienda.',
                ], 422);
            }
            if ($nuevoCel !== $actualCel) {
                if (! $request->boolean('confirmar_cambio_celular')) {
                    return response()->json([
                        'message' => 'Marca la confirmación: el cliente pidió cambiar el celular de este pedido.',
                    ], 422);
                }
                $cambiaCel = true;
            }
        }

        try {
            DB::transaction(function () use ($p, $data, $antes, $despues, $cambiaCel, $actualCel, $nuevoCel) {
                $p->estado = $data['estado'];
                if (array_key_exists('forma_pago', $data)) {
                    $p->forma_pago = $data['forma_pago'];
                }
                $nota = trim((string) ($data['nota_admin'] ?? ''));
                if ($cambiaCel && $nuevoCel) {
                    $p->telefono_contacto = $nuevoCel;
                    $linea = 'Celular '.($actualCel ?: 'sin número').' → '.$nuevoCel.' (pedido del cliente).';
                    $nota = $nota === '' ? $linea : (str_contains($nota, $linea) ? $nota : $nota."\n".$linea);
                    if (mb_strlen($nota) > 800) {
                        $nota = mb_substr($nota, 0, 800);
                    }
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('pedidos', 'nota_admin')) {
                    $p->nota_admin = $nota !== '' ? $nota : null;
                }
                $p->save();
                if ($antes !== $despues) {
                    app(\App\Services\StockPedidoService::class)->aplicarCambioEstado($p, $antes, $despues);
                    \App\Models\PedidoEstadoHistorial::create([
                        'id_pedido'       => $p->id_pedido,
                        'estado_anterior' => $antes,
                        'estado_nuevo'    => $despues,
                        'fecha'           => now('America/Lima'),
                        'comentario'      => $nota !== '' ? $nota : 'Cambio desde panel de pedidos',
                    ]);
                }
            });
        } catch (InsufficientStockException $e) {
            $nombres = collect($e->detalles)->pluck('nombre')->filter()->implode(', ');
            return response()->json([
                'message' => 'No hay stock suficiente para reactivar este pedido'.($nombres ? ': '.$nombres : '.').' El estado no se cambio.',
                'detalles' => $e->detalles,
            ], 422);
        }
        $row = Pedido::leftJoin('clientes as c','c.id_cliente','=','pedidos.id_cliente')
            ->select([
                'pedidos.*',
                DB::raw("TRIM(CONCAT(COALESCE(c.nombre,''),' ',COALESCE(c.apellido,''))) as cliente_nombre"),
                DB::raw('c.telefono as cliente_telefono'),
            ])
            ->where('pedidos.id_pedido',$p->id_pedido)->first();
        $dec = $this->decorateRow($row);
        $this->attachItems(collect([$dec]));
        $this->attachFechaEstado(collect([$dec]));
        return response()->json($dec);
    }

    public function destroy($id)
    {
        return response()->json([
            'message' => 'Los pedidos no se eliminan. Marquelos como cancelado para liberar el stock.',
        ], 422);
    }

    public function comprobantes($id)
    {
        $p = Pedido::find($id);
        if (!$p) return response()->json(['message'=>'Pedido no encontrado'],404);
        $urls = $this->buildComprobanteUrls($p);
        return response()->json([
            'pdf' => $urls['pdf'] ?? null,
            'xml' => $urls['xml'] ?? null,
            'cdr' => $urls['cdr'] ?? null,
        ]);
    }

    public function descargarComprobante($id, Request $request)
    {
        $tipo = strtolower((string)$request->query('tipo','pdf'));
        if (!in_array($tipo, ['pdf','xml','cdr'])) {
            return response()->json(['message'=>'Tipo invalido'], 422);
        }
        $p = Pedido::find($id);
        if (!$p) return response()->json(['message'=>'Pedido no encontrado'],404);
        $urls = $this->buildComprobantePaths($p);
        $rel  = $urls[$tipo] ?? null;
        if (!$rel || !Storage::disk('public')->exists($rel)) {
            return response()->json(['message'=>strtoupper($tipo).' no disponible'],404);
        }
        $mime = $tipo === 'pdf' ? 'application/pdf' : ($tipo === 'xml' ? 'application/xml' : 'application/zip');
        return response(Storage::disk('public')->get($rel), 200, [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'inline; filename="'.basename($rel).'"',
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET,HEAD,OPTIONS',
            'Access-Control-Allow-Headers' => '*',
        ]);
    }

    private function decorateRow($row)
    {
        if (!$row) return $row;
        $urls = $this->buildComprobanteUrls($row);
        $row->pdf_url = $urls['pdf'] ?? null;
        $row->xml_url = $urls['xml'] ?? null;
        $row->cdr_url = $urls['cdr'] ?? null;
        $c = \App\Support\Celular::contactoPedido($row, $row->cliente_telefono ?? null);
        $row->telefono_contacto = $c['telefono_contacto'];
        $row->celular_fmt = $c['celular_fmt'];
        $row->wa_url = $c['wa_url'];
        return $row;
    }

    private function attachItems($rows): void
    {
        $list = collect($rows);
        $ids = $list->pluck('id_pedido')->filter()->all();
        if (! $ids) {
            return;
        }
        $by = DB::table('detalles_pedidos as d')
            ->join('productos as p', 'p.id_producto', '=', 'd.id_producto')
            ->leftJoin('categorias as c', 'c.id_categoria', '=', 'p.id_categoria')
            ->whereIn('d.id_pedido', $ids)
            ->select([
                'd.id_pedido',
                'd.id_producto',
                'd.cantidad',
                'p.nombre',
                'p.imagen_url',
                'c.nombre as categoria',
            ])
            ->get()
            ->groupBy('id_pedido');
        foreach ($list as $r) {
            $items = collect($by->get($r->id_pedido, collect()))->map(fn ($i) => [
                'id_producto' => (int) $i->id_producto,
                'nombre' => $i->nombre,
                'imagen_url' => $i->imagen_url,
                'categoria' => $i->categoria,
                'cantidad' => (int) $i->cantidad,
            ])->values();
            $r->items = $items;
            $first = $items->first();
            $extra = $items->count() - 1;
            $r->producto_label = $first
                ? ($first['nombre'].' x'.$first['cantidad'].($extra > 0 ? ' +'.$extra : ''))
                : '-';
        }
    }

    private function attachFechaEstado($rows): void
    {
        $list = collect($rows);
        $ids = $list->pluck('id_pedido')->filter()->all();
        if (! $ids) {
            return;
        }
        $last = DB::table('pedido_estado_historial')
            ->select('id_pedido', DB::raw('MAX(fecha) as fecha_estado'))
            ->whereIn('id_pedido', $ids)
            ->groupBy('id_pedido')
            ->get()
            ->keyBy('id_pedido');
        foreach ($list as $r) {
            $r->fecha_estado = optional($last->get($r->id_pedido))->fecha_estado;
        }
    }

    private function attachCoords($rows): void
    {
        foreach (collect($rows) as $r) {
            $lat = isset($r->lat_entrega) ? (float) $r->lat_entrega : 0.0;
            $lng = isset($r->lng_entrega) ? (float) $r->lng_entrega : 0.0;
            if ((abs($lat) < 0.01 || abs($lng) < 0.01) && ! empty($r->comprobantes_json)) {
                $j = json_decode((string) $r->comprobantes_json, true);
                if (is_array($j)) {
                    $lat = (float) ($j['lat'] ?? $lat);
                    $lng = (float) ($j['lng'] ?? $lng);
                }
            }
            $r->lat_entrega = abs($lat) > 0.01 ? $lat : null;
            $r->lng_entrega = abs($lng) > 0.01 ? $lng : null;
            $dir = mb_strtoupper((string) ($r->direccion_entrega ?? ''));
            $r->es_retiro = str_contains($dir, 'RETIRO') || str_contains($dir, 'RECOJO');
        }
    }

    private function buildComprobanteUrls($pedido): array
    {
        if (!$pedido) return [];
        $tipo = $pedido->comprobante_tipo;
        $serie = $pedido->comprobante_serie;
        $num   = (int)($pedido->comprobante_numero ?? 0);
        if (!in_array($tipo, ['FA','BO'], true) || !$serie || !$num) {
            return [];
        }
        $num8 = str_pad((string)$num, 8, '0', STR_PAD_LEFT);
        $friendly = "{$serie}-{$num8}";
        return [
            'pdf' => $pedido->sunat_pdf ? route('fe.pedido.file', ['id' => $pedido->id_pedido, 'kind' => 'pdf']) : null,
            'xml' => $pedido->sunat_xml ? route('fe.pedido.file', ['id' => $pedido->id_pedido, 'kind' => 'xml']) : null,
            'cdr' => $pedido->sunat_cdr ? route('fe.pedido.file', ['id' => $pedido->id_pedido, 'kind' => 'cdr']) : null,
        ];
    }

    private function buildComprobantePaths($pedido): array
    {
        if (!$pedido) return [];
        $tipo = $pedido->comprobante_tipo;
        $serie = $pedido->comprobante_serie;
        $num   = (int)($pedido->comprobante_numero ?? 0);
        if (!in_array($tipo, ['FA','BO'], true) || !$serie || !$num) {
            return [];
        }
        $num8 = str_pad((string)$num, 8, '0', STR_PAD_LEFT);
        $friendly = "{$serie}-{$num8}";
        return [
            'pdf' => "comprobantes/pdf/{$tipo}/{$serie}/{$friendly}.pdf",
            'xml' => "comprobantes/xml/{$tipo}/{$serie}/{$friendly}.xml",
            'cdr' => "comprobantes/cdr/{$tipo}/R-{$friendly}.zip",
        ];
    }
}
