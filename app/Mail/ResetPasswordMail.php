<?php

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Cliente $cliente,
        public string $code,
        public string $resetUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código para recuperar tu contraseña — Estilo Dorado',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reset-password',
        );
    }
}
