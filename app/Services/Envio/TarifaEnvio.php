<?php

namespace App\Services\Envio;

/**
 * Estimado tipo Shalom desde Huancayo.
 * Cobertura: Lima, Callao, Junín (Huancayo) y Pasco (Cerro de Pasco).
 */
class TarifaEnvio
{
    public static function cubre(?string $departamento, ?string $provincia = null, ?string $texto = null): bool
    {
        $blob = self::norm($departamento.' '.$provincia.' '.$texto);
        if ($blob === '') {
            return false;
        }
        foreach (['HUANCAYO', 'JUNIN', 'LIMA', 'CALLAO', 'PASCO', 'CERRO DE PASCO'] as $k) {
            if (str_contains($blob, $k)) {
                return true;
            }
        }

        return false;
    }

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
        if (str_contains($d, 'PASCO') || str_contains($p, 'PASCO') || str_contains($p, 'CERRO DE PASCO')) {
            return [
                'costo' => 14.0,
                'zona' => 'pasco',
                'etiqueta' => 'Pasco / Cerro de Pasco · estimado Shalom',
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
            'costo' => 0.0,
            'zona' => 'fuera',
            'etiqueta' => 'Fuera de cobertura. Enviamos a Lima, Callao, Junín y Pasco.',
        ];
    }

    public const DIRECCION_TIENDA = 'Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.';

    public static function recojo(): array
    {
        return [
            'costo' => 0.0,
            'zona' => 'tienda',
            'etiqueta' => 'Recojo en tienda',
            'direccion' => self::DIRECCION_TIENDA,
        ];
    }

    private static function norm(?string $s): string
    {
        $s = mb_strtoupper(trim((string) $s));
        $s = strtr($s, ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N']);

        return $s;
    }
}
