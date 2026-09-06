<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsultaRucController extends Controller
{
    private const DEPS = [
        '01' => 'Amazonas', '02' => 'Áncash', '03' => 'Apurímac', '04' => 'Arequipa',
        '05' => 'Ayacucho', '06' => 'Cajamarca', '07' => 'Callao', '08' => 'Cusco',
        '09' => 'Huancavelica', '10' => 'Huánuco', '11' => 'Ica', '12' => 'Junín',
        '13' => 'La Libertad', '14' => 'Lambayeque', '15' => 'Lima', '16' => 'Loreto',
        '17' => 'Madre de Dios', '18' => 'Moquegua', '19' => 'Pasco', '20' => 'Piura',
        '21' => 'Puno', '22' => 'San Martín', '23' => 'Tacna', '24' => 'Tumbes', '25' => 'Ucayali',
    ];

    public function show(Request $request, string $ruc)
    {
        $ruc = preg_replace('/\D/', '', $ruc) ?? '';
        if (strlen($ruc) !== 11) {
            return response()->json(['ok' => false, 'message' => 'El RUC debe tener 11 dígitos.'], 422);
        }

        $data = Cache::remember('ruc:'.$ruc, now()->addDays(7), function () use ($ruc) {
            try {
                $res = Http::timeout(6)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'User-Agent' => 'EstiloDorado/1.0',
                    ])
                    ->get('https://openruc.com/api/ruc/'.$ruc);

                if (! $res->ok()) {
                    return null;
                }
                $j = $res->json();
                if (! is_array($j) || empty($j['razon_social'])) {
                    return null;
                }

                $ubi = preg_replace('/\D/', '', (string) ($j['ubigeo'] ?? '')) ?? '';
                $dep = strlen($ubi) >= 2 ? (self::DEPS[substr($ubi, 0, 2)] ?? null) : null;

                return [
                    'ruc' => $j['ruc'] ?? $ruc,
                    'razon_social' => $j['razon_social'],
                    'direccion' => $j['direccion'] ?? '',
                    'estado' => $j['estado'] ?? null,
                    'condicion' => $j['condicion'] ?? null,
                    'departamento' => $dep,
                    'ubigeo' => $ubi ?: null,
                ];
            } catch (\Throwable $e) {
                Log::info('[ruc] consulta falló', ['ruc' => $ruc, 'err' => $e->getMessage()]);

                return null;
            }
        });

        if (! $data) {
            return response()->json(['ok' => false, 'message' => 'No se encontró el RUC. Completa los datos a mano.'], 404);
        }

        return response()->json(['ok' => true, 'data' => $data]);
    }
}
