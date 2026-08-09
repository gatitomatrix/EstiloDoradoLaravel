<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\ReportExportService;
use Illuminate\Http\Request;

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
}
