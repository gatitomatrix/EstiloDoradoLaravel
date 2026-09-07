<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoEstadoHistorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoEstadoController extends Controller
{
    // GET /api/admin/pedidos/{id}/estado-historial
    public function historial($id)
    {
        $rows = PedidoEstadoHistorial::where('id_pedido', $id)
            ->orderBy('fecha','asc')
            ->get();

        return response()->json(['data' => $rows]);
    }

    // POST /api/admin/pedidos/{id}/estado
    public function cambiarEstado($id, Request $request)
    {
        return $this->update($id, $request);
    }

    // PUT /api/admin/pedidos/{id}/estado
    public function update($id, Request $request)
    {
        $data = $request->validate([
            'estado'     => 'required|in:pendiente,pagado,enviado,entregado,cancelado',
            'comentario' => 'nullable|string',
            'id_empleado'=> 'nullable|integer',
        ]);

        $pedido = Pedido::find($id);
        if (!$pedido) return response()->json(['message'=>'Pedido no encontrado'],404);

        $anterior = (string) $pedido->estado;
        try {
            DB::transaction(function () use ($pedido, $data, $anterior) {
                $pedido->estado = $data['estado'];
                $pedido->save();

                if (strcasecmp($anterior, $data['estado']) !== 0) {
                    app(\App\Services\StockPedidoService::class)->aplicarCambioEstado($pedido, $anterior, $data['estado']);
                }

                PedidoEstadoHistorial::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'estado_anterior' => $anterior,
                    'estado_nuevo'    => $data['estado'],
                    'fecha'           => now(),
                    'comentario'      => $data['comentario'] ?? null,
                    'id_empleado'     => $data['id_empleado'] ?? null,
                ]);
            });
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => 'No hay stock suficiente para reactivar este pedido. El estado no se cambió.',
                'detalles' => $e->detalles,
            ], 422);
        }

        return response()->json(['message'=>'Estado actualizado'],200);
    }
}
