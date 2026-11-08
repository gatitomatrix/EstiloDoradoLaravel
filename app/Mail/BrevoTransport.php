<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

class BrevoTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();
        if (! $email instanceof Email) {
            throw new \RuntimeException('Mensaje de correo no compatible.');
        }

        $key = (string) config('services.brevo.key');
        if ($key === '') {
            throw new \RuntimeException('Falta BREVO_API_KEY en Render.');
        }

        $from = $email->getFrom()[0] ?? null;
        $senderEmail = $from?->getAddress() ?: (string) config('mail.from.address');
        $senderName = $from?->getName() ?: (string) config('mail.from.name');

        $to = [];
        foreach ($email->getTo() as $addr) {
            $row = ['email' => $addr->getAddress()];
            if ($addr->getName()) {
                $row['name'] = $addr->getName();
            }
            $to[] = $row;
        }
        if ($to === []) {
            throw new \RuntimeException('Correo sin destinatario.');
        }

        $payload = [
            'sender' => [
                'email' => $senderEmail,
                'name' => $senderName !== '' ? $senderName : 'Estilo Dorado',
            ],
            'to' => $to,
            'subject' => $email->getSubject() ?: 'Estilo Dorado',
        ];

        $html = $email->getHtmlBody();
        $text = $email->getTextBody();
        if (is_string($html) && $html !== '') {
            $payload['htmlContent'] = $html;
        }
        if (is_string($text) && $text !== '') {
            $payload['textContent'] = $text;
        }
        if (! isset($payload['htmlContent']) && ! isset($payload['textContent'])) {
            $payload['textContent'] = 'Estilo Dorado';
        }

        $attachments = [];
        foreach ($email->getAttachments() as $part) {
            $raw = $part->getBody();
            if (! is_string($raw)) {
                $raw = stream_get_contents($raw) ?: '';
            }
            $attachments[] = [
                'content' => base64_encode($raw),
                'name' => $part->getFilename() ?: 'adjunto.pdf',
            ];
        }
        if ($attachments !== []) {
            $payload['attachment'] = $attachments;
        }

        $res = Http::withHeaders([
            'api-key' => $key,
            'accept' => 'application/json',
        ])->timeout(25)->post('https://api.brevo.com/v3/smtp/email', $payload);

        if (! $res->successful()) {
            throw new \RuntimeException('Brevo '.$res->status().': '.$res->body());
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
