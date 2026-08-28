<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReporteAdminController extends Controller
{
    public function __construct(private ReportExportService $export) {}

    public function clientes(string $ext, Request $request)
    {
        $headers = ['ID', 'Nombre', 'Apellido', 'Teléfono', 'Email', 'Dirección', 'Fecha registro'];
        $rows = Cliente::query()
            ->orderBy('id_cliente')
            ->get()
            ->map(fn ($c) => [
                $c->id_cliente,
                $c->nombre,
                $c->apellido,
                $c->telefono,
                $c->email,
                $c->direccion,
                optional($c->created_at)->format('Y-m-d H:i:s') ?? (string) $c->created_at,
            ])
            ->all();

        return $this->export->download(
            'reporte_clientes',
            'Reporte de clientes — Estilo Dorado',
            $headers,
            $rows,
            $ext
        );
    }

    public function productos(string $ext, Request $request)
    {
        $headers = ['ID', 'Nombre', 'Descripción', 'Precio compra', 'Precio venta', 'Stock', 'Estado'];
        $rows = Producto::query()
            ->orderBy('id_producto')
            ->get()
            ->map(fn ($p) => [
                $p->id_producto,
                $p->nombre,
                $p->descripcion,
                $p->precio_compra,
                $p->precio_venta,
                $p->stock,
                $p->estado,
            ])
            ->all();

        return $this->export->download(
            'reporte_productos',
            'Reporte de productos — Estilo Dorado',
            $headers,
            $rows,
            $ext
        );
    }

    public function pedidos(string $ext, Request $request)
    {
        $headers = ['ID', 'Cliente', 'Estado', 'Total', 'Forma de pago', 'Fecha', 'Dirección entrega'];
        $rows = Pedido::query()
            ->with('cliente')
            ->orderByDesc('id_pedido')
            ->get()
            ->map(function ($p) {
                $cliente = trim(($p->cliente->nombre ?? '').' '.($p->cliente->apellido ?? ''));

                return [
                    $p->id_pedido,
                    $cliente !== '' ? $cliente : ('#'.$p->id_cliente),
                    $p->estado,
                    $p->total,
                    $p->forma_pago,
                    optional($p->fecha_pedido)->format('Y-m-d H:i:s') ?? (string) $p->fecha_pedido,
                    $p->direccion_entrega,
                ];
            })
            ->all();

        return $this->export->download(
            'reporte_pedidos',
            'Reporte de pedidos — Estilo Dorado',
            $headers,
            $rows,
            $ext
        );
    }

    public function inventario(string $ext, Request $request)
    {
        $headers = ['ID', 'Producto', 'Tipo movimiento', 'Cantidad', 'Empleado', 'Fecha', 'Observación'];
        $rows = Inventario::query()
            ->with(['producto', 'empleado'])
            ->orderByDesc('id_movimiento')
            ->get()
            ->map(function ($m) {
                $fecha = $m->fecha
                    ?? $m->fecha_movimiento
                    ?? null;
                if ($fecha instanceof \DateTimeInterface) {
                    $fecha = $fecha->format('Y-m-d H:i:s');
                }

                return [
                    $m->id_movimiento,
                    $m->producto?->nombre,
                    $m->tipo_movimiento,
                    $m->cantidad,
                    $m->empleado?->nombre,
                    $fecha,
                    $m->observacion ?? '',
                ];
            })
            ->all();

        return $this->export->download(
            'reporte_inventario',
            'Reporte de inventario — Estilo Dorado',
            $headers,
            $rows,
            $ext
        );
    }

    /** Pedidos que ya representaron ingreso (no pendiente ni cancelado). */
    private const COBRADOS = ['pagado', 'enviado', 'entregado'];

    /**
     * Resumen financiero para el panel (KPIs + series). No altera listados existentes.
     */
    public function financiero(Request $request)
    {
        return response()->json($this->buildFinanciero($request));
    }

    public function financieroExport(string $ext, Request $request)
    {
        $data = $this->buildFinanciero($request);
        $headers = ['Concepto', 'Valor'];
        $rows = [
            ['Desde', $data['desde']],
            ['Hasta', $data['hasta']],
            ['Pedidos cobrados', $data['kpis']['pedidos_cobrados']],
            ['Ingresos (S/)', $data['kpis']['ingresos']],
            ['Ticket promedio (S/)', $data['kpis']['ticket_promedio']],
            ['Costo estimado (S/)', $data['kpis']['costo_estimado']],
            ['Margen estimado (S/)', $data['kpis']['margen_estimado']],
            ['Pedidos pendientes', $data['kpis']['pendientes']],
            ['Pedidos cancelados', $data['kpis']['cancelados']],
            ['Monto cancelado (S/)', $data['kpis']['monto_cancelado']],
        ];
        foreach ($data['por_pago'] as $p) {
            $rows[] = ['Pago: '.$p['metodo'].' ('.$p['pedidos'].' pedidos)', $p['total']];
        }
        foreach ($data['por_dia'] as $d) {
            $rows[] = ['Día '.$d['fecha'].' ('.$d['pedidos'].' pedidos)', $d['total']];
        }
        foreach ($data['top_productos'] as $p) {
            $rows[] = ['Top: '.$p['nombre'].' (und. '.$p['unidades'].')', $p['importe']];
        }

        return $this->export->download(
            'reporte_financiero',
            'Reporte financiero — Estilo Dorado',
            $headers,
            $rows,
            $ext
        );
    }

    public function ventasDia(string $ext, Request $request)
    {
        $data = $this->buildFinanciero($request);
        $rows = array_map(fn ($d) => [$d['fecha'], $d['pedidos'], $d['total']], $data['por_dia']);

        return $this->export->download(
            'reporte_ventas_dia',
            'Ventas por día — Estilo Dorado',
            ['Fecha', 'Pedidos', 'Total (S/)'],
            $rows,
            $ext
        );
    }

    public function formaPago(string $ext, Request $request)
    {
        $data = $this->buildFinanciero($request);
        $rows = array_map(fn ($p) => [$p['metodo'], $p['pedidos'], $p['total']], $data['por_pago']);

        return $this->export->download(
            'reporte_forma_pago',
            'Ventas por forma de pago — Estilo Dorado',
            ['Forma de pago', 'Pedidos', 'Total (S/)'],
            $rows,
            $ext
        );
    }

    public function topProductos(string $ext, Request $request)
    {
        $data = $this->buildFinanciero($request);
        $rows = array_map(
            fn ($p) => [$p['id'], $p['nombre'], $p['unidades'], $p['importe']],
            $data['top_productos']
        );

        return $this->export->download(
            'reporte_top_productos',
            'Productos que más facturaron — Estilo Dorado',
            ['ID', 'Producto', 'Unidades', 'Importe (S/)'],
            $rows,
            $ext
        );
    }

    /** Productos con stock ≤ umbral (default 10), para reposición. */
    public function stockBajo(string $ext, Request $request)
    {
        $threshold = (int) $request->query('threshold', 10);
        if ($threshold < 1 || $threshold > 100) {
            $threshold = 10;
        }

        $headers = ['ID', 'Nombre', 'Stock', 'Precio venta', 'Estado', 'Categoría'];
        $rows = Producto::query()
            ->with('categoria:id_categoria,nombre')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get()
            ->map(fn ($p) => [
                $p->id_producto,
                $p->nombre,
                $p->stock,
                $p->precio_venta,
                $p->estado,
                $p->categoria?->nombre ?? '',
            ])
            ->all();

        return $this->export->download(
            'reporte_stock_bajo',
            'Stock bajo (≤ '.$threshold.') — Estilo Dorado',
            $headers,
            $rows,
            $ext
        );
    }

    private function buildFinanciero(Request $request): array
    {
        $days = (int) $request->query('dias', 30);
        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }
        $hasta = Carbon::now()->endOfDay();
        $desde = Carbon::now()->subDays($days - 1)->startOfDay();

        $enRango = fn ($q) => $q->whereBetween('fecha_pedido', [$desde, $hasta]);

        $cobrados = Pedido::query()->where(fn ($q) => $enRango($q))
            ->whereIn('estado', self::COBRADOS)
            ->get(['id_pedido', 'total', 'estado', 'forma_pago', 'fecha_pedido']);

        $ingresos = round((float) $cobrados->sum('total'), 2);
        $nCob = $cobrados->count();
        $ticket = $nCob > 0 ? round($ingresos / $nCob, 2) : 0.0;

        $pendientes = Pedido::query()->where(fn ($q) => $enRango($q))->where('estado', 'pendiente')->count();
        $canceladosQ = Pedido::query()->where(fn ($q) => $enRango($q))->where('estado', 'cancelado');
        $cancelados = (clone $canceladosQ)->count();
        $montoCancelado = round((float) (clone $canceladosQ)->sum('total'), 2);

        $ids = $cobrados->pluck('id_pedido');
        $costo = 0.0;
        $top = [];
        if ($ids->isNotEmpty()) {
            $lineas = DetallePedido::query()
                ->whereIn('id_pedido', $ids)
                ->with('producto:id_producto,nombre,precio_compra')
                ->get();
            foreach ($lineas as $l) {
                $compra = (float) ($l->producto->precio_compra ?? 0);
                $costo += $compra * (int) $l->cantidad;
                $pid = (int) $l->id_producto;
                if (! isset($top[$pid])) {
                    $top[$pid] = [
                        'id' => $pid,
                        'nombre' => $l->producto->nombre ?? ('#'.$pid),
                        'unidades' => 0,
                        'importe' => 0.0,
                    ];
                }
                $top[$pid]['unidades'] += (int) $l->cantidad;
                $top[$pid]['importe'] += (float) $l->subtotal;
            }
        }
        $costo = round($costo, 2);
        $margen = round($ingresos - $costo, 2);

        usort($top, fn ($a, $b) => $b['importe'] <=> $a['importe']);
        $top = array_slice(array_values($top), 0, 6);
        foreach ($top as &$t) {
            $t['importe'] = round($t['importe'], 2);
        }

        $porPago = [];
        foreach ($cobrados->groupBy(fn ($p) => $p->forma_pago ?: 'otro') as $metodo => $grp) {
            $porPago[] = [
                'metodo' => (string) $metodo,
                'pedidos' => $grp->count(),
                'total' => round((float) $grp->sum('total'), 2),
            ];
        }
        usort($porPago, fn ($a, $b) => $b['total'] <=> $a['total']);

        $porDiaMap = [];
        for ($d = $desde->copy(); $d->lte($hasta); $d->addDay()) {
            $porDiaMap[$d->toDateString()] = ['fecha' => $d->toDateString(), 'total' => 0.0, 'pedidos' => 0];
        }
        foreach ($cobrados as $p) {
            $key = optional($p->fecha_pedido)->toDateString();
            if ($key && isset($porDiaMap[$key])) {
                $porDiaMap[$key]['total'] += (float) $p->total;
                $porDiaMap[$key]['pedidos']++;
            }
        }
        $porDia = array_values($porDiaMap);
        foreach ($porDia as &$row) {
            $row['total'] = round($row['total'], 2);
        }

        return [
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'dias' => $days,
            'kpis' => [
                'ingresos' => $ingresos,
                'pedidos_cobrados' => $nCob,
                'ticket_promedio' => $ticket,
                'costo_estimado' => $costo,
                'margen_estimado' => $margen,
                'pendientes' => $pendientes,
                'cancelados' => $cancelados,
                'monto_cancelado' => $montoCancelado,
            ],
            'por_pago' => $porPago,
            'por_dia' => $porDia,
            'top_productos' => $top,
        ];
    }
}
