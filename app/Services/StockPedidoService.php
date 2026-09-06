<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Pedido;
use App\Models\PedidoEstadoHistorial;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StockPedidoService
{
    public function reservar(Pedido $pedido, bool $allowOversell = false): void
    {
        $pedido->loadMissing('detalles.producto');
        if ($this->yaReservado($pedido)) {
            return;
        }

        $faltantes = [];
        $locked = [];
        foreach ($pedido->detalles as $d) {
            $p = Producto::where('id_producto', $d->id_producto)->lockForUpdate()->first();
            if (!$p) {
                continue;
            }
            $qty = (int) $d->cantidad;
            if ($p->stock < $qty) {
                $faltantes[] = [
                    'producto_id' => $p->id_producto,
                    'nombre' => $p->nombre,
                    'stock_disponible' => (int) $p->stock,
                    'cantidad_solicitada' => $qty,
                ];
            }
            $locked[] = [$p, $qty];
        }

        if ($faltantes && ! $allowOversell) {
            throw new InsufficientStockException($faltantes);
        }
        if ($faltantes && $allowOversell) {
            Log::warning('[stock] pedido '.$pedido->id_pedido.' sin stock suficiente; se descuenta igual por pago ya cobrado', $faltantes);
        }

        foreach ($locked as [$p, $qty]) {
            $p->stock = max(0, (int) $p->stock - $qty);
            $p->save();
        }
        $this->marcar($pedido, true);
    }

    public function devolver(Pedido $pedido): void
    {
        $pedido->loadMissing('detalles');
        if (! $this->debeDevolver($pedido)) {
            return;
        }
        foreach ($pedido->detalles as $d) {
            Producto::where('id_producto', $d->id_producto)
                ->increment('stock', (int) $d->cantidad);
        }
        $this->marcar($pedido, false);
    }

    private function debeDevolver(Pedido $pedido): bool
    {
        if ($this->yaReservado($pedido)) {
            return true;
        }
        // Pedidos viejos de la app que sí descontaban (POST /pedidos).
        try {
            $movil = PedidoEstadoHistorial::where('id_pedido', $pedido->id_pedido)
                ->where('comentario', 'like', '%aplicación móvil%')
                ->exists();
            if ($movil) {
                return true;
            }
        } catch (\Throwable $e) {
        }
        $tipo = strtoupper((string) $pedido->comprobante_tipo);
        if ($tipo === 'BO' && (int) $pedido->comprobante_numero === 0 && $tipo !== 'EF') {
            return true;
        }

        return false;
    }

    private function yaReservado(Pedido $pedido): bool
    {
        if (Schema::hasColumn('pedidos', 'stock_descontado')) {
            return (bool) $pedido->stock_descontado;
        }
        if (Schema::hasColumn('pedidos', 'observacion')) {
            return str_contains((string) $pedido->observacion, '[STOCK:1]');
        }

        return false;
    }

    private function marcar(Pedido $pedido, bool $on): void
    {
        if (Schema::hasColumn('pedidos', 'stock_descontado')) {
            $pedido->stock_descontado = $on;
            $pedido->save();

            return;
        }
        if (! Schema::hasColumn('pedidos', 'observacion')) {
            return;
        }
        $obs = preg_replace('/\s*\[STOCK:[01]\]/', '', (string) $pedido->observacion) ?? '';
        $pedido->observacion = trim($obs.' '.($on ? '[STOCK:1]' : '[STOCK:0]'));
        $pedido->save();
    }
}
