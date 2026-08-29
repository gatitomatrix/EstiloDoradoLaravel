<?php

namespace App\Services;

use App\Mail\PedidoEstadoMail;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PedidoMailer
{
    public function notify(Pedido $pedido): void
    {
        $pedido->loadMissing(['cliente', 'detalles.producto']);
        $email = $pedido->cliente?->email;
        if (! $email) {
            return;
        }

        $estado = strtolower((string) $pedido->estado);
        [$titulo, $intro] = match ($estado) {
            'pendiente' => [
                'Pedido #'.$pedido->id_pedido.' registrado — Estilo Dorado',
                'Recibimos tu pedido. Queda pendiente de pago (o de recojo en efectivo en tienda). Puedes verlo en Mis compras.',
            ],
            'pagado' => [
                'Pago confirmado — pedido #'.$pedido->id_pedido,
                '¡Gracias! Tu pago quedó registrado. Estilo Dorado preparará tu pedido. El comprobante lo ves en Mis compras cuando esté listo.',
            ],
            'enviado' => [
                'Tu pedido #'.$pedido->id_pedido.' va en camino',
                'La tienda marcó tu pedido como enviado. Cualquier duda, escríbenos por WhatsApp.',
            ],
            'entregado' => [
                'Pedido #'.$pedido->id_pedido.' entregado',
                '¡Listo! Marcamos tu pedido como entregado. Gracias por comprar en Estilo Dorado.',
            ],
            'cancelado' => [
                'Pedido #'.$pedido->id_pedido.' cancelado',
                'Tu pedido fue cancelado. Si no fuiste tú, avísanos por WhatsApp.',
            ],
            default => [
                'Actualización de tu pedido #'.$pedido->id_pedido,
                'El estado de tu pedido ahora es: '.$pedido->estado.'.',
            ],
        };

        try {
            Mail::to($email)->send(new PedidoEstadoMail($pedido, $titulo, $intro));
        } catch (\Throwable $e) {
            Log::warning('[PedidoMailer] '.$e->getMessage());
        }
    }
}
