<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\Asistente\WhatsappEscalation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AsistenteAdminController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('asistente_logs')) {
            return response()->json([
                'items' => [],
                'stats' => ['total' => 0, 'sin_producto' => 0, 'whatsapp' => 0],
                'max_id' => 0,
                'nuevos' => [],
            ]);
        }

        $q = DB::table('asistente_logs');
        $maxId = (int) (clone $q)->max('id');

        $after = (int) $request->query('after_id', 0);
        $nuevos = [];
        if ($after > 0) {
            $nuevos = (clone $q)->where('id', '>', $after)->orderBy('id')->limit(20)->get()
                ->map(fn ($r) => $this->present($r));
        }

        $items = DB::table('asistente_logs')->orderByDesc('id')->limit(80)->get()
            ->map(fn ($r) => $this->present($r));

        return response()->json([
            'stats' => [
                'total' => (clone $q)->count(),
                'sin_producto' => (clone $q)->where('tipo', 'sin_producto')->count(),
                'whatsapp' => (clone $q)->where('whatsapp', 1)->count(),
            ],
            'items' => $items,
            'max_id' => $maxId,
            'nuevos' => $nuevos,
        ]);
    }

    private function present(object $r): object
    {
        $r->created_at = Carbon::parse($r->created_at)
            ->timezone('America/Lima')
            ->format('d/m/Y H:i');
        $tipo = (string) ($r->queja_tipo ?? '');
        $r->queja_label = $tipo !== '' ? (new WhatsappEscalation)->quejaLabel($tipo) : null;
        $r->productos_items = $this->parseProductos($r);
        $cel = preg_replace('/\D+/', '', (string) ($r->celular ?? '')) ?? '';
        if (strlen($cel) < 9 && preg_match('/\b(9\d{8})\b/', (string) ($r->mensaje ?? ''), $hit)) {
            $cel = $hit[1];
            $r->celular = $r->celular ?: $cel;
        }
        if (strlen($cel) === 9 && str_starts_with($cel, '9')) {
            $cel = '51'.$cel;
        }
        $r->wa_url = strlen($cel) >= 11 ? 'https://wa.me/'.$cel : null;
        $r->celular_fmt = $r->celular ?? null;

        return $r;
    }

    private function parseProductos(object $r): array
    {
        $raw = $r->productos_json ?? null;
        if (is_string($raw) && str_starts_with(trim($raw), '[')) {
            $j = json_decode($raw, true);
            if (is_array($j)) {
                return array_values(array_filter($j, fn ($x) => is_array($x) && ! empty($x['nombre'])));
            }
        }
        $txt = trim((string) ($r->productos ?? ''));
        if ($txt === '') {
            return [];
        }
        $out = [];
        foreach (preg_split('/,\s*/', $txt) ?: [] as $n) {
            if ($n !== '') {
                $out[] = ['id' => 0, 'nombre' => $n];
            }
        }

        return $out;
    }

    public function interes()
    {
        $consultas = [];
        if (Schema::hasTable('asistente_logs') && Schema::hasColumn('asistente_logs', 'productos_json')) {
            $q = DB::table('asistente_logs')->whereNotNull('productos_json');
            if (Schema::hasColumn('asistente_logs', 'tipo')) {
                $q->where(function ($w) {
                    $w->whereNull('tipo')->orWhereNotIn('tipo', ['whatsapp', 'queja_espera']);
                });
            }
            $rows = $q->orderByDesc('id')->limit(800)->get(['productos_json']);
            foreach ($rows as $r) {
                $j = json_decode((string) $r->productos_json, true);
                if (! is_array($j)) {
                    continue;
                }
                $seen = [];
                foreach ($j as $p) {
                    if (! is_array($p)) {
                        continue;
                    }
                    $id = (int) ($p['id'] ?? 0);
                    if ($id < 1 || isset($seen[$id])) {
                        continue;
                    }
                    $seen[$id] = true;
                    $consultas[$id] = ($consultas[$id] ?? 0) + 1;
                }
            }
        }

        $likes = [];
        $dislikes = [];
        $adds = [];
        if (Schema::hasTable('asistente_feedback')) {
            $fb = DB::table('asistente_feedback')
                ->select('id_producto', 'voto', DB::raw('COUNT(*) as c'))
                ->groupBy('id_producto', 'voto')
                ->get();
            foreach ($fb as $row) {
                $id = (int) $row->id_producto;
                $c = (int) $row->c;
                if ($row->voto === 'up') {
                    $likes[$id] = $c;
                } elseif ($row->voto === 'down') {
                    $dislikes[$id] = $c;
                } elseif ($row->voto === 'add') {
                    $adds[$id] = $c;
                }
            }
        }

        $ids = array_values(array_unique(array_merge(
            array_keys($consultas),
            array_keys($likes),
            array_keys($adds)
        )));
        $prods = $ids
            ? Producto::query()->whereIn('id_producto', $ids)->get()->keyBy('id_producto')
            : collect();

        $items = [];
        foreach ($ids as $id) {
            $p = $prods->get($id);
            $items[] = [
                'id' => $id,
                'nombre' => $p?->nombre ?? ('Producto #'.$id),
                'imagen_url' => $p?->imagen_url,
                'precio' => $p ? (float) $p->precio_venta : null,
                'stock' => $p ? (int) $p->stock : null,
                'consultas' => (int) ($consultas[$id] ?? 0),
                'likes' => (int) ($likes[$id] ?? 0),
                'dislikes' => (int) ($dislikes[$id] ?? 0),
                'carritos' => (int) ($adds[$id] ?? 0),
            ];
        }
        usort($items, function ($a, $b) {
            if ($b['consultas'] !== $a['consultas']) {
                return $b['consultas'] <=> $a['consultas'];
            }

            return $b['likes'] <=> $a['likes'];
        });

        return response()->json([
            'stats' => [
                'productos' => count($items),
                'consultas' => array_sum($consultas),
                'likes' => array_sum($likes),
                'carritos' => array_sum($adds),
            ],
            'items' => $items,
        ]);
    }

    public function fichaCliente(int $id)
    {
        $c = Cliente::query()->find($id);
        if (! $c) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        $pedidos = Pedido::query()
            ->with(['detalles.producto'])
            ->where('id_cliente', $id)
            ->whereNotIn('estado', ['pendiente', 'cancelado'])
            ->orderByDesc('id_pedido')
            ->limit(5)
            ->get();

        $n = Pedido::query()
            ->where('id_cliente', $id)
            ->whereNotIn('estado', ['pendiente', 'cancelado'])
            ->count();
        $gasto = (float) Pedido::query()
            ->where('id_cliente', $id)
            ->whereNotIn('estado', ['pendiente', 'cancelado'])
            ->sum('total');
        $ultimo = $pedidos->first();

        $list = [];
        foreach ($pedidos as $p) {
            $items = [];
            foreach ($p->detalles as $d) {
                $prod = $d->producto;
                $items[] = [
                    'nombre' => $prod?->nombre ?? 'Ítem',
                    'cantidad' => (int) ($d->cantidad ?? 1),
                    'imagen_url' => $prod?->imagen_url,
                ];
            }
            $list[] = [
                'id_pedido' => (int) $p->id_pedido,
                'fecha' => optional($p->fecha_pedido)?->timezone('America/Lima')->format('d/m/Y H:i') ?: '',
                'total' => number_format((float) $p->total, 2, '.', ''),
                'estado' => $p->estado,
                'items' => $items,
            ];
        }

        $nombre = trim(($c->nombre ?? '').' '.($c->apellido ?? ''));

        return response()->json([
            'id_cliente' => (int) $c->id_cliente,
            'nombre' => $nombre !== '' ? $nombre : 'Cliente',
            'email' => $c->email,
            'telefono' => $c->telefono ?? null,
            'n_pedidos' => $n,
            'total_gastado' => number_format($gasto, 2, '.', ''),
            'ultimo_pedido' => $ultimo
                ? optional($ultimo->fecha_pedido)?->timezone('America/Lima')->format('d/m/Y')
                : null,
            'frecuente' => $n >= 3,
            'pedidos' => $list,
        ]);
    }
}
