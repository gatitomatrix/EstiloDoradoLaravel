<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoEstadoMail extends Mailable
{
    use SerializesModels;

    public string $tiendaUrl;
    public string $doriUrl;
    public string $logoUrl;
    public string $pedidoUrl;

    public function __construct(
        public Pedido $pedido,
        public string $titulo,
        public string $intro,
        public ?string $pdfPath = null,
    ) {
        $url = (string) config('app.frontend_url');
        $this->tiendaUrl = rtrim($url !== '' ? $url : 'https://estilodorado.net.pe', '/');
        $this->doriUrl = $this->tiendaUrl.'/assets/img/dori-completo.jpg';
        $this->logoUrl = $this->tiendaUrl.'/assets/img/logo-edorado.jpeg';
        $this->pedidoUrl = $this->tiendaUrl.'/resumen/'.$pedido->id_pedido;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->titulo,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.pedido-estado',
        );
    }

    public function attachments(): array
    {
        if (! $this->pdfPath || ! is_file($this->pdfPath)) {
            return [];
        }
        $tipo = strtoupper((string) $this->pedido->comprobante_tipo);
        $name = ($tipo === 'FA' ? 'Factura' : 'Boleta').'-'.$this->pedido->id_pedido.'.pdf';

        return [
            Attachment::fromPath($this->pdfPath)
                ->as($name)
                ->withMime('application/pdf'),
        ];
    }
}
