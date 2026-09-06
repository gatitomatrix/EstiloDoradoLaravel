<?php

namespace App\Services;

use App\Mail\PedidoEstadoMail;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
        $pdfPath = null;

        if ($estado === 'pagado') {
            try {
                app(ComprobanteService::class)->asegurarPdf($pedido);
                $pedido->refresh();
                if ($pedido->sunat_pdf && Storage::disk('public')->exists($pedido->sunat_pdf)) {
                    $pdfPath = Storage::disk('public')->path($pedido->sunat_pdf);
                }
            } catch (\Throwable $e) {
                Log::warning('[PedidoMailer] pdf '.$pedido->id_pedido.': '.$e->getMessage());
            }
        }

        [$titulo, $intro] = match ($estado) {
            'pendiente' => [
                'Pedido #'.$pedido->id_pedido.' registrado — Estilo Dorado',
                'Recibimos tu pedido. Queda pendiente de pago en tienda. Te esperamos para recogerlo; si cambias de opinión, puedes cancelarlo en Mis compras y el stock se libera.',
            ],
            'pagado' => [
                '¡Gracias por tu compra! Pedido #'.$pedido->id_pedido,
                $pdfPath
                    ? 'Confirmamos tu pago. Dori te adjunta el comprobante en este correo. Ya estamos preparando tu pedido con mucho cuidado.'
                    : 'Confirmamos tu pago. Ya estamos preparando tu pedido. El comprobante también queda en Mis compras.',
            ],
            'enviado' => [
                'Tu pedido #'.$pedido->id_pedido.' va en camino',
                'La tienda marcó tu pedido como enviado. Cualquier duda, Dori o WhatsApp están para ayudarte.',
            ],
            'entregado' => [
                'Pedido #'.$pedido->id_pedido.' entregado',
                '¡Listo! Esperamos que lo disfrutes. Gracias por elegir Estilo Dorado.',
            ],
            'cancelado' => [
                'Pedido #'.$pedido->id_pedido.' cancelado',
                'Tu pedido fue cancelado y el stock volvió a la tienda. Si no fuiste tú, avísanos por WhatsApp.',
            ],
            default => [
                'Actualización de tu pedido #'.$pedido->id_pedido,
                'El estado de tu pedido ahora es: '.$pedido->estado.'.',
            ],
        };

        try {
            Mail::to($email)->send(new PedidoEstadoMail($pedido, $titulo, $intro, $pdfPath));
        } catch (\Throwable $e) {
            Log::warning('[PedidoMailer] '.$e->getMessage());
        }
    }
}
