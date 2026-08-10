<?php

namespace App\Services\Asistente;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;

class AsistenteService
{
    public function __construct(
        private OllamaClient $ollama,
        private GeminiClient $gemini,
    ) {}

    /**
     * @return array{reply:string,driver:string,products:array<int,array<string,mixed>>,pedido:?array<string,mixed>,suggestions:array<int,string>}
     */
    public function handle(string $message, ?Cliente $cliente = null): array
    {
        $message = trim($message);
        $products = $this->findProducts($message);
        $pedido = $this->findPedido($message, $cliente);
        $context = $this->buildContext($products, $pedido, $cliente);

        $driver = strtolower((string) config('llm.driver', 'ollama'));
        $reply = null;
        $used = 'rules';

        if (in_array($driver, ['ollama', 'gemini'], true)) {
            $system = $this->systemPrompt();
            $user = "Contexto de la tienda (usa SOLO estos datos):\n{$context}\n\nPregunta del cliente:\n{$message}";

            $reply = $driver === 'gemini'
                ? $this->gemini->chat($system, $user)
                : $this->ollama->chat($system, $user);

            if ($reply) {
                $used = $driver;
            } elseif (config('llm.fallback_rules', true)) {
                Log::info("[asistente] LLM {$driver} falló → reglas");
                $reply = $this->rulesReply($message, $products, $pedido, $cliente);
                $used = 'rules';
            } else {
                $reply = 'No pude consultar el asistente de IA en este momento. Intenta de nuevo o escribe "ayuda".';
                $used = $driver.'_error';
            }
        } else {
            $reply = $this->rulesReply($message, $products, $pedido, $cliente);
            $used = 'rules';
        }

        return [
            'reply' => $reply,
            'driver' => $used,
            'products' => array_map(fn (Producto $p) => $this->productCard($p), $products),
            'pedido' => $pedido,
            'suggestions' => $this->suggestions($cliente !== null),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'TXT'
Eres el asistente virtual de la tienda "Estilo Dorado" (regalos y detalles, Cerro de Pasco, Perú).
Responde SIEMPRE en español, de forma breve, clara y amable (máximo 120 palabras).
Solo usa precios, stock y pedidos del CONTEXTO. Si no hay dato, dilo y sugiere ver el catálogo en la app.
No inventes productos, promociones ni números de pedido.
Puedes orientar sobre: catálogo, stock, precios, cómo comprar, pago (tarjeta/Yape/efectivo en prueba), recojo en tienda o delivery, y estado de pedidos del cliente.
Si preguntan por un pedido y no hay datos en contexto, pide el número y que inicie sesión.
No des información de otros clientes.
TXT;
    }

    /**
     * @return list<Producto>
     */
    private function findProducts(string $message): array
    {
        $q = mb_strtolower($message);
        // quitar ruido
        foreach (['tienen', 'tiene', 'busco', 'quiero', 'precio', 'cuesta', 'stock', 'hay', 'de', 'el', 'la', 'un', 'una', 'me', 'por favor', 'hola'] as $w) {
            $q = preg_replace('/\b'.preg_quote($w, '/').'\b/u', ' ', $q) ?? $q;
        }
        $q = trim(preg_replace('/\s+/', ' ', $q) ?? '');

        $tokens = array_values(array_filter(explode(' ', $q), fn ($t) => mb_strlen($t) >= 3));

        $query = Producto::query()
            ->where(function ($b) {
                $b->whereNull('estado')->orWhere('estado', 'activo');
            });

        if ($tokens === []) {
            return $query->orderByDesc('id_producto')->limit(5)->get()->all();
        }

        $query->where(function ($b) use ($tokens) {
            foreach ($tokens as $t) {
                $b->orWhere('nombre', 'like', '%'.$t.'%')
                    ->orWhere('descripcion', 'like', '%'.$t.'%')
                    ->orWhere('slug', 'like', '%'.$t.'%');
            }
        });

        return $query->orderByDesc('stock')->limit(6)->get()->all();
    }

    /**
     * @return array<string,mixed>|null
     */
    private function findPedido(string $message, ?Cliente $cliente): ?array
    {
        if (! preg_match('/\b(?:pedido\s*#?\s*|n[uú]mero\s*|orden\s*#?\s*)?(\d{1,8})\b/iu', $message, $m)) {
            // "mi pedido" / "estado" sin número → último del cliente
            if ($cliente && preg_match('/pedido|compra|estado|env[ií]o|seguimiento/iu', $message)) {
                $p = Pedido::query()
                    ->where('id_cliente', $cliente->id_cliente)
                    ->orderByDesc('id_pedido')
                    ->first();

                return $p ? $this->pedidoCard($p) : null;
            }

            return null;
        }

        $id = (int) $m[1];
        $p = Pedido::query()->where('id_pedido', $id)->first();
        if (! $p) {
            return null;
        }

        // Solo el dueño (o sin auth no devolvemos detalle sensible)
        if (! $cliente || (int) $p->id_cliente !== (int) $cliente->id_cliente) {
            return [
                'id_pedido' => $id,
                'acceso' => 'restringido',
                'mensaje' => 'Inicia sesión con la cuenta que hizo el pedido para ver el detalle.',
            ];
        }

        return $this->pedidoCard($p);
    }

    /**
     * @param  list<Producto>  $products
     * @param  array<string,mixed>|null  $pedido
     */
    private function buildContext(array $products, ?array $pedido, ?Cliente $cliente): string
    {
        $lines = [];
        $lines[] = 'Cliente: '.($cliente
            ? ($cliente->nombre.' '.($cliente->apellido ?? '').' (id '.$cliente->id_cliente.')')
            : 'invitado (no autenticado)');

        $lines[] = 'Productos relevantes:';
        if ($products === []) {
            $lines[] = '- (ninguno encontrado por la consulta; sugiere catálogo general)';
        } else {
            foreach ($products as $p) {
                $lines[] = sprintf(
                    '- id=%d | %s | S/ %s | stock=%d',
                    $p->id_producto,
                    $p->nombre,
                    number_format((float) $p->precio_venta, 2, '.', ''),
                    (int) $p->stock
                );
            }
        }

        $lines[] = 'Pedido:';
        if ($pedido === null) {
            $lines[] = '- (sin pedido en contexto)';
        } else {
            $lines[] = '- '.json_encode($pedido, JSON_UNESCAPED_UNICODE);
        }

        $lines[] = 'Ayuda fija: pago con tarjeta (Culqi prueba), Yape o efectivo; entrega recojo en tienda o envío express; app móvil y web Estilo Dorado.';

        return implode("\n", $lines);
    }

    /**
     * @param  list<Producto>  $products
     * @param  array<string,mixed>|null  $pedido
     */
    private function rulesReply(string $message, array $products, ?array $pedido, ?Cliente $cliente): string
    {
        $m = mb_strtolower($message);

        if (preg_match('/hola|buenos|buenas|hey/u', $m)) {
            return '¡Hola! Soy el asistente de Estilo Dorado. Puedo ayudarte con productos, precios, stock, cómo comprar o el estado de tu pedido. ¿Qué necesitas?';
        }

        if (preg_match('/c[oó]mo (compro|comprar)|pago|yape|tarjeta|delivery|recojo|env[ií]o/u', $m)) {
            return "Para comprar: elige un producto → carrito → entrega (recojo en tienda o envío express) → pago con tarjeta de prueba, Yape o efectivo. "
                ."Si ya tienes un pedido pendiente, ve a Mis compras para pagarlo o ver el detalle.";
        }

        if ($pedido !== null) {
            if (($pedido['acceso'] ?? null) === 'restringido') {
                return 'Encontré un número de pedido, pero para ver su estado debes iniciar sesión con la cuenta que lo realizó.';
            }

            return sprintf(
                'Tu pedido #%s está en estado «%s». Total: S/ %s. Pago: %s. Entrega: %s.',
                $pedido['id_pedido'],
                $pedido['estado'],
                $pedido['total'],
                $pedido['forma_pago'] ?? '—',
                $pedido['direccion_entrega'] ?? '—'
            );
        }

        if (preg_match('/pedido|compra|estado/u', $m) && ! $cliente) {
            return 'Para consultar el estado de un pedido, inicia sesión y dime el número (ej. «pedido 12») o escribe «mi pedido».';
        }

        if ($products !== []) {
            // si la consulta parece de catálogo
            if (count($products) === 1) {
                $p = $products[0];
                $stock = (int) $p->stock;

                return sprintf(
                    '%s cuesta S/ %s. %s. Puedes abrirlo en el catálogo (id %d) y agregarlo al carrito.',
                    $p->nombre,
                    number_format((float) $p->precio_venta, 2, '.', ''),
                    $stock > 0 ? "Hay {$stock} unidad(es) en stock" : 'Por ahora está agotado',
                    $p->id_producto
                );
            }

            $list = collect($products)->take(4)->map(function (Producto $p) {
                return sprintf(
                    '• %s — S/ %s (stock %d)',
                    $p->nombre,
                    number_format((float) $p->precio_venta, 2, '.', ''),
                    (int) $p->stock
                );
            })->implode("\n");

            return "Encontré estos productos relacionados:\n{$list}\n¿Quieres el detalle de alguno?";
        }

        if (preg_match('/ayuda|que puedes|qu[eé] haces/u', $m)) {
            return 'Puedo: buscar productos y precios, indicar stock, explicar cómo comprar/pagar/entregar, y si inicias sesión, el estado de tus pedidos. Prueba: «tienen flores», «precio de…», «pedido 5».';
        }

        return 'No encontré un producto exacto con eso. Prueba con otra palabra del nombre o revisa el catálogo en Inicio. También puedes preguntar cómo comprar o el estado de un pedido.';
    }

    /**
     * @return array<string,mixed>
     */
    private function productCard(Producto $p): array
    {
        return [
            'id' => $p->id_producto,
            'nombre' => $p->nombre,
            'precio' => (float) $p->precio_venta,
            'stock' => (int) $p->stock,
            'imagen_url' => $p->imagen_url,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function pedidoCard(Pedido $p): array
    {
        return [
            'id_pedido' => $p->id_pedido,
            'estado' => $p->estado,
            'total' => number_format((float) $p->total, 2, '.', ''),
            'forma_pago' => $p->forma_pago,
            'direccion_entrega' => $p->direccion_entrega,
            'fecha_pedido' => optional($p->fecha_pedido)?->toDateTimeString(),
        ];
    }

    /**
     * @return list<string>
     */
    private function suggestions(bool $loggedIn): array
    {
        $base = [
            '¿Qué productos tienen?',
            '¿Cómo compro?',
            'Formas de pago',
        ];
        if ($loggedIn) {
            $base[] = 'Estado de mi pedido';
        }

        return $base;
    }
}
