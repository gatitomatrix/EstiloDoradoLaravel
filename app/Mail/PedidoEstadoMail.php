<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoEstadoMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Pedido $pedido,
        public string $titulo,
        public string $intro,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->titulo,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pedido-estado',
        );
    }
}
