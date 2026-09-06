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
                $this->fecha($c->created_at),
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
        $headers = ['ID', 'Nombre', 'Categoría', 'Precio compra (S/)', 'Precio venta (S/)', 'Stock', 'Estado'];
        $rows = Producto::query()
            ->with('categoria:id_categoria,nombre')
            ->orderBy('id_producto')
            ->get()
            ->map(fn ($p) => [
                $p->id_producto,
                $p->nombre,
                $p->categoria?->nombre ?? '—',
                $this->soles($p->precio_compra),
                $this->soles($p->precio_venta),
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
        [$desde, $hasta] = $this->rangoFechas($request);
        $headers = ['ID', 'Cliente', 'Estado', 'Total (S/)', 'Forma de pago', 'Fecha', 'Comprobante', 'Entrega'];
        $q = Pedido::query()->with('cliente')->orderByDesc('id_pedido');
        if ($request->filled('desde') || $request->filled('hasta') || $request->filled('dias') || $request->filled('mes')) {
            $q->whereBetween('fecha_pedido', [$desde, $hasta]);
        }
        $rows = $q->get()
            ->map(function ($p) {
                $cliente = trim(($p->cliente->nombre ?? '').' '.($p->cliente->apellido ?? ''));
                $comp = trim(($p->comprobante_tipo ?? '').' '.($p->comprobante_serie ?? '').'-'.($p->comprobante_numero ?? ''));
                $comp = trim(str_replace('--', '-', $comp), '- ');

                return [
                    $p->id_pedido,
                    $cliente !== '' ? $cliente : ('#'.$p->id_cliente),
                    $p->estado,
                    $this->soles($p->total),
                    $p->forma_pago ?: '—',
                    $this->fecha($p->fecha_pedido),
                    $comp !== '' ? $comp : '—',
                    $p->direccion_entrega ?: 'Recojo en tienda',
                ];
            })
            ->all();

        return $this->export->download(
            'reporte_pedidos',
            'Reporte de pedidos — Estilo Dorado ('.$desde->toDateString().' a '.$hasta->toDateString().')',
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
                return [
                    $m->id_movimiento,
                    $m->producto?->nombre ?? '—',
                    $m->tipo_movimiento,
                    $m->cantidad,
                    $m->empleado?->nombre ?? '—',
                    $this->fecha($m->fecha ?? $m->fecha_movimiento ?? null),
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
        $k = $data['kpis'];
        $headers = ['Indicador', 'Periodo desde', 'Periodo hasta', 'Cantidad', 'Monto (S/)'];
        $rows = [
            ['Ingresos cobrados', $data['desde'], $data['hasta'], $k['pedidos_cobrados'], $this->soles($k['ingresos'])],
            ['Ticket promedio', $data['desde'], $data['hasta'], $k['pedidos_cobrados'], $this->soles($k['ticket_promedio'])],
            ['Costo estimado', $data['desde'], $data['hasta'], $k['pedidos_cobrados'], $this->soles($k['costo_estimado'])],
            ['Margen estimado', $data['desde'], $data['hasta'], $k['pedidos_cobrados'], $this->soles($k['margen_estimado'])],
            ['Pedidos pendientes', $data['desde'], $data['hasta'], $k['pendientes'], '—'],
            ['Pedidos cancelados', $data['desde'], $data['hasta'], $k['cancelados'], $this->soles($k['monto_cancelado'])],
        ];
        foreach ($data['por_pago'] as $p) {
            $rows[] = [
                'Pago: '.$p['metodo'],
                $data['desde'],
                $data['hasta'],
                $p['pedidos'],
                $this->soles($p['total']),
            ];
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
        $nombres = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $rows = array_map(function ($d) use ($nombres) {
            $c = Carbon::parse($d['fecha']);
            $ticket = ((int) $d['pedidos']) > 0 ? ((float) $d['total'] / (int) $d['pedidos']) : 0;

            return [
                $c->format('d/m/Y'),
                $nombres[$c->dayOfWeek] ?? '',
                $d['pedidos'],
                $this->soles($d['total']),
                $this->soles($ticket),
            ];
        }, $data['por_dia']);

        return $this->export->download(
            'reporte_ventas_dia',
            'Ventas por día — Estilo Dorado',
            ['Fecha', 'Día', 'Pedidos', 'Total (S/)', 'Ticket promedio (S/)'],
            $rows,
            $ext
        );
    }

    public function formaPago(string $ext, Request $request)
    {
        $data = $this->buildFinanciero($request);
        $ingresos = (float) ($data['kpis']['ingresos'] ?? 0);
        $rows = array_map(function ($p) use ($ingresos, $data) {
            $ticket = ((int) $p['pedidos']) > 0 ? ((float) $p['total'] / (int) $p['pedidos']) : 0;

            return [
                $p['metodo'] ?: 'otro',
                $data['desde'].' a '.$data['hasta'],
                $p['pedidos'],
                $this->soles($p['total']),
                $this->soles($ticket),
                $this->pct((float) $p['total'], $ingresos),
            ];
        }, $data['por_pago']);

        return $this->export->download(
            'reporte_forma_pago',
            'Ventas por forma de pago — Estilo Dorado',
            ['Forma de pago', 'Periodo', 'Pedidos', 'Total (S/)', 'Ticket (S/)', '% del ingreso'],
            $rows,
            $ext
        );
    }

    public function topProductos(string $ext, Request $request)
    {
        $data = $this->buildFinanciero($request);
        $ingresos = (float) ($data['kpis']['ingresos'] ?? 0);
        $rows = array_map(function ($p) use ($ingresos) {
            $und = (int) $p['unidades'];
            $imp = (float) $p['importe'];
            $prom = $und > 0 ? $imp / $und : 0;

            return [
                $p['id'],
                $p['nombre'],
                $und,
                $this->soles($imp),
                $this->soles($prom),
                $this->pct($imp, $ingresos),
            ];
        }, $data['top_productos']);

        return $this->export->download(
            'reporte_top_productos',
            'Productos que más facturaron — Estilo Dorado',
            ['ID', 'Producto', 'Unidades', 'Importe (S/)', 'Precio prom. (S/)', '% de ventas'],
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

        $headers = ['ID', 'Nombre', 'Categoría', 'Stock', 'A reponer', 'Precio venta (S/)', 'Estado'];
        $rows = Producto::query()
            ->with('categoria:id_categoria,nombre')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get()
            ->map(fn ($p) => [
                $p->id_producto,
                $p->nombre,
                $p->categoria?->nombre ?? '—',
                $p->stock,
                max(0, $threshold - (int) $p->stock),
                $this->soles($p->precio_venta),
                $p->estado,
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

    private function rangoFechas(Request $request): array
    {
        $tz = 'America/Lima';
        $ahora = Carbon::now($tz);
        $hasta = $ahora->copy()->endOfDay();
        $desde = $ahora->copy()->subDays(29)->startOfDay();
        $days = 30;

        if ($request->filled('desde') || $request->filled('hasta')) {
            if ($request->filled('desde')) {
                $desde = Carbon::parse($request->query('desde'), $tz)->startOfDay();
            }
            if ($request->filled('hasta')) {
                $hasta = Carbon::parse($request->query('hasta'), $tz)->endOfDay();
            }
            if ($desde->gt($hasta)) {
                $tmp = $desde->copy();
                $desde = $hasta->copy()->startOfDay();
                $hasta = $tmp->endOfDay();
            }
            if ($desde->diffInDays($hasta) > 365) {
                $desde = $hasta->copy()->subDays(365)->startOfDay();
            }
            $days = $desde->diffInDays($hasta) + 1;

            return [$desde, $hasta, $days];
        }

        if ((string) $request->query('mes') === '1') {
            $desde = $ahora->copy()->startOfMonth()->startOfDay();
            $days = $desde->diffInDays($hasta) + 1;

            return [$desde, $hasta, $days];
        }

        $days = (int) $request->query('dias', 30);
        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }
        $desde = $ahora->copy()->subDays($days - 1)->startOfDay();

        return [$desde, $hasta, $days];
    }

    private function buildFinanciero(Request $request): array
    {
        [$desde, $hasta, $days] = $this->rangoFechas($request);

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

    private function soles(mixed $n): string
    {
        return 'S/ '.number_format((float) $n, 2, '.', ',');
    }

    private function fecha(mixed $d): string
    {
        if ($d === null || $d === '') {
            return '';
        }
        try {
            $c = $d instanceof \DateTimeInterface
                ? Carbon::instance(\Carbon\Carbon::parse($d))
                : Carbon::parse((string) $d);

            return $c->timezone('America/Lima')->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $d;
        }
    }

    private function pct(float $part, float $whole): string
    {
        if ($whole <= 0) {
            return '0 %';
        }

        return number_format($part / $whole * 100, 1, '.', ',').' %';
    }
}
