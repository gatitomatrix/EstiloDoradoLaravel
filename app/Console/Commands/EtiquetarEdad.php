<?php

namespace App\Console\Commands;

use App\Models\Producto;
use Illuminate\Console\Command;

class EtiquetarEdad extends Command
{
    protected $signature = 'estilo:etiquetar-edad';

    protected $description = 'Fusiona etiquetas de edad/género (edad:10…60, nino, joven, adulto, mayor, mujer, hombre) sin borrar las existentes';

    public function handle(): int
    {
        $n = 0;
        Producto::query()->orderBy('id_producto')->each(function (Producto $p) use (&$n) {
            $add = $this->infer($p);
            if ($add === []) {
                return;
            }
            $cur = array_filter(array_map('trim', explode(',', mb_strtolower((string) $p->etiquetas))));
            $merged = array_values(array_unique(array_merge($cur, $add)));
            $p->etiquetas = implode(', ', $merged);
            $p->save();
            $n++;
        });
        $this->info("Actualizados {$n} productos (etiquetas fusionadas).");

        return self::SUCCESS;
    }

    private function infer(Producto $p): array
    {
        $h = mb_strtolower($p->nombre.' '.($p->etiquetas ?? '').' '.($p->descripcion ?? ''));
        $t = [];

        $nino = (bool) preg_match('/peluche|stich|hot wheels|cerdita|muñec|munec|infantil|niñ|cartel/u', $h);
        $flores = (bool) preg_match('/flores/u', $h);
        $hombre = (bool) preg_match('/billetera|caballero|puma|militar|hombre/u', $h);
        $mujer = (bool) preg_match('/novia|dama|mujer|romance|perfume/u', $h);
        $detalle = (bool) preg_match('/detalle|cajita|caja /u', $h);

        if ($nino) {
            $t = array_merge($t, ['nino', 'infantil', 'edad:10', 'edad:20']);
        }
        if ($flores) {
            $t = array_merge($t, ['mujer', 'joven', 'adulto', 'edad:20', 'edad:30', 'edad:40', 'edad:50', 'edad:60']);
        }
        if ($hombre) {
            $t = array_merge($t, ['hombre', 'caballero', 'adulto', 'edad:20', 'edad:30', 'edad:40', 'edad:50', 'edad:60']);
        }
        if ($mujer && ! $nino) {
            $t = array_merge($t, ['mujer', 'edad:20', 'edad:30', 'edad:40']);
        }
        if ($detalle && ! $hombre) {
            $t = array_merge($t, ['joven', 'adulto', 'edad:20', 'edad:30', 'edad:40']);
        }

        return array_values(array_unique($t));
    }
}
