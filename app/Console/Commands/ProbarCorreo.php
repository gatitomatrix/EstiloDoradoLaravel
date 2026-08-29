<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProbarCorreo extends Command
{
    protected $signature = 'estilo:probar-correo {email : Destino (Gmail, Outlook, etc.)}';

    protected $description = 'Envía un correo de prueba (Mailpit o SMTP real, según .env)';

    public function handle(): int
    {
        $to = (string) $this->argument('email');
        $mailer = config('mail.default');
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');

        $this->info("Mailer: {$mailer}  Host: {$host}:{$port}");
        $this->info("From: ".config('mail.from.address'));
        $this->info("To: {$to}");

        try {
            Mail::raw(
                "Prueba de Estilo Dorado.\nSi lees esto, el correo funciona.\nMailer={$mailer} host={$host}",
                function ($m) use ($to) {
                    $m->to($to)->subject('Prueba Estilo Dorado');
                }
            );
        } catch (\Throwable $e) {
            $this->error('Falló: '.$e->getMessage());
            $this->line('Mailpit: MAIL_HOST=127.0.0.1 MAIL_PORT=1025 y `mailpit` abierto.');
            $this->line('Gmail: MAIL_HOST=smtp.gmail.com MAIL_PORT=587 + contraseña de aplicación.');
            return self::FAILURE;
        }

        if ((string) $host === '127.0.0.1' || (int) $port === 1025) {
            $this->info('Enviado a Mailpit → http://127.0.0.1:8025');
        } else {
            $this->info('Enviado por SMTP real. Revisa la bandeja (y spam) de '.$to);
        }

        return self::SUCCESS;
    }
}
