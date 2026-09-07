<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoClienteController extends Controller
{
    /**
     * Lista de pedidos del cliente
     */
    public function index(Request $request)
    {
        $cliente = $request->user();

        $pedidos = Pedido::where('id_cliente', $cliente->id_cliente)
            ->with(['detalles.producto', 'historial' => fn ($q) => $q->orderByDesc('fecha')])
            ->orderByDesc('id_pedido')
            ->get()
            ->map(function ($p) {
                $p->fecha_estado = optional($p->historial->first())->fecha;
                $p->setRelation('historial', $p->historial->take(8)->values());
                return $p;
            });

        return response()->json($pedidos);
    }

    /**
     * Ver un pedido específico
     */
    public function show(Request $request, $id)
    {
        $cliente = $request->user();

        $pedido = Pedido::where('id_pedido', $id)
            ->where('id_cliente', $cliente->id_cliente)
            ->with(['detalles.producto', 'historial' => fn ($q) => $q->orderByDesc('fecha')])
            ->firstOrFail();
        $pedido->fecha_estado = optional($pedido->historial->first())->fecha;

        return response()->json($pedido);
    }

    /**
     * Alias para /pedidos/mios
     */
    public function mios(Request $request)
    {
        return $this->index($request);
    }
}