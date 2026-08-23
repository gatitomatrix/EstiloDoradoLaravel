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
            'offered_ids' => 'sometimes|array|max:12',
            'offered_ids.*' => 'integer|min:1',
        ]);

        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }
        @ini_set('max_execution_time', '180');

        $cliente = $this->resolveCliente($request);

        try {
            $offered = array_values(array_map('intval', $data['offered_ids'] ?? []));
            $result = $asistente->handle($data['message'], $cliente, $offered);

            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
                'driver' => $result['driver'],
                'products' => $result['products'],
                'pedido' => $result['pedido'],
                'suggestions' => $result['suggestions'],
                'action' => $result['action'],
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
                'action' => null,
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
