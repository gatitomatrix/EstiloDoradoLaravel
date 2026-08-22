<?php

namespace App\Services\Envio;

/**
 * Estimado tipo Shalom desde Huancayo. No es cotización en vivo.
 */
class TarifaEnvio
{
    public static function estimar(?string $departamento, ?string $provincia): array
    {
        $d = self::norm($departamento);
        $p = self::norm($provincia);

        if ($p !== '' && str_contains($p, 'HUANCAYO')) {
            return [
                'costo' => 8.0,
                'zona' => 'huancayo',
                'etiqueta' => 'Huancayo (misma ciudad) · estimado Shalom',
            ];
        }
        if ($d !== '' && str_contains($d, 'JUNIN')) {
            return [
                'costo' => 12.0,
                'zona' => 'junin',
                'etiqueta' => 'Junín (otras provincias) · estimado Shalom',
            ];
        }
        if (str_contains($d, 'LIMA') || str_contains($d, 'CALLAO') || str_contains($p, 'CALLAO') || str_contains($p, 'LIMA')) {
            return [
                'costo' => 18.0,
                'zona' => 'lima',
                'etiqueta' => 'Lima / Callao · estimado Shalom',
            ];
        }

        return [
            'costo' => 25.0,
            'zona' => 'resto',
            'etiqueta' => 'Resto del Perú · estimado Shalom',
        ];
    }

    public static function recojo(): array
    {
        return [
            'costo' => 0.0,
            'zona' => 'tienda',
            'etiqueta' => 'Recojo en tienda',
        ];
    }

    private static function norm(?string $s): string
    {
        $s = mb_strtoupper(trim((string) $s));
        $s = strtr($s, ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N']);

        return $s;
    }
}
