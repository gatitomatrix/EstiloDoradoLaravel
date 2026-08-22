<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Envio\TarifaEnvio;
use Illuminate\Http\Request;

class EnvioController extends Controller
{
    public function estimado(Request $r)
    {
        $modo = strtolower((string) $r->query('modo', 'express'));
        if (in_array($modo, ['retiro', 'pickup', 'tienda'], true)) {
            return response()->json(['success' => true] + TarifaEnvio::recojo());
        }

        $t = TarifaEnvio::estimar($r->query('departamento'), $r->query('provincia'));

        return response()->json(['success' => true] + $t);
    }
}
