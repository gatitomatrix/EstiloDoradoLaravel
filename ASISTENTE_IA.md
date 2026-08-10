# Asistente IA (chatbot) — local

## Qué hace

- Endpoint: `POST /api/asistente` body `{ "message": "..." }`
- Drivers: `rules` | `ollama` | `gemini` (ver `config/llm.php` y `.env`)
- Si Ollama/Gemini falla → fallback a **reglas** con catálogo real

## Local con Ollama (tu PC)

1. Ollama instalado y modelo listo:
   ```powershell
   ollama list
   # debe aparecer llama3.1:8b
   ```
2. En `.env` de Laravel:
   ```env
   LLM_DRIVER=ollama
   OLLAMA_URL=http://127.0.0.1:11434
   OLLAMA_MODEL=llama3.1:8b
   LLM_FALLBACK_RULES=true
   ```
3. `php artisan serve --host=0.0.0.0 --port=8000`
4. App móvil → icono robot / menú **Asistente IA**

## Producción (después)

```env
LLM_DRIVER=gemini
GEMINI_API_KEY=tu_key
GEMINI_MODEL=gemini-2.0-flash
```

No subas la key a GitHub.

## Prueba rápida API

```powershell
curl -X POST http://127.0.0.1:8000/api/asistente -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"message\":\"hola\"}"
```
