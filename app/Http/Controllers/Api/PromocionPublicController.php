<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PrecioService;

class PromocionPublicController extends Controller
{
    public function activa(PrecioService $precios)
    {
        $c = $precios->campanaActiva();
        if (! $c) {
            return response()->json(['activa' => false]);
        }
        $texto = trim((string) $c->texto_cinta);
        if ($texto === '') {
            $texto = sprintf('¡%.0f%% de descuento en toda la tienda!', (float) $c->porcentaje);
        }

        return response()->json([
            'activa' => true,
            'titulo' => $c->titulo,
            'texto' => $texto,
            'porcentaje' => (float) $c->porcentaje,
            'fecha_fin' => optional($c->fecha_fin)?->format('Y-m-d'),
        ]);
    }
}
