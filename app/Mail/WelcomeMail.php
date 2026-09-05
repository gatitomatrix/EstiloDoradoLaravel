<?php

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use SerializesModels;

    public string $tiendaUrl;

    public function __construct(public Cliente $cliente)
    {
        $this->tiendaUrl = (string) config('app.frontend_url');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a Estilo Dorado!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome',
        );
    }
}
