<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Support\Facades\Schema;

class InventarioKardex
{
    // Kardex = historial. No pone el stock él solo: quien llama (entrada, reserva, salida)
    // ya cambió productos.stock. Aquí solo queda la fila para el reporte.
    public function registrar(
        int $idProducto,
        string $tipo,
        int $cantidad,
        ?string $observacion = null,
        string $refTipo = 'otro',
        ?int $refId = null,
        ?int $empId = null,
    ): ?Inventario {
        if ($cantidad === 0 || ! Schema::hasTable('inventario')) {
            return null;
        }

        return Inventario::create([
            'id_producto' => $idProducto,
            'tipo_movimiento' => $tipo,
            'cantidad' => abs($cantidad),
            'fecha' => now('America/Lima'),
            'observacion' => $observacion,
            'referencia_tipo' => $refTipo,
            'referencia_id' => $refId,
            'id_empleado' => $empId ?? $this->empleadoActual(),
        ]);
    }

    /** Quién está actuando en el panel. Null si el movimiento lo hace un cliente o un job. */
    public function empleadoActual(): ?int
    {
        $u = auth()->user();
        if ($u instanceof Empleado && ! empty($u->id_empleado)) {
            return (int) $u->id_empleado;
        }

        return null;
    }

    /** @return \Illuminate\Support\Collection<int,Inventario> */
    public function dePedido(int $idPedido, ?string $tipo = null)
    {
        $q = Inventario::query()
            ->where('referencia_tipo', 'pedido')
            ->where('referencia_id', $idPedido);
        if ($tipo) {
            $q->where('tipo_movimiento', $tipo);
        }

        return $q->get();
    }
}
