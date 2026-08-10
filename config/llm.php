<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Driver: rules | ollama | gemini
    |--------------------------------------------------------------------------
    | - rules: solo catálogo / plantillas (sin LLM)
    | - ollama: local http://127.0.0.1:11434
    | - gemini: API Google (producción o free tier)
    */
    'driver' => env('LLM_DRIVER', 'ollama'),

    'ollama' => [
        'base_url' => rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/'),
        'model' => env('OLLAMA_MODEL', 'llama3.1:8b'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 90),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 45),
    ],

    /** Si el LLM falla, usar reglas automáticamente */
    'fallback_rules' => filter_var(env('LLM_FALLBACK_RULES', true), FILTER_VALIDATE_BOOL),
];
