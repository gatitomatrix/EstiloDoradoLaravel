<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Support\Facades\Schema;

class InventarioKardex
{
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
            'fecha' => now(),
            'observacion' => $observacion,
            'referencia_tipo' => $refTipo,
            'referencia_id' => $refId,
            'id_empleado' => $empId,
        ]);
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
