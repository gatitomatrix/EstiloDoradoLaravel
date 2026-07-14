<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\PedidoEstadoHistorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoClienteStoreController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'forma_pago'        => 'nullable|string|max:50',
            'direccion_entrega' => 'nullable|string|max:255',
            'observacion'       => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'items.*.cantidad'    => 'required|integer|min:1|max:99',
        ]);

        $cliente = $request->user();

        // ============================================
        // 1. VALIDAR STOCK ANTES DE INICIAR LA TRANSACCIÓN
        // ============================================
        $erroresStock = [];
        foreach ($data['items'] as $item) {
            $producto = Producto::find($item['id_producto']);
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uno de los productos no existe.',
                    'error'   => 'product_not_found',
                ], 422);
            }
            if ($producto->stock < $item['cantidad']) {
                $erroresStock[] = [
                    'producto_id' => $producto->id_producto,
                    'nombre'      => $producto->nombre,
                    'stock_disponible' => $producto->stock,
                    'cantidad_solicitada' => $item['cantidad'],
                ];
            }
        }

        // Si hay problemas de stock, devolvemos error claro
        if (!empty($erroresStock)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay suficiente stock para algunos productos.',
                'error'   => 'insufficient_stock',
                'detalles' => $erroresStock,
            ], 422);
        }

        // ============================================
        // 2. CREAR EL PEDIDO (si todo está bien)
        // ============================================
        return DB::transaction(function () use ($data, $cliente) {
            $pedido = Pedido::create([
                'id_cliente'        => $cliente->id_cliente,
                'fecha_pedido'      => now(),
                'estado'            => 'pendiente',
                'total'             => 0,
                'forma_pago'        => $data['forma_pago'] ?? null,
                'direccion_entrega' => $data['direccion_entrega'] ?? null,
                'observacion'       => $data['observacion'] ?? null,

                // === CAMPOS DE COMPROBANTE ELECTRÓNICO (obligatorios) ===
                'comprobante_tipo'  => 'boleta',      // puedes cambiar a 'factura' si prefieres
                'comprobante_serie' => null,
                'comprobante_numero'=> null,
                'sunat_xml'         => null,
                'sunat_pdf'         => null,
                'sunat_cdr'         => null,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $producto = Producto::findOrFail($item['id_producto']);
                $precio = $producto->precio_venta;
                $subtotal = $precio * $item['cantidad'];

                DetallePedido::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'id_producto'     => $producto->id_producto,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $precio,
                    'subtotal'        => $subtotal,
                ]);

                // Descontar stock
                $producto->decrement('stock', $item['cantidad']);

                $total += $subtotal;
            }

            $pedido->total = $total;
            $pedido->save();

            // Registrar historial
            PedidoEstadoHistorial::create([
                'id_pedido'  => $pedido->id_pedido,
                'estado'     => 'pendiente',
                'fecha'      => now(),
                'comentario' => 'Pedido creado desde la aplicación móvil',
            ]);

            $pedido->load('detalles.producto');

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'pedido'  => [
                    'id_pedido'         => $pedido->id_pedido,
                    'estado'            => $pedido->estado,
                    'total'             => $pedido->total,
                    'forma_pago'        => $pedido->forma_pago,
                    'direccion_entrega' => $pedido->direccion_entrega,
                    'observacion'       => $pedido->observacion,
                    'fecha_pedido'      => $pedido->fecha_pedido,
                    'detalles'          => $pedido->detalles,
                ]
            ], 201);
        });
    }
}