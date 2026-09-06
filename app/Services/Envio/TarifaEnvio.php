<?php

namespace App\Services\Envio;

/**
 * Pasco: solo domicilio S/5 (sin Shalom).
 * Huancayo y Lima–Callao: agencia S/12; domicilio +5 (HYO=17) o +10 (Lima=22).
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

    public static function zona(?string $departamento, ?string $provincia = null, ?string $distrito = null, ?string $texto = null): string
    {
        $blob = self::norm($departamento.' '.$provincia.' '.$distrito.' '.$texto);
        if ($blob === '') {
            return 'fuera';
        }
        if (str_contains($blob, 'PASCO')) {
            return 'pasco';
        }
        if (str_contains($blob, 'HUANCAYO') || str_contains($blob, 'JUNIN') || str_contains($blob, 'CHILCA') || str_contains($blob, 'EL TAMBO')) {
            return 'huancayo';
        }
        if (str_contains($blob, 'LIMA') || str_contains($blob, 'CALLAO')) {
            return 'lima';
        }

        return 'fuera';
    }

    /** @return array{costo:float,zona:string,etiqueta:string} */
    public static function costo(?string $departamento, ?string $provincia, ?string $distrito, string $tipo = 'AGENCIA', ?string $texto = null): array
    {
        $zona = self::zona($departamento, $provincia, $distrito, $texto);
        $tipo = strtoupper($tipo) === 'DOMICILIO' ? 'DOMICILIO' : 'AGENCIA';

        if ($zona === 'fuera') {
            return ['costo' => 0.0, 'zona' => 'fuera', 'etiqueta' => 'Fuera de cobertura'];
        }
        if ($zona === 'pasco') {
            return ['costo' => 5.0, 'zona' => 'pasco', 'etiqueta' => 'Envío a domicilio Pasco'];
        }
        if ($tipo === 'AGENCIA') {
            $donde = $zona === 'huancayo' ? 'Huancayo' : 'Lima – Callao';

            return ['costo' => 12.0, 'zona' => $zona, 'etiqueta' => "Envío Shalom agencia {$donde}"];
        }
        if ($zona === 'huancayo') {
            return ['costo' => 17.0, 'zona' => 'huancayo', 'etiqueta' => 'Envío Shalom + domicilio Huancayo'];
        }

        return ['costo' => 22.0, 'zona' => 'lima', 'etiqueta' => 'Envío Shalom + domicilio Lima – Callao'];
    }

    /** @param array<string,mixed> $data */
    public static function desdeRequest(array $data): array
    {
        $dir = (string) ($data['direccion_entrega'] ?? '');
        $pago = (string) ($data['forma_pago'] ?? '');
        if ($pago === 'efectivo' || str_contains(mb_strtoupper($dir), 'RETIRO')) {
            return ['costo' => 0.0, 'zona' => 'tienda', 'etiqueta' => 'Recojo en tienda'];
        }

        $ub = is_array($data['ubigeo'] ?? null) ? $data['ubigeo'] : [];
        $dep = $ub['departamento'] ?? null;
        $prov = $ub['provincia'] ?? null;
        $dist = $ub['distrito'] ?? null;
        $tipo = (string) ($data['envio_tipo'] ?? 'AGENCIA');

        if (!$dep && $dir !== '') {
            $parsed = self::parseDireccion($dir);
            $dep = $parsed['departamento'];
            $prov = $parsed['provincia'];
            $dist = $parsed['distrito'];
        }

        return self::costo($dep, $prov, $dist, $tipo, $dir);
    }

    /** @return array{departamento:?string,provincia:?string,distrito:?string} */
    public static function parseDireccion(string $dir): array
    {
        $tail = $dir;
        if (str_contains($dir, '–')) {
            $parts = explode('–', $dir);
            $tail = trim((string) end($parts));
        } elseif (str_contains($dir, '-')) {
            $parts = explode('-', $dir);
            $tail = trim((string) end($parts));
        }
        $bits = array_values(array_filter(array_map('trim', explode('/', $tail))));

        return [
            'distrito' => $bits[0] ?? null,
            'provincia' => $bits[1] ?? null,
            'departamento' => $bits[2] ?? ($bits[1] ?? null),
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
