<?php

namespace App\Support;

class Celular
{
    /** Normaliza a 9 dígitos (9xxxxxxxx) o null. */
    public static function normalizar(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw) ?? '';
        if (str_starts_with($d, '51') && strlen($d) === 11) {
            $d = substr($d, 2);
        }
        if (preg_match('/^9\d{8}$/', $d)) {
            return $d;
        }

        return null;
    }

    public static function desdePedido($pedido): ?string
    {
        if (! $pedido) {
            return null;
        }
        if (! empty($pedido->telefono_contacto)) {
            return self::normalizar((string) $pedido->telefono_contacto);
        }
        if (! empty($pedido->observacion) && preg_match('/\[CEL:(\d{9})\]/', (string) $pedido->observacion, $m)) {
            return $m[1];
        }

        return null;
    }
}
