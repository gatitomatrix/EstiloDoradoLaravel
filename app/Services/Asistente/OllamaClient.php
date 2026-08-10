<?php

namespace App\Services\Asistente;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaClient
{
    public function chat(string $system, string $user): ?string
    {
        $base = config('llm.ollama.base_url');
        $model = config('llm.ollama.model');
        $timeout = (int) config('llm.ollama.timeout', 90);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($base.'/api/chat', [
                    'model' => $model,
                    'stream' => false,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'options' => [
                        'temperature' => 0.3,
                        'num_predict' => 400,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('[ollama] HTTP '.$response->status().' '.$response->body());

                return null;
            }

            $content = data_get($response->json(), 'message.content');

            return is_string($content) && trim($content) !== '' ? trim($content) : null;
        } catch (\Throwable $e) {
            Log::warning('[ollama] '.$e->getMessage());

            return null;
        }
    }
}
