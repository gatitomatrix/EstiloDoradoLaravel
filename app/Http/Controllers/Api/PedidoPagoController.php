<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\PedidoEstadoHistorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ComprobanteService;
use App\Services\Envio\TarifaEnvio;

class PedidoPagoController extends Controller
{
    
    public function index(Request $request)
{
    $user = $request->user();

    $pedidos = \App\Models\Pedido::where('id_cliente', $user->id_cliente)
        ->with(['detalles.producto'])
        ->orderByDesc('id_pedido')
        ->get();

    $list = $pedidos->map(function ($p) {
        $det = $p->detalles;
        $first = $det->first();
        $firstName = $first?->producto?->nombre ?? ('#' . ($first?->id_producto ?? '—'));
        $extra = max(0, $det->count() - 1);
        $label = $extra > 0 ? "{$firstName} (+{$extra})" : $firstName;

        $tipo  = $p->comprobante_tipo;
        $serie = $p->comprobante_serie;
        $num8  = str_pad((string)$p->comprobante_numero, 8, '0', STR_PAD_LEFT);
        $emitido = $this->comprobanteEmitido($p);

        return [
            'id_pedido'         => $p->id_pedido,
            'fecha_pedido'      => optional($p->fecha_pedido)->format('Y-m-d H:i:s'),
            'estado'            => $p->estado,
            'total'             => $p->total,
            'forma_pago'        => $p->forma_pago,
            'direccion_entrega' => $p->direccion_entrega,
            'producto_label'    => $label,
            'comprobante_tipo'  => $emitido ? $tipo : null,
            'comprobante_serie' => $emitido ? $serie : null,
            'comprobante_numero'=> $emitido ? $p->comprobante_numero : null,
            'friendly'          => $emitido && $serie && $p->comprobante_numero ? "{$serie}-{$num8}" : null,
        ];
    });

    return response()->json($list);
    }
    
    
    public function confirmar(Request $request)
    {
        $data = $request->validate([
    'forma_pago'        => 'required|in:tarjeta,yape,efectivo',
    'culqi_id'          => 'required_if:forma_pago,tarjeta,yape|string',
    'direccion_entrega' => 'nullable|string',
    'items'             => 'required|array|min:1',
    'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
    'items.*.cantidad'    => 'required|integer|min:1',
    'comprobante'       => 'nullable|in:BO,FA,bo,fa',
    'factura'           => 'nullable|array',
    'boleta'            => 'nullable|array',
         ]);


        $user = $request->user();

        // =========================
        // MODO EFECTIVO (retiro en tienda)
        // =========================
        if ($data['forma_pago'] === 'efectivo') {
            return DB::transaction(function () use ($data, $user) {

                // Crear pedido SIN comprobante (usamos valores neutros para evitar NOT NULL)
                $pedido = new Pedido();
                $pedido->id_cliente         = $user->id_cliente;
                $pedido->fecha_pedido       = now();
                $pedido->estado             = 'pendiente'; // se pagará en tienda
                $pedido->total              = 0;
                $pedido->forma_pago         = 'efectivo';
                $pedido->culqi_id           = null;
                $pedido->direccion_entrega  = $data['direccion_entrega'] ?? null;

                // Evitar error 1364 si tus columnas son NOT NULL
                $pedido->comprobante_tipo   = 'EF';
                $pedido->comprobante_serie  = 'EF00';
                $pedido->comprobante_numero = 0;

                $pedido->save();

                // Detalles + total
                $total = 0;
                foreach ($data['items'] as $it) {
                    $prod   = Producto::findOrFail($it['id_producto']);
                    $precio = $prod->precio_venta;

                    DetallePedido::create([
                        'id_pedido'       => $pedido->id_pedido,
                        'id_producto'     => $prod->id_producto,
                        'cantidad'        => $it['cantidad'],
                        'precio_unitario' => $precio,
                    ]);

                    $total += $precio * $it['cantidad'];
                }
                $pedido->total = $total;
                $pedido->save();

                $pedido->load('detalles.producto');

                // Respuesta (sin comprobante)
                return response()->json($this->pedidoPayload($pedido), 201);
            });
        }

        // =========================
        // MODO CULQI (tarjeta/yape) – emite 1 comprobante (FA o BO)
        // =========================

        $hasFA = !empty($data['factura']);
        $hasBO = !empty($data['boleta']);

        if (!$hasFA && !$hasBO) {
            return response()->json([
                'success' => false,
                'message' => 'Debes ingresar datos de FACTURA o BOLETA para emitir el comprobante.',
            ], 422);
        }

        if ($hasFA && $hasBO) {
            $sel = strtoupper((string)($data['comprobante'] ?? ''));
            if (!in_array($sel, ['FA','BO'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Has enviado datos de FACTURA y BOLETA. Indica en "comprobante" si quieres emitir FA o BO.',
                ], 422);
            }
            if ($sel === 'FA') unset($data['boleta']);
            if ($sel === 'BO') unset($data['factura']);
            $hasFA = isset($data['factura']);
            $hasBO = isset($data['boleta']);
        }

        $tipoElegido = $hasFA ? 'FA' : 'BO';
        if (!empty($data['comprobante'])) {
            $tipoElegido = strtoupper($data['comprobante']) === 'FA' ? 'FA' : 'BO';
        }

        $dir = (string) ($data['direccion_entrega'] ?? '');
        $esRetiro = str_contains(mb_strtoupper($dir), 'RETIRO');
        if ($dir !== '' && ! $esRetiro && ! TarifaEnvio::cubre(null, null, $dir)) {
            return response()->json([
                'message' => 'Por ahora el envío a domicilio solo cubre Lima, Callao, Junín (Huancayo) y Pasco (Cerro de Pasco). Puedes retirar en tienda.',
            ], 422);
        }

        return DB::transaction(function () use ($data, $user, $tipoElegido) {

            // Serie & número antes del insert para evitar NOT NULL
            $serie  = $tipoElegido === 'FA' ? 'F001' : 'B001';
            $numero = $this->nextNumero($serie);

            $pedido = new Pedido();
            $pedido->id_cliente         = $user->id_cliente;
            $pedido->fecha_pedido       = now();
            $pedido->estado             = 'pagado';
            $pedido->total              = 0;
            $pedido->forma_pago         = $data['forma_pago']; // tarjeta|yape
            $pedido->culqi_id           = $data['culqi_id'];
            $pedido->direccion_entrega  = $data['direccion_entrega'] ?? null;

            $pedido->comprobante_tipo   = $tipoElegido;
            $pedido->comprobante_serie  = $serie;
            $pedido->comprobante_numero = $numero;
            $pedido->save();

            // Detalles + total
            $total = 0;
            foreach ($data['items'] as $it) {
                $prod   = Producto::findOrFail($it['id_producto']);
                $precio = $prod->precio_venta;

                DetallePedido::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'id_producto'     => $prod->id_producto,
                    'cantidad'        => $it['cantidad'],
                    'precio_unitario' => $precio,
                ]);

                $total += $precio * $it['cantidad'];
            }
            $pedido->total = $total;
            $pedido->save();

            // Emisión SUNAT: no tumbar el pedido si falla (timeout/cert)
            try {
                /** @var ComprobanteService $svc */
                $svc = app(ComprobanteService::class);
                $res = $svc->emitir($pedido, $data);
                $pedido->sunat_pdf = $res['pdf'] ?? null;
                $pedido->sunat_xml = $res['xml'] ?? null;
                $pedido->sunat_cdr = $res['cdr'] ?? null;
                $pedido->save();
            } catch (\Throwable $e) {
                \Log::warning('[confirmar] emitir CPE falló pedido '.$pedido->id_pedido.': '.$e->getMessage());
            }

            $pedido->load('detalles.producto');

            return response()->json($this->pedidoPayload($pedido), 201);
        });
    }

    public function show($id, Request $request)
    {
        $user = $request->user();
        $p = Pedido::where('id_pedido', $id)
            ->where('id_cliente', $user->id_cliente)
            ->with('detalles.producto')
            ->firstOrFail();

        return response()->json($this->pedidoPayload($p));
    }

    /**
     * POST /api/pedidos/{id}/cancelar
     */
    public function cancelar($id, Request $request)
    {
        $data = $request->validate([
            'motivo' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($id, $user, $data) {
            $pedido = Pedido::where('id_pedido', $id)
                ->where('id_cliente', $user->id_cliente)
                ->with('detalles')
                ->lockForUpdate()
                ->firstOrFail();

            if (strtolower((string) $pedido->estado) !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar pedidos pendientes de pago.',
                    'estado'  => $pedido->estado,
                ], 422);
            }

            $anterior = $pedido->estado;
            $pedido->estado = 'cancelado';
            $pedido->save();

            // Restaurar stock: pedidos viejos (POST /pedidos) descontaban stock.
            $restaurar = false;
            try {
                $restaurar = PedidoEstadoHistorial::where('id_pedido', $pedido->id_pedido)
                    ->where('comentario', 'like', '%aplicación móvil%')
                    ->exists();
            } catch (\Throwable $e) {
                $restaurar = false;
            }

            if (!$restaurar && $pedido->comprobante_tipo !== 'EF') {
                if ($pedido->comprobante_tipo === 'BO' && (int) $pedido->comprobante_numero === 0) {
                    $restaurar = true;
                }
            }
            // efectivo de confirmar (EF) no descontó stock → no restaurar
            if ($pedido->comprobante_tipo === 'EF') {
                $restaurar = false;
            }

            if ($restaurar) {
                foreach ($pedido->detalles as $d) {
                    Producto::where('id_producto', $d->id_producto)
                        ->increment('stock', (int) $d->cantidad);
                }
            }

            try {
                PedidoEstadoHistorial::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'estado_anterior' => $anterior,
                    'estado_nuevo'    => 'cancelado',
                    'fecha'           => now(),
                    'comentario'      => $data['motivo'] ?? 'Cancelado por el cliente',
                ]);
            } catch (\Throwable $e) {
                // no bloquear cancelación si falla el historial
            }

            $pedido->load('detalles.producto');

            $payload = $this->pedidoPayload($pedido);
            $payload['success'] = true;
            $payload['message'] = 'Pedido cancelado';
            return response()->json($payload);
        });
    }

    /**
     * POST /api/pedidos/{id}/pagar
     * Completa el pago de un pedido pendiente (yape/tarjeta) y emite comprobante.
     */
    public function pagar($id, Request $request)
    {
        $data = $request->validate([
            'forma_pago'  => 'required|in:tarjeta,yape',
            'culqi_id'    => 'required|string',
            'comprobante' => 'nullable|in:BO,FA,bo,fa',
            'factura'     => 'nullable|array',
            'boleta'      => 'nullable|array',
        ]);

        $hasFA = !empty($data['factura']);
        $hasBO = !empty($data['boleta']);
        if (!$hasFA && !$hasBO) {
            return response()->json([
                'success' => false,
                'message' => 'Debes ingresar datos de FACTURA o BOLETA.',
            ], 422);
        }

        $tipoElegido = $hasFA ? 'FA' : 'BO';
        if (!empty($data['comprobante'])) {
            $tipoElegido = strtoupper($data['comprobante']) === 'FA' ? 'FA' : 'BO';
        }
        if ($tipoElegido === 'FA' && !$hasFA) {
            return response()->json(['success' => false, 'message' => 'Faltan datos de factura'], 422);
        }
        if ($tipoElegido === 'BO' && !$hasBO) {
            return response()->json(['success' => false, 'message' => 'Faltan datos de boleta'], 422);
        }

        $user = $request->user();

        return DB::transaction(function () use ($id, $user, $data, $tipoElegido) {
            $pedido = Pedido::where('id_pedido', $id)
                ->where('id_cliente', $user->id_cliente)
                ->with('detalles.producto')
                ->lockForUpdate()
                ->firstOrFail();

            if (strtolower((string) $pedido->estado) !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pedido ya no está pendiente de pago.',
                    'estado'  => $pedido->estado,
                ], 422);
            }

            if (strtolower((string) $pedido->forma_pago) === 'efectivo') {
                return response()->json([
                    'success' => false,
                    'message' => 'Los pedidos en efectivo se pagan en tienda.',
                ], 422);
            }

            $serie  = $tipoElegido === 'FA' ? 'F001' : 'B001';
            $numero = $this->nextNumero($serie);

            $pedido->forma_pago         = $data['forma_pago'];
            $pedido->culqi_id           = $data['culqi_id'];
            $pedido->estado             = 'pagado';
            $pedido->comprobante_tipo   = $tipoElegido;
            $pedido->comprobante_serie  = $serie;
            $pedido->comprobante_numero = $numero;
            $pedido->save();

            try {
                /** @var ComprobanteService $svc */
                $svc = app(ComprobanteService::class);
                $res = $svc->emitir($pedido, $data);
                $pedido->sunat_pdf = $res['pdf'] ?? null;
                $pedido->sunat_xml = $res['xml'] ?? null;
                $pedido->sunat_cdr = $res['cdr'] ?? null;
                $pedido->save();
            } catch (\Throwable $e) {
                // Si falla SUNAT, igual dejamos pagado con serie/número
            }

            try {
                PedidoEstadoHistorial::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'estado_anterior' => 'pendiente',
                    'estado_nuevo'    => 'pagado',
                    'fecha'           => now(),
                    'comentario'      => 'Pago completado por el cliente (app móvil)',
                ]);
            } catch (\Throwable $e) {
            }

            $pedido->load('detalles.producto');
            $payload = $this->pedidoPayload($pedido);
            $payload['success'] = true;
            $payload['message'] = 'Pago registrado';
            return response()->json($payload);
        });
    }

    // ----------------- helpers -----------------

    private function nextNumero(string $serie): int
    {
        $last = DB::table('pedidos')
            ->where('comprobante_serie', $serie)
            ->selectRaw('COALESCE(MAX(CAST(comprobante_numero AS UNSIGNED)),0) as n')
            ->value('n');
        return (int)$last + 1;
    }

    /** Comprobante real emitido (no placeholders BO-00000000 de pedidos pendientes). */
    private function comprobanteEmitido(Pedido $p): bool
    {
        $tipo = strtoupper((string) $p->comprobante_tipo);
        if (!in_array($tipo, ['FA', 'BO'], true)) {
            return false;
        }
        if ((int) $p->comprobante_numero <= 0) {
            return false;
        }
        $estado = strtolower((string) $p->estado);
        if (!in_array($estado, ['pagado', 'enviado', 'entregado', 'completado'], true)) {
            return false;
        }
        return true;
    }

    private function pedidoPayload(Pedido $p): array
    {
        $tipo  = $p->comprobante_tipo;
        $serie = $p->comprobante_serie;
        $num8  = str_pad((string)$p->comprobante_numero, 8, '0', STR_PAD_LEFT);
        $friendly = "{$serie}-{$num8}";
        $emitido = $this->comprobanteEmitido($p);

        $pdfUrl = null;
        $xmlUrl = null;
        $cdrUrl = null;
        if ($emitido) {
            try {
                $pdfUrl = $p->sunat_pdf ? route('fe.pdf', ['tipo' => $tipo, 'serie' => $serie, 'name' => "{$friendly}.pdf"]) : null;
                $xmlUrl = $p->sunat_xml ? route('fe.xml', ['tipo' => $tipo, 'serie' => $serie, 'name' => "{$friendly}.xml"]) : null;
                $cdrUrl = $p->sunat_cdr ? route('fe.cdr', ['tipo' => $tipo, 'name'  => "R-{$friendly}.zip"]) : null;
            } catch (\Throwable $e) {
                $pdfUrl = $p->sunat_pdf;
                $xmlUrl = $p->sunat_xml;
                $cdrUrl = $p->sunat_cdr;
            }
        }

        return [
            'id_pedido'         => $p->id_pedido,
            'fecha_pedido'      => $p->fecha_pedido?->format('Y-m-d H:i:s'),
            'estado'            => $p->estado,
            'total'             => $p->total,
            'forma_pago'        => $p->forma_pago,
            'direccion_entrega' => $p->direccion_entrega,

            'sunat_pdf' => $pdfUrl,
            'sunat_xml' => $xmlUrl,
            'sunat_cdr' => $cdrUrl,

            'comprobante' => $emitido ? [
                'tipo'   => $tipo,
                'serie'  => $serie,
                'numero' => $p->comprobante_numero,
                'pdf'    => $pdfUrl,
                'xml'    => $xmlUrl,
                'cdr'    => $cdrUrl,
            ] : null,

            'detalles' => $p->detalles->map(function ($d) {
                return [
                    'id_producto'     => $d->id_producto,
                    'producto'        => $d->producto?->nombre,
                    'cantidad'        => $d->cantidad,
                    'precio_unitario' => $d->precio_unitario,
                    'subtotal'        => $d->cantidad * $d->precio_unitario,
                ];
            }),
        ];
    }
}
