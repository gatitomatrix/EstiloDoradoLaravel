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
    public function __construct(private InventarioKardex $kardex) {}

    public function aplicarCambioEstado(Pedido $pedido, string $antes, string $despues): void
    {
        $antes = strtolower($antes);
        $despues = strtolower($despues);
        if ($antes === $despues) {
            return;
        }
        if (in_array($despues, ['entregado', 'completado'], true) && $antes !== 'cancelado') {
            $this->confirmarEntrega($pedido);
            return;
        }
        if ($despues === 'cancelado') {
            $this->devolver($pedido);
        }
    }

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
            if (! $p) {
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
            $this->kardex->registrar(
                (int) $p->id_producto,
                'reserva',
                $qty,
                'Reserva por pedido #'.$pedido->id_pedido,
                'pedido',
                (int) $pedido->id_pedido,
            );
        }
        $this->marcar($pedido, true);
    }

    /** Al marcar Entregado: la reserva pasa a Salida. No vuelve a descontar stock. */
    public function confirmarEntrega(Pedido $pedido): void
    {
        $pedido->loadMissing('detalles');
        if (! $this->yaReservado($pedido)) {
            $this->reservar($pedido, true);
        }
        $reservas = $this->kardex->dePedido((int) $pedido->id_pedido, 'reserva');
        if ($reservas->isEmpty()) {
            if ($this->kardex->dePedido((int) $pedido->id_pedido, 'salida')->isNotEmpty()) {
                return;
            }
            foreach ($pedido->detalles as $d) {
                $this->kardex->registrar(
                    (int) $d->id_producto,
                    'salida',
                    (int) $d->cantidad,
                    'Salida confirmada al entregar pedido #'.$pedido->id_pedido,
                    'pedido',
                    (int) $pedido->id_pedido,
                );
            }

            return;
        }
        foreach ($reservas as $mov) {
            $mov->tipo_movimiento = 'salida';
            $mov->observacion = trim((string) $mov->observacion.' · Entregado');
            $mov->save();
        }
    }

    public function devolver(Pedido $pedido): void
    {
        $pedido->loadMissing('detalles');
        if (! $this->debeDevolver($pedido)) {
            return;
        }

        $salidas = $this->kardex->dePedido((int) $pedido->id_pedido, 'salida');
        $reservas = $this->kardex->dePedido((int) $pedido->id_pedido, 'reserva');

        foreach ($pedido->detalles as $d) {
            Producto::where('id_producto', $d->id_producto)
                ->increment('stock', (int) $d->cantidad);
        }

        if ($salidas->isNotEmpty()) {
            foreach ($pedido->detalles as $d) {
                $this->kardex->registrar(
                    (int) $d->id_producto,
                    'devolucion',
                    (int) $d->cantidad,
                    'Devolución pedido #'.$pedido->id_pedido,
                    'pedido',
                    (int) $pedido->id_pedido,
                );
            }
        } elseif ($reservas->isNotEmpty()) {
            foreach ($reservas as $mov) {
                $mov->tipo_movimiento = 'liberacion';
                $mov->observacion = trim((string) $mov->observacion.' · Pedido cancelado, stock liberado');
                $mov->save();
            }
        } else {
            foreach ($pedido->detalles as $d) {
                $this->kardex->registrar(
                    (int) $d->id_producto,
                    'liberacion',
                    (int) $d->cantidad,
                    'Liberación pedido #'.$pedido->id_pedido,
                    'pedido',
                    (int) $pedido->id_pedido,
                );
            }
        }
        $this->marcar($pedido, false);
    }

    private function debeDevolver(Pedido $pedido): bool
    {
        if ($this->yaReservado($pedido)) {
            return true;
        }
        if ($this->kardex->dePedido((int) $pedido->id_pedido)->isNotEmpty()) {
            return true;
        }
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
        if ($tipo === 'BO' && (int) $pedido->comprobante_numero === 0) {
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

        return $this->kardex->dePedido((int) $pedido->id_pedido, 'reserva')->isNotEmpty()
            || $this->kardex->dePedido((int) $pedido->id_pedido, 'salida')->isNotEmpty();
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
