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

    public function handle(string $message, ?Cliente $cliente = null, array $offeredIds = []): array
    {
        $message = trim($message);
        $intent = $this->detectIntent($message, $offeredIds !== []);

        if ($intent === 'add_to_cart') {
            return $this->handleAddToCart($message, $offeredIds);
        }

        $products = $this->findProducts($message, $intent);
        $pedido = $this->findPedido($message, $cliente);
        $catalogCount = (int) Producto::query()
            ->where(function ($b) {
                $b->whereNull('estado')->orWhere('estado', 'activo');
            })
            ->count();

        $context = $this->buildContext($products, $pedido, $cliente, $catalogCount, $intent);

        $driver = strtolower((string) config('llm.driver', 'ollama'));
        $reply = null;
        $used = 'rules';

        if (in_array($driver, ['ollama', 'gemini'], true)) {
            $system = $this->systemPrompt();
            $user = "Contexto de la tienda (usa SOLO estos datos; no inventes):\n{$context}\n\nPregunta del cliente:\n{$message}";

            $reply = $driver === 'gemini'
                ? $this->gemini->chat($system, $user)
                : $this->ollama->chat($system, $user);

            if ($reply) {
                $used = $driver;
            } elseif (config('llm.fallback_rules', true)) {
                Log::info("[asistente] LLM {$driver} falló → reglas");
                $reply = $this->rulesReply($message, $products, $pedido, $cliente, $catalogCount, $intent);
                $used = 'rules';
            } else {
                $reply = 'No pude consultar el asistente de IA en este momento. Intenta de nuevo o escribe "ayuda".';
                $used = $driver.'_error';
            }
        } else {
            $reply = $this->rulesReply($message, $products, $pedido, $cliente, $catalogCount, $intent);
            $used = 'rules';
        }

        $showProducts = in_array($intent, ['product', 'catalog', 'mixed'], true) && $products !== [];

        return [
            'reply' => $reply,
            'driver' => $used,
            'products' => $showProducts
                ? array_map(fn (Producto $p) => $this->productCard($p), $products)
                : [],
            'pedido' => $pedido,
            'suggestions' => $this->suggestions($cliente !== null),
            'action' => null,
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'TXT'
Eres el asistente virtual de la tienda "Estilo Dorado" (regalos y detalles personalizados, Cerro de Pasco, Perú).
Responde SIEMPRE en español, breve y amable (máximo 100 palabras).

REGLAS ESTRICTAS:
1) Usa SOLO precios, stock, nombres y totales del CONTEXTO. No inventes productos ni cantidades.
2) El campo "total_productos_activos" es el número real del catálogo. Si te preguntan cuántos hay, usa ese número.
3) Si un producto aparece en "Productos encontrados", existe. Di su precio y stock exactos.
4) Si "Productos encontrados" está vacío y buscan un artículo, di que no lo hallaste y sugiere el catálogo de la app.
5) NO digas que hay que descargar de App Store/Google Play ni inventes dominios (no uses www.estilodorado.com).
6) Registro: en la app o web, perfil → Regístrate (correo y contraseña) o «Continuar con Google» (Gmail).
7) Compra: producto → carrito → entrega (recojo en tienda o envío express) → pago (tarjeta Culqi en prueba, Yape o efectivo).
8) Pedidos: solo con datos del contexto; si no hay, pide iniciar sesión y el número de pedido.
9) Si preguntan por comida u otro rubro ajeno, indícalo con amabilidad y ofrece regalos/detalles del catálogo.
10) Si "Productos encontrados" está vacío: di que NO hay ese artículo. NO nombres un producto concreto (ni Cerdita ni otro) como si fuera lo que pidieron. Invita a buscar en Inicio.
11) Si hay productos en la lista, ofrécelos. Si pidieron "ositos" y la lista está vacía, no inventes peluches de oso.
12) NUNCA digas que ya agregaste algo al carrito. Solo puedes invitar a usar el botón "Agregar" o a confirmar.
13) No des información de otros clientes.
TXT;
    }

    private function detectIntent(string $message, bool $hasOffered = false): string
    {
        $m = mb_strtolower($message);

        $addCue = (bool) preg_match('/agrega|a[nñ]ade|al\s+carrito|me\s+llevo|ponme|ponlo|quiero\s+(esa|ese|esta|este|la|el|una|uno|\d)|la\s+primera|la\s+segunda|la\s+\d/u', $m);
        if ($hasOffered && $addCue) {
            return 'add_to_cart';
        }
        if ($hasOffered && preg_match('/^(quiero|dame|me\s+das)\b/u', $m)) {
            return 'add_to_cart';
        }

        if (preg_match('/registr|cuenta|usuario|crear\s*cuenta|sign\s*up|login|iniciar\s*sesi/u', $m)) {
            return 'account';
        }
        if (preg_match('/c[oó]mo\s+compr|pasos|carrito|delivery|recojo|env[ií]o/u', $m)
            && ! preg_match('/agrega|a[nñ]ade/u', $m)) {
            return 'howto';
        }
        if (preg_match('/yape|tarjeta|culqi|efectivo|forma[s]?\s+de\s+pago|m[eé]todo[s]?\s+de\s+pago|pagar/u', $m)
            && ! preg_match('/busco|producto|cuesta|precio|stock|cerdit|cajit|flores|billetera/u', $m)) {
            return 'payment';
        }
        if (preg_match('/pedido|seguimiento|estado\s+de\s+mi/u', $m)) {
            return 'order';
        }
        if (preg_match('/cu[aá]ntos\s+product|cu[aá]ntas\s+cosas|total\s+del\s+cat[aá]logo|variedad/u', $m)) {
            return 'catalog_count';
        }
        if (preg_match('/qu[eé]\s+product|cat[aá]logo|qu[eé]\s+venden|qu[eé]\s+tienen/u', $m)) {
            return 'catalog';
        }
        if (preg_match('/hola|buenos|buenas|hey|ayuda|qu[eé]\s+puedes/u', $m)
            && ! preg_match('/busco|precio|stock|compr|product|pedido/u', $m)) {
            return 'help';
        }
        if (preg_match('/hambre|comida|pizza|hamburg|almorz|cenar|restaurante/u', $m)
            && ! preg_match('/busco|cerdit|cajit|regalo|flores/u', $m)) {
            return 'offtopic';
        }
        if (preg_match('/busco|precio|cuesta|stock|tienen|hay\s|quiero|cerdit|cajit|flores|billetera|hot\s*wheels|personaliz/u', $m)) {
            return 'product';
        }
        if (preg_match('/busco|cerdit|product/u', $m) && preg_match('/compr|pago|yape/u', $m)) {
            return 'mixed';
        }

        return 'product';
    }

    private function handleAddToCart(string $message, array $offeredIds): array
    {
        $empty = [
            'driver' => 'rules',
            'pedido' => null,
            'suggestions' => ['¿Qué productos tienen?', 'Cerdita tiburón', '¿Cómo compro?'],
        ];

        if ($offeredIds === []) {
            return $empty + [
                'reply' => 'Primero te muestro opciones del catálogo. Pregunta por un producto (ej. «cerdita» o «peluches») y luego dime «quiero esa» o usa el botón Agregar.',
                'products' => [],
                'action' => null,
            ];
        }

        $offered = Producto::query()
            ->whereIn('id_producto', $offeredIds)
            ->where(function ($b) {
                $b->whereNull('estado')->orWhere('estado', 'activo');
            })
            ->get();

        if ($offered->isEmpty()) {
            return $empty + [
                'reply' => 'Las opciones anteriores ya no están disponibles. Busca de nuevo el producto y te las vuelvo a mostrar.',
                'products' => [],
                'action' => null,
            ];
        }

        $qty = 1;
        if (preg_match('/\b(\d{1,2})\s*(unidad|unidades|x)?\b/u', mb_strtolower($message), $qm)) {
            $qty = max(1, min(20, (int) $qm[1]));
        }

        $picked = $this->matchOffered($message, $offered);

        if (count($picked) === 1) {
            $p = $picked[0];
            $stock = (int) $p->stock;
            $card = $this->productCard($p);
            if ($stock < 1) {
                return $empty + [
                    'reply' => $p->nombre.' está agotado por ahora. Elige otro de la lista o busca de nuevo.',
                    'products' => [$card],
                    'action' => null,
                ];
            }
            $qty = min($qty, $stock);

            return $empty + [
                'reply' => sprintf(
                    '¿Agrego %s × %d (S/ %s c/u) al carrito? Confirma y uso el mismo carrito de la app, con el stock real.',
                    $p->nombre,
                    $qty,
                    number_format((float) $p->precio_venta, 2, '.', '')
                ),
                'products' => [$card],
                'action' => [
                    'type' => 'confirm_add',
                    'id' => $p->id_producto,
                    'qty' => $qty,
                    'nombre' => $p->nombre,
                    'precio' => (float) $p->precio_venta,
                    'stock' => $stock,
                    'imagen_url' => $p->imagen_url,
                ],
            ];
        }

        if (count($picked) > 1) {
            $cards = array_map(fn (Producto $p) => $this->productCard($p), $picked);

            return $empty + [
                'reply' => 'Hay más de una opción parecida. Elige con el botón Agregar o dime el nombre exacto.',
                'products' => $cards,
                'action' => ['type' => 'clarify'],
            ];
        }

        $cards = $offered->map(fn (Producto $p) => $this->productCard($p))->all();

        return $empty + [
            'reply' => 'Ese nombre no está en las opciones que te acabo de mostrar. Toca Agregar en una tarjeta o dime el nombre tal como aparece en la lista.',
            'products' => $cards,
            'action' => null,
        ];
    }

    private function matchOffered(string $message, $offered): array
    {
        $m = mb_strtolower($message);

        $ordinals = [
            1 => '/\b(primera|primer|1)\b/u',
            2 => '/\b(segunda|segundo|2)\b/u',
            3 => '/\b(tercera|tercero|3)\b/u',
        ];
        if (preg_match('/\b(la|el)\s+(primera|primer|segunda|segundo|tercera|tercero|\d)\b/u', $m)) {
            $list = $offered->values();
            foreach ($ordinals as $i => $pat) {
                if (preg_match($pat, $m) && isset($list[$i - 1])) {
                    return [$list[$i - 1]];
                }
            }
        }

        if (preg_match('/\b(esa|ese|esta|este|la\s+misma)\b/u', $m) && $offered->count() === 1) {
            return [$offered->first()];
        }

        $tokens = $this->extractSearchTokens($message);
        if ($tokens === []) {
            if ($offered->count() === 1 && preg_match('/quiero|agrega|a[nñ]ade|dame|llevo/u', $m)) {
                return [$offered->first()];
            }

            return [];
        }

        $scored = [];
        foreach ($offered as $p) {
            $name = mb_strtolower((string) $p->nombre);
            $tags = mb_strtolower((string) ($p->etiquetas ?? ''));
            $desc = mb_strtolower((string) ($p->descripcion ?? ''));
            $score = 0;
            $nameHits = 0;
            foreach ($tokens as $t) {
                if (str_contains($name, $t)) {
                    $score += 10;
                    $nameHits++;
                    if (str_contains($name, implode(' ', $tokens))) {
                        $score += 8;
                    }
                } elseif ($tags !== '' && str_contains($tags, $t)) {
                    $score += 3;
                } elseif (str_contains($desc, $t)) {
                    $score += 1;
                }
            }
            if ($nameHits === count($tokens)) {
                $score += 15;
            }
            if ($score > 0) {
                $scored[] = ['p' => $p, 's' => $score];
            }
        }

        if ($scored === []) {
            return [];
        }

        usort($scored, fn ($a, $b) => $b['s'] <=> $a['s']);
        $best = $scored[0]['s'];
        $second = $scored[1]['s'] ?? 0;

        if ($best >= $second + 8) {
            return [$scored[0]['p']];
        }

        $tied = array_values(array_filter($scored, fn ($x) => $x['s'] === $best));

        return array_map(fn ($x) => $x['p'], $tied);
    }

    private function findProducts(string $message, string $intent): array
    {
        if (in_array($intent, ['help', 'howto', 'payment', 'account', 'order', 'offtopic', 'catalog_count'], true)) {
            return [];
        }

        $base = Producto::query()->where(function ($b) {
            $b->whereNull('estado')->orWhere('estado', 'activo');
        });

        if ($intent === 'catalog') {
            return (clone $base)->orderByDesc('stock')->limit(6)->get()->all();
        }

        $tokens = $this->extractSearchTokens($message);
        if ($tokens === []) {
            return [];
        }

        $required = $this->requiredNeedles($message);

        $scored = [];
        $candidates = (clone $base)
            ->where(function ($b) use ($tokens) {
                foreach ($tokens as $t) {
                    $b->orWhere('nombre', 'like', '%'.$t.'%')
                        ->orWhere('descripcion', 'like', '%'.$t.'%')
                        ->orWhere('slug', 'like', '%'.$t.'%')
                        ->orWhere('etiquetas', 'like', '%'.$t.'%');
                }
            })
            ->limit(50)
            ->get();

        foreach ($candidates as $p) {
            $name = mb_strtolower((string) $p->nombre);
            $tags = mb_strtolower((string) ($p->etiquetas ?? ''));
            $desc = mb_strtolower((string) ($p->descripcion ?? ''));
            $hay = $name.' '.$tags.' '.$desc;
            if ($required !== [] && ! $this->hayContainsAny($hay, $required)) {
                continue;
            }
            $wantsPlush = (bool) preg_match('/peluch|\bosito|\boso/u', mb_strtolower($message));
            if ($wantsPlush && preg_match('/bolso|mochila|piton|pitón/u', $name)) {
                continue;
            }
            $score = 0;
            foreach ($tokens as $t) {
                if (str_contains($name, $t)) {
                    $score += 10;
                    if (str_starts_with($name, $t)) {
                        $score += 5;
                    }
                }
                if ($tags !== '' && str_contains($tags, $t)) {
                    $score += 12;
                } elseif (str_contains($desc, $t)) {
                    $score += 6;
                }
            }
            $wantsPlush = (bool) preg_match('/peluch|osito|oso/u', mb_strtolower($message));
            if ($wantsPlush && (str_contains($desc, 'osito') || str_contains($desc, 'peluche de oso') || str_contains($name, 'peluche'))) {
                $score += 8;
            }
            if ($wantsPlush && preg_match('/bolso|mochila|piton|pitón|cuero/u', $name.' '.$desc)) {
                $score -= 15;
            }
            if ($score > 0) {
                $scored[] = ['p' => $p, 's' => $score + min(3, (int) $p->stock / 10)];
            }
        }

        usort($scored, fn ($a, $b) => $b['s'] <=> $a['s']);

        if (count($scored) >= 2 && $scored[0]['s'] >= $scored[1]['s'] + 8) {
            return [$scored[0]['p']];
        }

        return array_map(fn ($x) => $x['p'], array_slice($scored, 0, 6));
    }

    private function extractSearchTokens(string $message): array
    {
        $q = mb_strtolower($message);
        $q = str_replace(["\n", "\r", '?', '¿', '!', '¡', ',', '.', ';', ':'], ' ', $q);

        $stop = [
            'tienen', 'tiene', 'busco', 'buscar', 'quiero', 'precio', 'cuesta', 'cuanto', 'cuánto',
            'stock', 'hay', 'por', 'favor', 'hola', 'buenos', 'buenas', 'como', 'cómo', 'compro',
            'comprar', 'pago', 'pagar', 'con', 'yape', 'tarjeta', 'efectivo', 'puedo', 'podria',
            'podría', 'que', 'qué', 'una', 'uno', 'unos', 'unas', 'los', 'las', 'del', 'para',
            'este', 'esta', 'eso', 'esa', 'mas', 'más', 'muy', 'algo', 'tambien', 'también',
            'necesito', 'info', 'informacion', 'información', 'sobre', 'mi', 'tu', 'su', 'the',
            'and', 'app', 'web', 'movil', 'móvil', 'solo', 'total', 'catalogo', 'catálogo',
            'variedad', 'diferentes', 'disponible', 'disponibles', 'unidad', 'unidades',
            'hambre', 'comida', 'cosas', 'preguntarte', 'puedes', 'puede', 'hacer',
            'tienes', 'tenéis', 'tendre', 'tendré', 'algun', 'algún', 'algunos', 'algunas',
            'vendes', 'venden', 'sale', 'salen',
        ];

        foreach ($stop as $w) {
            $q = preg_replace('/\b'.preg_quote($w, '/').'\b/u', ' ', $q) ?? $q;
        }

        $q = trim(preg_replace('/\s+/', ' ', $q) ?? '');
        $parts = $q === '' ? [] : explode(' ', $q);

        $tokens = [];
        foreach ($parts as $t) {
            $t = trim($t);
            if (mb_strlen($t) < 3) {
                continue;
            }
            if (preg_match('/^\d+$/', $t)) {
                continue;
            }
            $tokens[] = $t;
        }

        $extra = [];
        foreach ($tokens as $t) {
            if (str_starts_with($t, 'cerdit')) {
                $extra[] = 'cerdita';
                $extra[] = 'tiburon';
                $extra[] = 'tiburón';
            }
            if (preg_match('/^osit/u', $t) || $t === 'oso' || $t === 'osos' || $t === 'teddy') {
                $extra[] = 'oso';
                $extra[] = 'osito';
                $extra[] = 'ositos';
            }
            if (str_contains($t, 'peluch')) {
                $extra[] = 'peluche';
                $extra[] = 'peluches';
            }
            if (str_contains($t, 'muñec') || str_contains($t, 'munec')) {
                $extra[] = 'muñeca';
                $extra[] = 'muñeco';
            }
            if (str_contains($t, 'flor')) {
                $extra[] = 'flores';
            }
            if (str_contains($t, 'caja') || str_contains($t, 'cajit')) {
                $extra[] = 'cajita';
                $extra[] = 'caja';
            }
        }

        return array_values(array_unique(array_merge($tokens, $extra)));
    }

    /** Si piden un animal/tipo concreto, el producto DEBE mencionarlo. */
    private function requiredNeedles(string $message): array
    {
        $m = mb_strtolower($message);
        if (preg_match('/\b(oso|osos|osito|ositos|teddy)\b/u', $m)) {
            return ['oso', 'osito', 'ositos', 'osos', 'teddy'];
        }

        return [];
    }

    private function hayContainsAny(string $hay, array $needles): bool
    {
        foreach ($needles as $n) {
            if ($n !== '' && str_contains($hay, $n)) {
                return true;
            }
        }

        return false;
    }

    private function findPedido(string $message, ?Cliente $cliente): ?array
    {
        if (! preg_match('/\b(?:pedido\s*#?\s*|n[uú]mero\s*|orden\s*#?\s*)(\d{1,8})\b/iu', $message, $m)
            && ! preg_match('/\bpedido\s+(\d{1,8})\b/iu', $message, $m)) {
            if ($cliente && preg_match('/\b(mi\s+pedido|mis\s+compras|estado\s+de\s+mi|seguimiento)\b/iu', $message)) {
                $p = Pedido::query()
                    ->where('id_cliente', $cliente->id_cliente)
                    ->orderByDesc('id_pedido')
                    ->first();

                return $p ? $this->pedidoCard($p) : null;
            }

            return null;
        }

        $id = (int) ($m[1] ?? 0);
        if ($id <= 0) {
            return null;
        }

        $p = Pedido::query()->where('id_pedido', $id)->first();
        if (! $p) {
            return null;
        }

        if (! $cliente || (int) $p->id_cliente !== (int) $cliente->id_cliente) {
            return [
                'id_pedido' => $id,
                'acceso' => 'restringido',
                'mensaje' => 'Inicia sesión con la cuenta que hizo el pedido para ver el detalle.',
            ];
        }

        return $this->pedidoCard($p);
    }

    private function buildContext(
        array $products,
        ?array $pedido,
        ?Cliente $cliente,
        int $catalogCount,
        string $intent,
    ): string {
        $lines = [];
        $lines[] = 'intent_detectado: '.$intent;
        $lines[] = 'total_productos_activos: '.$catalogCount;
        $lines[] = 'Cliente: '.($cliente
            ? trim($cliente->nombre.' '.($cliente->apellido ?? '')).' (id '.$cliente->id_cliente.')'
            : 'invitado (no autenticado)');

        $lines[] = 'Productos encontrados:';
        if ($products === []) {
            $lines[] = '- (ninguno para esta consulta)';
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
        $lines[] = $pedido === null
            ? '- (sin pedido en contexto)'
            : '- '.json_encode($pedido, JSON_UNESCAPED_UNICODE);

        $lines[] = 'Ayuda fija: registro con correo o Continuar con Google (Gmail real); compra en la misma app; pagos Culqi prueba / Yape / efectivo; entrega recojo o express. No inventes enlaces de tiendas de aplicaciones.';

        return implode("\n", $lines);
    }

    private function rulesReply(
        string $message,
        array $products,
        ?array $pedido,
        ?Cliente $cliente,
        int $catalogCount,
        string $intent,
    ): string {
        if ($intent === 'help' || preg_match('/hola|buenos|buenas|hey/u', mb_strtolower($message))) {
            return '¡Hola! Soy el asistente de Estilo Dorado. Puedo ayudarte con productos, precios, stock, cómo comprar, formas de pago o el estado de tu pedido (si inicias sesión). ¿Qué necesitas?';
        }

        if ($intent === 'account') {
            return 'Para crear tu usuario: Iniciar sesión → Regístrate (nombre, correo y contraseña) o «Continuar con Google» con tu Gmail.';
        }

        if ($intent === 'howto') {
            return 'Para comprar: 1) Elige un producto en el catálogo 2) Agrégalo al carrito 3) Elige entrega (recojo en tienda o envío express) 4) Paga con tarjeta (Culqi prueba), Yape o efectivo. Si ya tienes un pedido pendiente, ábrelo en Mis compras.';
        }

        if ($intent === 'payment') {
            return 'Formas de pago en Estilo Dorado: tarjeta (Culqi en modo prueba), Yape y efectivo. El pago se hace en el flujo de compra o desde Mis compras si el pedido quedó pendiente.';
        }

        if ($intent === 'offtopic') {
            return 'No vendemos comida: Estilo Dorado es una tienda de regalos y detalles personalizados. ¿Te muestro algo del catálogo (flores, cajitas, detalles, etc.)?';
        }

        if (in_array($intent, ['product', 'mixed'], true) && $products === []) {
            if (preg_match('/\b(oso|osos|osito|ositos|teddy)\b/u', mb_strtolower($message))) {
                return 'No tenemos peluches de oso / ositos en el catálogo. En Inicio puedes ver otros peluches o detalles (por nombre, no como osos). ¿Buscas otra cosa, por ejemplo cerdita o cajita?';
            }

            return 'No encontré un producto con ese nombre en el catálogo. Prueba con otra palabra (ej. «cerdita», «cajita», «flores») o revisa Inicio.';
        }

        if ($intent === 'catalog_count') {
            return "En el catálogo activo hay {$catalogCount} productos. Puedes verlos todos en Inicio o preguntarme por un nombre (ej. cerdita, cajita, flores).";
        }

        if ($intent === 'catalog') {
            if ($products === []) {
                return "Tenemos {$catalogCount} productos activos en el catálogo. Ábrelo en Inicio de la app para verlos todos.";
            }
            $list = collect($products)->take(5)->map(function (Producto $p) {
                return sprintf(
                    '• %s — S/ %s (stock %d)',
                    $p->nombre,
                    number_format((float) $p->precio_venta, 2, '.', ''),
                    (int) $p->stock
                );
            })->implode("\n");

            return "Algunos productos del catálogo ({$catalogCount} en total):\n{$list}\nPuedes tocar un producto o buscar por nombre.";
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

        if (preg_match('/pedido|compra|estado/u', mb_strtolower($message)) && ! $cliente) {
            return 'Para consultar el estado de un pedido, inicia sesión y dime el número (ej. «pedido 12») o escribe «mi pedido».';
        }

        if ($products !== []) {
            if (count($products) === 1) {
                $p = $products[0];
                $stock = (int) $p->stock;

                return sprintf(
                    '%s cuesta S/ %s. %s. Ábrelo en el catálogo (id %d) y agrégalo al carrito. Para pagar: carrito → entrega → Yape, tarjeta de prueba o efectivo.',
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

            return "Encontré estos productos:\n{$list}\n¿Quieres el detalle de alguno?";
        }

        return 'No encontré un producto exacto con esa búsqueda. Prueba con otra palabra (ej. «cerdita», «cajita», «flores») o revisa el catálogo en Inicio. También puedo explicar cómo comprar o pagar.';
    }

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

    private function suggestions(bool $loggedIn): array
    {
        $base = [
            '¿Qué productos tienen?',
            'Cerdita tiburón',
            'Quiero la cerdita',
            '¿Cómo compro?',
            'Formas de pago',
        ];
        if ($loggedIn) {
            $base[] = 'Estado de mi pedido';
        } else {
            $base[] = '¿Cómo me registro?';
        }

        return $base;
    }
}
