<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\Asistente\AsistenteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

class AsistenteController extends Controller
{
    public function chat(Request $request, AsistenteService $asistente)
    {
        $data = $request->validate([
            'message' => 'required|string|min:1|max:1000',
            'offered_ids' => 'sometimes|array|max:12',
            'offered_ids.*' => 'integer|min:1',
            'awaiting' => 'sometimes|nullable|string|max:40',
            'complaint' => 'sometimes|nullable|array',
            'complaint.tipo' => 'sometimes|nullable|string|max:40',
            'complaint.pedido_id' => 'sometimes|nullable|integer',
            'complaint.phone' => 'sometimes|nullable|string|max:20',
            'complaint.mensaje' => 'sometimes|nullable|string|max:300',
        ]);

        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }
        @ini_set('max_execution_time', '180');

        $cliente = $this->resolveCliente($request);

        try {
            $offered = array_values(array_map('intval', $data['offered_ids'] ?? []));
            $result = $asistente->handle(
                $data['message'],
                $cliente,
                $offered,
                $data['awaiting'] ?? null,
                $data['complaint'] ?? []
            );

            $this->logConsulta($data['message'], $result);

            $actions = $result['actions'] ?? [];
            if ($actions === [] && ! empty($result['action'])) {
                $actions = [$result['action']];
            }

            return response()->json([
                'success' => true,
                'reply' => $result['reply'],
                'driver' => $result['driver'],
                'products' => $result['products'],
                'pedido' => $result['pedido'],
                'suggestions' => $result['suggestions'],
                'action' => $result['action'],
                'actions' => $actions,
                'pedidos' => $result['pedidos'] ?? [],
                'awaiting' => $result['awaiting'] ?? null,
                'complaint' => $result['complaint'] ?? null,
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

    /** No corta el chat si falla. Omite saludos/gracias. */
    private function logConsulta(string $message, array $result): void
    {
        try {
            if (! Schema::hasTable('asistente_logs')) {
                return;
            }
            $m = mb_strtolower(trim($message));
            if (preg_match('/^(hola|buenos\s*d[ií]as|buenas|gracias|genial|ok+|listo|chau|adi[oó]s)[\s!¡.]*$/u', $m)) {
                return;
            }
            $n = is_array($result['products'] ?? null) ? count($result['products']) : 0;
            $nombres = [];
            $cards = [];
            foreach ($result['products'] ?? [] as $p) {
                if (! is_array($p) || empty($p['nombre'])) {
                    continue;
                }
                $nombres[] = $p['nombre'];
                $cards[] = [
                    'id' => (int) ($p['id'] ?? 0),
                    'nombre' => $p['nombre'],
                    'precio' => $p['precio'] ?? null,
                    'stock' => $p['stock'] ?? null,
                    'imagen_url' => $p['imagen_url'] ?? null,
                ];
            }
            $wa = ! empty($result['action']) && (($result['action']['type'] ?? '') === 'whatsapp');
            $tipo = $result['log_tipo'] ?? ($wa ? 'whatsapp' : ($n > 0 ? 'catalogo' : 'sin_producto'));
            $row = [
                'mensaje' => mb_substr($message, 0, 500),
                'tipo' => $tipo,
                'n_productos' => min($n, 99),
                'whatsapp' => $wa ? 1 : 0,
                'driver' => is_string($result['driver'] ?? null) ? substr($result['driver'], 0, 16) : null,
                'created_at' => now(),
            ];
            if (Schema::hasColumn('asistente_logs', 'productos')) {
                $row['productos'] = $nombres ? mb_substr(implode(', ', $nombres), 0, 500) : null;
            }
            if (Schema::hasColumn('asistente_logs', 'productos_json')) {
                $row['productos_json'] = $cards ? json_encode($cards, JSON_UNESCAPED_UNICODE) : null;
            }
            if (Schema::hasColumn('asistente_logs', 'queja_tipo')) {
                $row['queja_tipo'] = isset($result['queja_tipo']) ? substr((string) $result['queja_tipo'], 0, 40) : null;
            }
            if (Schema::hasColumn('asistente_logs', 'urgencia')) {
                $row['urgencia'] = ! empty($result['urgencia']) ? 1 : 0;
            }
            DB::table('asistente_logs')->insert($row);
        } catch (\Throwable $e) {
            Log::warning('[asistente-log] '.$e->getMessage());
        }
    }
}
