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
    public string $doriUrl;
    public string $logoUrl;

    public function __construct(public Cliente $cliente)
    {
        $url = (string) config('app.frontend_url');
        $this->tiendaUrl = rtrim($url !== '' ? $url : 'https://estilodorado.net.pe', '/');
        $this->doriUrl = $this->tiendaUrl.'/assets/img/dori-completo.jpg';
        $this->logoUrl = $this->tiendaUrl.'/assets/img/logo-edorado.jpeg';
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
            html: 'emails.welcome',
        );
    }
}
