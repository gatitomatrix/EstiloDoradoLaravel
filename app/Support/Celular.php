<?php

namespace App\Support;

class Celular
{
    /** WhatsApp de la tienda. Nunca se guarda como celular del cliente. */
    public static function deTienda(): string
    {
        $n = self::normalizar((string) (config('llm.whatsapp.number') ?: '51916464315'));

        return $n ?: '916464315';
    }

    /** Normaliza a 9 dígitos (9xxxxxxxx) o null. */
    public static function normalizar(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw) ?? '';
        if (str_starts_with($d, '51') && strlen($d) >= 11) {
            $d = substr($d, 2, 9);
        }
        $d = substr($d, 0, 9);
        if (preg_match('/^9\d{8}$/', $d)) {
            return $d;
        }

        return null;
    }

    /** Celular de un cliente: válido y distinto al de la tienda. */
    public static function deCliente(?string $raw): ?string
    {
        $d = self::normalizar($raw);
        if (! $d || $d === self::deTienda()) {
            return null;
        }

        return $d;
    }

    /** Enlace wa.me al celular del cliente (nunca el de la tienda). */
    public static function waMe(?string $raw, ?string $text = null): ?string
    {
        $d = self::deCliente($raw);
        if (! $d) {
            return null;
        }
        $q = $text ? ('?text='.rawurlencode($text)) : '';

        return 'https://wa.me/51'.$d.$q;
    }

    public static function formato(?string $raw): ?string
    {
        $d = self::deCliente($raw);
        if (! $d) {
            return null;
        }

        return '+51 '.$d;
    }

    public static function desdePedido($pedido): ?string
    {
        if (! $pedido) {
            return null;
        }
        if (! empty($pedido->telefono_contacto)) {
            return self::deCliente((string) $pedido->telefono_contacto);
        }
        if (! empty($pedido->observacion) && preg_match('/\[CEL:(\d{9})\]/', (string) $pedido->observacion, $m)) {
            return self::deCliente($m[1]);
        }

        return null;
    }

    /** Datos para el admin: número + enlace WhatsApp del cliente del pedido. */
    public static function contactoPedido($pedido, ?string $clienteTel = null): array
    {
        $cel = self::desdePedido($pedido) ?: self::deCliente($clienteTel);
        $id = $pedido->id_pedido ?? '';
        $nombre = trim((string) ($pedido->cliente_nombre ?? ''));
        $first = $nombre !== '' ? explode(' ', $nombre)[0] : '';
        $text = $first !== ''
            ? "Hola {$first}, te escribimos de Estilo Dorado por tu pedido #{$id}."
            : "Hola, te escribimos de Estilo Dorado por tu pedido #{$id}.";

        return [
            'telefono_contacto' => $cel,
            'celular_fmt' => self::formato($cel),
            'wa_url' => self::waMe($cel, $text),
        ];
    }
}
