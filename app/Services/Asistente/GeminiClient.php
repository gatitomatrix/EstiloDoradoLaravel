<?php

namespace App\Services\Asistente;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    public function chat(string $system, string $user): ?string
    {
        $apiKey = config('llm.gemini.api_key');
        if (! $apiKey) {
            Log::warning('[gemini] GEMINI_API_KEY vacío');

            return null;
        }

        $model = config('llm.gemini.model', 'gemini-2.0-flash');
        $timeout = (int) config('llm.gemini.timeout', 45);
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            .$model.':generateContent?key='.urlencode($apiKey);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->post($url, [
                    'system_instruction' => [
                        'parts' => [['text' => $system]],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $user]],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.55,
                        'maxOutputTokens' => 700,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('[gemini] HTTP '.$response->status().' '.$response->body());

                return null;
            }

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

            return is_string($text) && trim($text) !== '' ? trim($text) : null;
        } catch (\Throwable $e) {
            Log::warning('[gemini] '.$e->getMessage());

            return null;
        }
    }
}
