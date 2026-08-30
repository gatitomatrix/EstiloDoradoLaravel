<?php

namespace App\Services\Asistente;

/**
 * Edad (~10/20/30/40/50/60) y género a partir del mensaje.
 * No usa la edad del comprador: solo para quién es el regalo.
 */
class AudienceSegment
{
    public ?int $edad = null;

    public ?string $genero = null; // mujer | hombre

    public function parse(string $message): self
    {
        $m = mb_strtolower($message);
        $s = new self;

        if (preg_match('/(\d{1,2})\s*(años|ano|año)/u', $m, $hit)) {
            $s->edad = self::band((int) $hit[1]);
        } elseif (preg_match('/\b(diez)\b/u', $m)) {
            $s->edad = 10;
        } elseif (preg_match('/\b(veinte|veinteañer)/u', $m)) {
            $s->edad = 20;
        } elseif (preg_match('/\b(treinta)/u', $m)) {
            $s->edad = 30;
        } elseif (preg_match('/\b(cuarenta)/u', $m)) {
            $s->edad = 40;
        } elseif (preg_match('/\b(cincuenta)/u', $m)) {
            $s->edad = 50;
        } elseif (preg_match('/\b(sesenta)/u', $m)) {
            $s->edad = 60;
        } elseif (preg_match('/niñ[oa]|nienito|ninita|pequeñ|chibolo|wawa|infantil/u', $m)) {
            $s->edad = 10;
        } elseif (preg_match('/adolescente|teen|juvenil/u', $m)) {
            $s->edad = 20;
        } elseif (preg_match('/abuel[oa]|anciano|tercera\s+edad/u', $m)) {
            $s->edad = 60;
        } elseif (preg_match('/mam[aá]|madre|pap[aá]|padre|suegr/u', $m)) {
            $s->edad = 50;
        } elseif (preg_match('/\b(hijo|hija)\b/u', $m) && preg_match('/peque|niñ/u', $m)) {
            $s->edad = 10;
        }

        $mujer = (bool) preg_match('/mujer|chica|dama|se[nñ]orita|novia|mam[aá]|madre|hermana|t[ií]a|abuela|hija|niña|esposa|prima\b/u', $m);
        $hombre = (bool) preg_match('/\b(hombre|caballero|chico|var[oó]n|novio|pap[aá]|padre|hermano|t[ií]o|abuelo|hijo|niño|esposo|marido|primo)\b/u', $m);

        if ($mujer && ! $hombre) {
            $s->genero = 'mujer';
        } elseif ($hombre && ! $mujer) {
            $s->genero = 'hombre';
        } elseif (preg_match('/niña|hija|mam[aá]|novia/u', $m)) {
            $s->genero = 'mujer';
        } elseif (preg_match('/niño|hijo|pap[aá]|novio/u', $m)) {
            $s->genero = 'hombre';
        }

        return $s;
    }

    public static function band(int $n): int
    {
        if ($n <= 14) {
            return 10;
        }
        if ($n <= 24) {
            return 20;
        }
        if ($n <= 34) {
            return 30;
        }
        if ($n <= 44) {
            return 40;
        }
        if ($n <= 54) {
            return 50;
        }

        return 60;
    }

    public function label(): string
    {
        $g = $this->genero === 'mujer' ? 'mujer' : ($this->genero === 'hombre' ? 'hombre' : 'sin género indicado');
        $e = $this->edad ? 'edad aprox. '.$this->edad.' años' : 'edad no indicada';

        return $g.'; '.$e;
    }

    public function extraTokens(): array
    {
        $extra = [];
        $e = $this->edad;
        $g = $this->genero;

        if ($e === 10) {
            $extra = array_merge($extra, ['peluche', 'cajita', 'cartel', 'stich', 'infantil']);
            if ($g !== 'mujer') {
                $extra[] = 'hot wheels';
                $extra[] = 'caja';
            }
        } elseif ($e === 20) {
            $extra = array_merge($extra, ['detalle', 'cajita', 'peluche']);
            if ($g !== 'hombre') {
                $extra[] = 'flores';
            }
            if ($g === 'hombre') {
                $extra[] = 'accesorio';
            }
        } elseif (in_array($e, [30, 40], true)) {
            $extra[] = 'detalle';
            if ($g === 'hombre') {
                $extra = array_merge($extra, ['billetera', 'caballero', 'accesorio']);
            } else {
                $extra = array_merge($extra, ['flores', 'cajita']);
            }
        } elseif (in_array($e, [50, 60], true)) {
            if ($g === 'hombre') {
                $extra = array_merge($extra, ['billetera', 'caballero', 'accesorio']);
            } else {
                $extra = array_merge($extra, ['flores', 'detalle']);
            }
        }

        if ($g === 'mujer' && $e !== 10) {
            $extra[] = 'flores';
        }
        if ($g === 'hombre' && ($e === null || $e >= 20)) {
            $extra[] = 'caballero';
            $extra[] = 'billetera';
        }

        return $extra;
    }

    /** Etiquetas que suman puntaje si el producto las tiene. */
    public function boostTags(): array
    {
        $t = [];
        if ($this->genero === 'mujer') {
            $t[] = 'mujer';
            $t[] = 'dama';
            $t[] = 'novia';
        }
        if ($this->genero === 'hombre') {
            $t[] = 'hombre';
            $t[] = 'caballero';
        }
        if ($this->edad === 10) {
            $t = array_merge($t, ['nino', 'niño', 'infantil', 'edad:10']);
        }
        if ($this->edad === 20) {
            $t = array_merge($t, ['joven', 'edad:20']);
        }
        if (in_array($this->edad, [30, 40], true)) {
            $t = array_merge($t, ['adulto', 'edad:'.$this->edad]);
        }
        if (in_array($this->edad, [50, 60], true)) {
            $t = array_merge($t, ['mayor', 'adulto', 'edad:'.$this->edad]);
        }

        return $t;
    }

    public function shouldAsk(): bool
    {
        return $this->edad === null || $this->genero === null;
    }
}
