<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\Asistente\AsistenteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class AsistenteController extends Controller
{
    public function chat(Request $request, AsistenteService $asistente)
    {
        $data = $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $cliente = $this->resolveCliente($request);

        try {
            $result = $asistente->handle($data['message'], $cliente);

            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
                'driver' => $result['driver'],
                'products' => $result['products'],
                'pedido' => $result['pedido'],
                'suggestions' => $result['suggestions'],
            ]);
        } catch (\Throwable $e) {
            Log::error('[asistente] '.$e->getMessage());

            return response()->json([
                'success' => false,
                'reply' => 'Hubo un problema al procesar tu mensaje. Intenta de nuevo en un momento.',
                'driver' => 'error',
                'products' => [],
                'pedido' => null,
                'suggestions' => ['¿Cómo compro?', '¿Qué productos tienen?'],
            ], 500);
        }
    }

    private function resolveCliente(Request $request): ?Cliente
    {
        $user = $request->user();
        if ($user instanceof Cliente) {
            return $user;
        }

        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        $access = PersonalAccessToken::findToken($token);
        $tokenable = $access?->tokenable;

        return $tokenable instanceof Cliente ? $tokenable : null;
    }
}
