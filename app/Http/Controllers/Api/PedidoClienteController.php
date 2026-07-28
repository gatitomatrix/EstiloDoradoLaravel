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
            ->with(['detalles.producto'])
            ->orderByDesc('id_pedido')
            ->get();

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
            ->with(['detalles.producto'])
            ->firstOrFail();

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