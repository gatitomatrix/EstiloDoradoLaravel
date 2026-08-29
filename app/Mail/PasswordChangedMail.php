<?php

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Cliente $cliente) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu contraseña de Estilo Dorado se actualizó',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password-changed',
        );
    }
}
