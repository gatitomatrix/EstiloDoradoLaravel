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
        try {
            $data = $request->validate([
                'forma_pago'          => 'nullable|string|max:50',
                'direccion_entrega'   => 'nullable|string|max:255',
                'envio_tipo'          => 'nullable|in:AGENCIA,DOMICILIO,agencia,domicilio',
                'ubigeo'              => 'nullable|array',
                'observacion'         => 'nullable|string|max:500',
                'items'               => 'required|array|min:1',
                'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
                'items.*.cantidad'    => 'required|integer|min:1|max:99',
            ]);

            $cliente = $request->user();

            // Validar stock
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
                        'producto_id'         => $producto->id_producto,
                        'nombre'              => $producto->nombre,
                        'stock_disponible'    => $producto->stock,
                        'cantidad_solicitada' => $item['cantidad'],
                    ];
                }
            }

            if (!empty($erroresStock)) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'No hay suficiente stock para algunos productos.',
                    'error'    => 'insufficient_stock',
                    'detalles' => $erroresStock,
                ], 422);
            }

            return DB::transaction(function () use ($data, $cliente) {
                $pedido = new Pedido();
                $pedido->id_cliente         = $cliente->id_cliente;
                $pedido->fecha_pedido       = now();
                $pedido->estado             = 'pendiente';
                $pedido->total              = 0;
                $pedido->forma_pago         = $data['forma_pago'] ?? null;
                $pedido->direccion_entrega  = $data['direccion_entrega'] ?? null;
                // $pedido->observacion     = $data['observacion'] ?? null;   // columna no existe en la tabla

                // Campos de comprobante (valores seguros)
                $pedido->comprobante_tipo   = 'BO';
                $pedido->comprobante_serie  = 'B001';
                $pedido->comprobante_numero = 0;
                $pedido->sunat_xml          = null;
                $pedido->sunat_pdf          = null;
                $pedido->sunat_cdr          = null;

                $pedido->save();

                $total = 0;
                foreach ($data['items'] as $item) {
                    $producto = Producto::findOrFail($item['id_producto']);
                    $precio   = (float) $producto->precio_final;
                    $subtotal = $precio * $item['cantidad'];

                    DetallePedido::create([
                        'id_pedido'       => $pedido->id_pedido,
                        'id_producto'     => $producto->id_producto,
                        'cantidad'        => $item['cantidad'],
                        'precio_unitario' => $precio,
                        // 'subtotal'     => $subtotal,   // columna generada en la base de datos
                    ]);

                    $producto->decrement('stock', $item['cantidad']);
                    $total += $subtotal;
                }

                $info = \App\Services\Envio\TarifaEnvio::desdeRequest($data);
                $envio = round((float) $info['costo'], 2);
                if (\Illuminate\Support\Facades\Schema::hasColumn('pedidos', 'costo_envio')) {
                    $pedido->costo_envio = $envio;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('pedidos', 'envio_etiqueta')) {
                    $pedido->envio_etiqueta = $info['etiqueta'];
                }
                $pedido->observacion = trim((string) ($pedido->observacion ?? '').' ENVIO:'.$envio.'|'.$info['etiqueta']);
                $pedido->total = $total + $envio;
                $pedido->save();

                PedidoEstadoHistorial::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'estado_anterior' => null,
                    'estado_nuevo'    => 'pendiente',
                    'fecha'           => now(),
                    'comentario'      => 'Pedido creado desde la aplicación móvil',
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
                        'observacion'       => $pedido->observacion ?? null,
                        'fecha_pedido'      => $pedido->fecha_pedido,
                        'detalles'          => $pedido->detalles,
                    ]
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }
}