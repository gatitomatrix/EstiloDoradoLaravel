<?php

namespace App\Services\Asistente;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;

class AsistenteService
{
    // Dori. Primero reglas (queja, pedidos, dueño). Si no calza, Gemini/Ollama.
    // Invitado ve catálogo. Logueado puede ver sus 3 últimos pedidos.
    // "Hablar con el dueño" / WhatsApp: solo si hay sesión, para saber quién es.
    public function __construct(
        private OllamaClient $ollama,
        private GeminiClient $gemini,
        private WhatsappEscalation $whatsapp,
        private ComplaintFlow $complaints,
    ) {}

    public function handle(string $message, ?Cliente $cliente = null, array $offeredIds = [], ?string $awaiting = null, array $complaint = []): array
    {
        $message = trim($message);
        $awaiting = is_string($awaiting) ? trim($awaiting) : '';
        $complaint = is_array($complaint) ? $complaint : [];

        if ($awaiting === 'complaint_phone') {
            return $this->complaints->takePhone($message, $cliente, $complaint);
        }
        if ($awaiting === 'complaint_phone_confirm') {
            if (! $cliente) {
                return $this->complaints->takePhone($message, null, $complaint);
            }

            return $this->complaints->confirmProfilePhone($message, $cliente, $complaint);
        }
        if ($awaiting === 'complaint_order') {
            if (! $cliente) {
                return $this->complaints->afterTipo($complaint['tipo'] ?? 'otro', $message, null, $complaint);
            }

            return $this->complaints->pickOrder($message, $cliente, $complaint);
        }
        if ($awaiting === 'complaint_login') {
            if ($cliente) {
                return $this->complaints->afterLogin($complaint['tipo'] ?? 'otro', $message, $cliente, $complaint);
            }
            if (preg_match('/whatsapp|\bno\b/u', mb_strtolower($message))) {
                return $this->complaints->takePhone('no', null, $complaint);
            }

            return $this->complaints->afterTipo($complaint['tipo'] ?? 'otro', $message, null, $complaint);
        }
        if ($awaiting === 'complaint_detail') {
            return $this->resolveComplaintDetail($message, $cliente, $complaint);
        }

        $esc = $this->whatsapp->match($message);
        if ($esc === 'humano') {
            return $this->handleHumano($message, $cliente);
        }

        // "mi pedido no llega" no es "ver mis pedidos": entra al flujo de queja (elige el #).
        $saludoCorto = (bool) preg_match('/^(hola|buenos\s*d[ií]as|buenas(?:\s*tardes|\s*noches)?)[\s!¡.?]*$/iu', $message);
        $queja = $saludoCorto ? null : $this->whatsapp->classifyQueja($message);
        if (in_array($queja, ['no_llego', 'producto_danado', 'cobro', 'devolucion', 'demora'], true)) {
            return $this->complaints->afterTipo($queja, $message, $cliente, $complaint);
        }

        $intent = $this->detectIntent($message, $offeredIds !== []);

        if ($intent === 'add_to_cart') {
            return $this->handleAddToCart($message, $offeredIds);
        }

        if ($intent === 'order') {
            return $this->handleOrderQuery($message, $cliente);
        }

        if ($intent === 'courtesy') {
            return [
                'reply' => '¡Con gusto! Si se te ocurre otro regalo o producto, aquí estoy.',
                'driver' => 'rules',
                'products' => [],
                'pedido' => null,
                'suggestions' => [],
                'action' => null,
            ];
        }

        if ($this->whatsapp->isVagueComplaint($message)) {
            return [
                'reply' => $this->whatsapp->askComplaintDetail(),
                'driver' => 'rules',
                'products' => [],
                'pedido' => null,
                'suggestions' => [],
                'action' => null,
                'awaiting' => 'complaint_detail',
                'log_tipo' => 'queja_espera',
                'urgencia' => false,
            ];
        }

        if ($esc) {
            if (in_array($esc, ['reclamo', 'devolucion', 'cobro'], true)) {
                $tipo = $this->whatsapp->classifyQueja($message) ?: $esc;

                return $this->complaints->afterTipo($tipo, $message, $cliente, $complaint);
            }
            $pedido = $this->findPedido($message, $cliente);
            $pid = is_array($pedido) ? ($pedido['id_pedido'] ?? null) : null;

            return [
                'reply' => $this->whatsapp->reply($esc),
                'driver' => 'rules',
                'products' => [],
                'pedido' => $pedido,
                'suggestions' => [],
                'action' => $this->whatsapp->action($message, $pid ? (int) $pid : null),
                'awaiting' => null,
                'log_tipo' => 'whatsapp',
                'queja_tipo' => $esc,
                'urgencia' => true,
            ];
        }

        $products = $this->findProducts($message, $intent, $offeredIds);
        $pedido = $this->findPedido($message, $cliente);
        $catalogCount = (int) Producto::query()
            ->where(function ($b) {
                $b->whereNull('estado')->orWhere('estado', 'activo');
            })
            ->count();

        $context = $this->buildContext($products, $pedido, $cliente, $catalogCount, $intent, $message);

        $driver = strtolower((string) config('llm.driver', 'gemini'));
        $reply = null;
        $used = 'rules';
        // Gemini en producto, catálogo y cómo comprar. No gastar tokens en hola / fuera de tema.
        $skipLlm = in_array($intent, ['help', 'offtopic', 'order'], true);

        if (! $skipLlm && in_array($driver, ['ollama', 'gemini'], true)) {
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
            'awaiting' => null,
            'intent' => $intent,
            'log_tipo' => $showProducts ? 'catalogo' : ($pedido && $cliente ? 'pedido' : null),
        ];
    }

    private function resolveComplaintDetail(string $message, ?Cliente $cliente, array $complaint = []): array
    {
        if ($this->whatsapp->isVagueComplaint($message) && mb_strlen(trim($message)) < 32) {
            return [
                'reply' => $this->whatsapp->askComplaintDetail(),
                'driver' => 'rules',
                'products' => [],
                'pedido' => null,
                'suggestions' => [],
                'action' => null,
                'awaiting' => 'complaint_detail',
                'log_tipo' => 'queja_espera',
                'urgencia' => false,
                'complaint' => $complaint,
            ];
        }

        $tipo = $this->whatsapp->classifyQueja($message) ?? 'otro';

        return $this->complaints->afterTipo($tipo, $message, $cliente, $complaint);
    }

    private function systemPrompt(): string
    {
        return <<<'TXT'
Eres Dori, asistente de ventas de "Estilo Dorado" (regalos, detalles personalizados, flores, cajitas, peluches, billeteras y accesorios). Tienda en Cerro de Pasco, Perú. Recojo: Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca. Hablas en español peruano, cercano y profesional, como un chat de tienda actual (no robot, no discurso largo).

ESTILO:
- 2 a 4 frases. Puedes hacer UNA pregunta corta al final para seguir la conversación (¿para quién es?, ¿presupuesto?, ¿recojo o envío?).
- Tutea. NUNCA te presentes de nuevo ni empieces con «Hola, soy…»: el cliente ya vio tu saludo en la ventana. Responde DIRECTO a lo que pidió.
- Si mezcla saludo y pedido («hola, es el cumpleaños de mi hermano»), ignora el hola y recomienda productos.
- Si el tema es de la tienda (regalo, ocasión, envío, pago, stock, pedido, personalizado, horario, ubicación), responde con gusto. Si es política, presidente, farándula, comida, clima, tareas u otro rubro: NO contestes el dato. Redirige amable a regalos/catálogo en 2 frases.
- Si no sabes un dato de la tienda que no esté en este contexto: dilo en una frase y invita a contactar por WhatsApp o el correo de atención. NO inventes.

DATOS FIJOS DE LA TIENDA (puedes usarlos siempre):
- Catálogo público: no hace falta registrarse para ver productos.
- Compra: producto → carrito → entrega (recojo en tienda GRATIS o envío express) → pago.
- Envío estimado (no cotización Shalom en vivo): Huancayo S/8, Junín S/12, Lima S/18, resto del Perú S/25.
- Pagos: tarjeta (Culqi modo prueba en la demo), Yape y efectivo.
- Registro: correo + contraseña o «Continuar con Google».
- Pedidos: en «Mis compras»; cancelar/pagar si está pendiente. Comprobante cuando ya pagó.
- Personalizados: carteles, flores y detalles; el cliente elige el producto del catálogo.
- NO inventes App Store, Play Store ni www.estilodorado.com.
- NO inventes horarios exactos si no están en el contexto; di que el pedido en app/web es 24/7 y el recojo se coordina con la tienda.
- Devoluciones: coordinar con la tienda; no prometas plazos que no están en el contexto.

REGLAS DE PRODUCTOS (estricto):
1) Precios, stock y nombres SOLO del bloque "Productos encontrados".
2) total_productos_activos = tamaño real del catálogo.
2b) Si preguntan qué venden o cuántos productos: di que hay total_productos_activos y «Te recomendamos estos» (máximo 5 de Productos encontrados). NO listes precios ni stock en el texto: las tarjetas ya los muestran.
3) Si esa lista NO está vacía, esos productos EXISTEN: dilo, cotiza y ofrece. PROHIBIDO "no tenemos" en ese caso.
4) Lista vacía: no hay ese artículo. NO inventes Cerdita ni otro nombre. Invita a otra palabra o a Inicio.
5) NUNCA digas que ya agregaste al carrito; invita al botón Agregar o a «quiero la [nombre]».
6) Regalo para mujer / cumpleaños: flores, cajitas, peluche, detalles. Para hombre / papá / esposo: billeteras y accesorios de caballero, no solo flores.
7) EDAD Y GÉNERO del DESTINATARIO (no del comprador). Si el contexto trae destinatario_regalo, prioriza ESA lista: un niño ~10 no es lo mismo que alguien de 50-60. No ofrezcas billetera de caballero a un niño ni peluche infantil como primera opción a un adulto mayor.
8) Si destinatario_regalo dice edad o género "no indicada", pregunta UNA vez: «¿Es para hombre o mujer, y más o menos qué edad: 10, 20, 30, 40, 50 o 60?»
9) No des datos de otros clientes. Pedidos solo con el contexto; si no hay, pide iniciar sesión.
10) PRESUPUESTO: si el cliente dice «menor a 20», «hasta 15 soles», «barato», etc., recomienda SOLO productos de la lista (ya vienen filtrados). Si la lista no está vacía, NO digas que no hay. Si está vacía, di que no hay en ese rango y pregunta si sube un poco el tope.

Si no estás seguro, pregunta. No rellenes con productos inventados.
TXT;
    }

    private function detectIntent(string $message, bool $hasOffered = false): string
    {
        $m = mb_strtolower($message);

        $addCue = (bool) preg_match('/agrega|a[nñ]ade|al\s+carrito|me\s+llevo|ponme|ponlo|quiero\s+(esa|ese|esta|este|la|el|una|uno|\d)|la\s+primera|la\s+segunda|la\s+\d/u', $m);
        if ($hasOffered && $addCue && ! preg_match('/pedido|compras/u', $m)) {
            return 'add_to_cart';
        }
        if ($hasOffered && preg_match('/^(quiero|dame|me\s+das)\b/u', $m)
            && ! preg_match('/pedido|compras|hablar|due[nñ]|duelo|tienda|whats?app|asesor|gerente|humano|jefe|propietari/u', $m)) {
            return 'add_to_cart';
        }

        if (preg_match('/registr|cuenta|usuario|crear\s*cuenta|sign\s*up|login|iniciar\s*sesi/u', $m)) {
            return 'account';
        }
        // Lista: "mis pedidos", "quiero ver mis pedidos", typo "pedodos". No "mi pedido no llega".
        $pideLista = (bool) preg_match(
            '/mis\s+ped|mis\s+compras|cu[aá]l(?:es)?\s+son\s+mis|quiero\s+ver\s+mis|ver\s+mis\s+|[uú]ltimos?\s+\d*\s*ped|seguimiento|estado\s+de\s+mi|pedido\s*#?\s*\d{1,8}/u',
            $m
        );
        $quejaPedido = (bool) preg_match(
            '/no\s+(me\s+)?lleg|no\s+ha\s+lleg|a[uú]n\s+no|todav[ií]a\s+no|extravi|demor|no\s+me\s+(han\s+)?entreg|perd[ií]d/u',
            $m
        );
        if ($pideLista && ! $quejaPedido) {
            return 'order';
        }
        if (preg_match('/c[oó]mo\s+(hago|puedo|hago\s+para)?.{0,28}compr|para\s+comprar|c[oó]mo\s+compr|pasos.{0,12}compr|carrito|delivery|recojo|env[ií]o/u', $m)
            && ! preg_match('/agrega|a[nñ]ade|busco|billetera|cerdit|cajit|flores|hot\s*wheels/u', $m)) {
            return 'howto';
        }
        if (preg_match('/yape|tarjeta|culqi|efectivo|forma[s]?\s+de\s+pago|m[eé]todo[s]?\s+de\s+pago|\bpagar\b/u', $m)
            && ! preg_match('/busco|producto|cuesta|precio|stock|cerdit|cajit|flores|billetera/u', $m)) {
            return 'payment';
        }

        if (preg_match('/cumplea|cumple\b|recomend|regalo|regalar|aniversario|para\s+(una?\s+)?(mujer|chica|dama|se[nñ]orita)|novia|hermana|pap[aá]|padre|esposo/u', $m)) {
            return 'product';
        }
        if (preg_match('/cu[aá]ntos\s+product|cu[aá]ntas\s+cosas|total\s+del\s+cat[aá]logo|variedad/u', $m)) {
            return 'catalog_count';
        }
        if (preg_match('/qu[eé]\s+(product|cosas|venden|vendes|tienen)|cat[aá]logo|qu[eé]\s+hay\b|qu[eé]\s+venden/u', $m)) {
            return 'catalog';
        }
        $soloSaludo = (bool) preg_match('/^(hola|buenos\s*d[ií]as|buenas(?:\s*tardes|\s*noches)?|hey|ayuda|qu[eé]\s+puedes\s+hacer)[\s!¡.?]*$/u', $m);
        if ($soloSaludo) {
            return 'help';
        }
        $cortesia = (bool) preg_match(
            '/^(ok+|okay|vale|listo|dale|va|perfecto|excelente|genial|buenísimo|buenisimo|entendido|de\s+acuerdo|gracias|muchas\s+gracias|mil\s+gracias|thank(s|\s*you)?|ty|chau|adi[oó]s|bye|nos\s+vemos|muy\s+amable|todo\s+bien)([\s,!.¡¿]*(gracias|genial|ok+|vale|listo))*[\s!¡.]*$/u',
            $m
        );
        if ($cortesia) {
            return 'courtesy';
        }
        $elogio = (bool) preg_match('/gracias|bonit|precios[oaos]|lind[oaos]|hermos[oa]|encant|divin[oa]|me gusta|me encanta|est[aá]n\s+(bonit|lind|precios|hermos)|qu[eé]\s+(lind|bonit|precios)|bac[aá]n|ch[eé]vere|genial|perfecto/u', $m);
        $pideAlgo = (bool) preg_match('/busco|tienen|hay\s|\bprecio\b|cuesta|\bstock\b|reclamo|queja|agrega|cerdit|cajit|flores|cu[aá]nto|quiero\s+(la|el|una|ese|esa)|producto lleg/u', $m);
        if ($elogio && ! $pideAlgo && mb_strlen($m) < 90) {
            return 'courtesy';
        }
        if (preg_match('/hambre|comida|pizza|hamburg|almorz|cenar|restaurante/u', $m)
            && ! preg_match('/busco|cerdit|cajit|regalo|flores/u', $m)) {
            return 'offtopic';
        }
        if ($this->isOffTopic($m)) {
            return 'offtopic';
        }
        if (preg_match('/busco|precio|cuesta|stock|tienen|hay\s|quiero|cerdit|cajit|flores|billetera|hot\s*wheels|personaliz|recomend|regalo|cumple|presupuesto|barato|econ[oó]mic|soles/u', $m)) {
            return 'product';
        }
        if (preg_match('/busco|cerdit|product/u', $m) && preg_match('/compr|pago|yape/u', $m)) {
            return 'mixed';
        }

        return 'product';
    }

    private function isOffTopic(string $m): bool
    {
        if (preg_match('/regalo|producto|pedido|cajit|flores|billetera|compr|cat[aá]logo|yape|recojo|env[ií]o/u', $m)) {
            return false;
        }

        return (bool) preg_match(
            '/presidente|ministr[oa]|congreso|eleccion|trump|biden|milei|f[uú]tbol|mundial\s+de|clima|temperatura|receta|tarea\b|wikipedia|chiste|hor[oó]scopo|guerra|capital\s+de|qui[eé]n\s+es\s+el|qu[eé]\s+hora\s+es|traduce|far[aá]ndula|celebridad|netflix|chatgpt/u',
            $m
        );
    }

    private function contactoCorto(): string
    {
        $num = $this->whatsapp->displayNumber();
        $email = trim((string) config('llm.contacto.email', ''));
        if (str_contains(mb_strtolower($email), 'no-reply') || str_contains(mb_strtolower($email), 'noreply')) {
            $email = '';
        }
        if ($num && $email !== '') {
            return 'Si no te di con lo que buscas, escríbenos al WhatsApp '.$num.' o al correo '.$email.'.';
        }
        if ($num) {
            return 'Si no te di con lo que buscas, escríbenos al WhatsApp '.$num.'.';
        }
        if ($email !== '') {
            return 'Si no te di con lo que buscas, escríbenos a '.$email.'.';
        }

        return 'Si no te di con lo que buscas, pide «hablar con la tienda» (inicia sesión) y te pasamos el WhatsApp.';
    }

    private function handleHumano(string $message, ?Cliente $cliente): array
    {
        if (! $cliente) {
            return [
                'reply' => 'Para comunicarte con la tienda inicia sesión. Así sabremos quién eres y no se mezcla con otro cliente. Luego te doy el WhatsApp.',
                'driver' => 'rules',
                'products' => [],
                'pedido' => null,
                'suggestions' => ['¿Cómo me registro?', 'Estado de mi pedido'],
                'action' => ['type' => 'login', 'label' => 'Iniciar sesión'],
                'awaiting' => null,
                'log_tipo' => 'whatsapp',
                'queja_tipo' => 'humano',
            ];
        }

        $nombre = trim(($cliente->nombre ?? '').' '.($cliente->apellido ?? ''));
        $correo = (string) ($cliente->email ?? '');
        $txt = 'Hola, soy '.($nombre !== '' ? $nombre : 'cliente');
        if ($correo !== '') {
            $txt .= ' ('.$correo.')';
        }
        $txt .= '. Quiero comunicarme con la tienda. '.$message;
        $wa = $this->whatsapp->action($txt, null);
        $num = $this->whatsapp->displayNumber();
        $reply = $num
            ? 'Claro. El WhatsApp de Estilo Dorado es '.$num.'. Toca «Escribir por WhatsApp»: en el celular abre la app y en la computadora WhatsApp Web. El mensaje ya lleva tu nombre para que sepan quién eres.'
            : 'Claro. Una persona de la tienda te atiende por WhatsApp; el botón abre el chat.';

        return [
            'reply' => $reply,
            'driver' => 'rules',
            'products' => [],
            'pedido' => null,
            'suggestions' => [],
            'action' => $wa,
            'awaiting' => null,
            'log_tipo' => 'whatsapp',
            'queja_tipo' => 'humano',
        ];
    }

    private function handleOrderQuery(string $message, ?Cliente $cliente): array
    {
        $login = [
            'reply' => 'Para ver tus pedidos inicia sesión con la cuenta con la que compraste. Luego te muestro los últimos 3 y el enlace a Mis compras.',
            'driver' => 'rules',
            'products' => [],
            'pedido' => null,
            'pedidos' => [],
            'suggestions' => ['¿Cómo me registro?', '¿Cómo compro?'],
            'action' => ['type' => 'login', 'label' => 'Iniciar sesión'],
            'awaiting' => null,
            'log_tipo' => 'pedido',
        ];
        if (! $cliente) {
            return $login;
        }

        $one = null;
        if (preg_match('/\b(?:pedido\s*#?\s*|n[uú]mero\s*|orden\s*#?\s*)(\d{1,8})\b/iu', $message, $m)
            || preg_match('/\bpedido\s+(\d{1,8})\b/iu', $message, $m)) {
            $one = $this->findPedido($message, $cliente);
        }

        $chips = $this->recentOrderChips($cliente, 3);
        if ($one && ($one['acceso'] ?? null) === 'restringido') {
            return $login;
        }

        $link = [
            'type' => 'navigate',
            'url' => '/mis-compras',
            'label' => 'Ver todos en Mis compras',
        ];

        if ($one && empty($one['acceso'])) {
            $txt = sprintf(
                'Tu pedido #%s está en estado «%s». Total: S/ %s. Pago: %s. Entrega: %s. Abajo tienes tus últimos pedidos; para el historial completo usa Mis compras.',
                $one['id_pedido'],
                $one['estado'] ?? '—',
                $one['total'] ?? '—',
                $one['forma_pago'] ?? '—',
                $one['direccion_entrega'] ?? '—'
            );

            return [
                'reply' => $txt,
                'driver' => 'rules',
                'products' => [],
                'pedido' => $one,
                'pedidos' => $chips,
                'suggestions' => ['Estado de mi pedido', '¿Cómo compro?'],
                'action' => $link,
                'awaiting' => null,
                'log_tipo' => 'pedido',
            ];
        }

        if ($chips === []) {
            return [
                'reply' => 'Aún no veo pedidos en esta cuenta. Cuando compres, aparecerán aquí y en Mis compras.',
                'driver' => 'rules',
                'products' => [],
                'pedido' => null,
                'pedidos' => [],
                'suggestions' => ['¿Qué productos tienen?', '¿Cómo compro?'],
                'action' => $link,
                'awaiting' => null,
                'log_tipo' => 'pedido',
            ];
        }

        $n = count($chips);
        $txt = $n === 1
            ? 'Este es tu pedido más reciente. Para el historial completo entra a Mis compras.'
            : "Estos son tus últimos {$n} pedidos. Si quieres ver todos, entra a Mis compras.";

        return [
            'reply' => $txt,
            'driver' => 'rules',
            'products' => [],
            'pedido' => null,
            'pedidos' => $chips,
            'suggestions' => ['Estado de mi pedido', '¿Cómo compro?'],
            'action' => $link,
            'awaiting' => null,
            'log_tipo' => 'pedido',
        ];
    }

    /** @return list<array<string, mixed>> */
    private function recentOrderChips(Cliente $cliente, int $limit = 3): array
    {
        $rows = Pedido::query()
            ->with(['detalles.producto'])
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('id_pedido')
            ->limit($limit)
            ->get();

        $out = [];
        foreach ($rows as $p) {
            $first = $p->detalles->first()?->producto;
            $out[] = [
                'id_pedido' => (int) $p->id_pedido,
                'fecha' => optional($p->fecha_pedido)?->timezone('America/Lima')->format('d/m/Y H:i') ?: '',
                'total' => number_format((float) $p->total, 2, '.', ''),
                'estado' => $p->estado,
                'resumen' => mb_substr((string) ($first?->nombre ?? 'Pedido'), 0, 40),
                'imagen_url' => $first?->imagen_url,
            ];
        }

        return $out;
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
                    number_format((float) $p->precio_final, 2, '.', '')
                ),
                'products' => [$card],
                'action' => [
                    'type' => 'confirm_add',
                    'id' => $p->id_producto,
                    'qty' => $qty,
                    'nombre' => $p->nombre,
                    'precio' => (float) $p->precio_final,
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

    private function findProducts(string $message, string $intent, array $offeredIds = []): array
    {
        if (in_array($intent, ['help', 'howto', 'payment', 'account', 'order', 'offtopic', 'catalog_count'], true)) {
            return [];
        }

        $budget = $this->parseBudget($message);
        $base = Producto::query()->where(function ($b) {
            $b->whereNull('estado')->orWhere('estado', 'activo');
        });

        if ($budget && $offeredIds !== []) {
            $kept = Producto::query()
                ->whereIn('id_producto', $offeredIds)
                ->where(function ($b) {
                    $b->whereNull('estado')->orWhere('estado', 'activo');
                })
                ->get()
                ->filter(fn (Producto $p) => $this->inBudget($p, $budget))
                ->values();
            if ($kept->isNotEmpty()) {
                return $kept->all();
            }
        }

        if ($intent === 'catalog' && ! $budget) {
            return (clone $base)->orderByDesc('stock')->limit(6)->get()->all();
        }

        $tokens = $this->extractSearchTokens($message);
        if ($budget && $tokens === []) {
            return $this->productsInBudget($base, $budget);
        }

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

        $seg = (new AudienceSegment)->parse($message);

        foreach ($candidates as $p) {
            if ($budget && ! $this->inBudget($p, $budget)) {
                continue;
            }
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
            $msg = mb_strtolower($message);
            if (preg_match('/mujer|chica|dama|se[nñ]orita|novia/u', $msg) && str_contains($tags, 'caballero')) {
                continue;
            }
            if (preg_match('/para\s+(un\s+|el\s+|mi\s+)?(hombre|caballero|chico|var[oó]n|pap[aá]|padre|esposo|marido)\b/u', $msg)
                && preg_match('/\b(novia|dama)\b/u', $tags) && ! str_contains($tags, 'caballero')) {
                continue;
            }
            $score = 0;
            if (preg_match('/hombre|caballero|pap[aá]|padre|esposo/u', $msg) && (str_contains($tags, 'caballero') || str_contains($name, 'billetera') || str_contains($tags, 'militar'))) {
                $score += 12;
            }
            if (preg_match('/cumplea|cumple\b|fiesta/u', $msg) && (str_contains($tags, 'cumplea') || str_contains($tags, 'fiesta') || str_contains($tags, 'globo'))) {
                $score += 10;
            }
            foreach ($tokens as $t) {
                if (str_contains($name, $t)) {
                    $score += 10;
                    if (str_starts_with($name, $t)) {
                        $score += 5;
                    }
                    if (preg_match('/^\d+$/', $t) && preg_match('/(^|\s)'.preg_quote($t, '/').'(\s|$)/u', $name)) {
                        $score += 28;
                    }
                }
                if ((int) $p->id_producto === (int) $t && preg_match('/^\d+$/', $t)) {
                    $score += 20;
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
            foreach ($seg->boostTags() as $bt) {
                if ($bt !== '' && (str_contains($tags, $bt) || str_contains($hay, $bt))) {
                    $score += 14;
                }
            }
            if ($seg->edad === 10 && preg_match('/billetera|caballero|perfume|militar/u', $hay) && ! preg_match('/infantil|nino|niño|edad:10/u', $hay)) {
                $score -= 20;
            }
            if ($seg->edad !== null && $seg->edad >= 50 && preg_match('/stich|hot wheels|infantil|cerdita|edad:10/u', $hay) && ! preg_match('/edad:50|edad:60|mayor|adulto/u', $hay)) {
                $score -= 16;
            }
            if ($seg->genero === 'hombre' && $seg->edad !== 10 && preg_match('/\bflores\b/u', $hay) && ! str_contains($tags, 'caballero')) {
                $score -= 8;
            }
            if ($score > 0) {
                $scored[] = ['p' => $p, 's' => $score + min(3, (int) $p->stock / 10)];
            }
        }

        usort($scored, fn ($a, $b) => $b['s'] <=> $a['s']);

        $nums = array_values(array_filter($tokens, fn ($t) => (bool) preg_match('/^\d{1,4}$/', $t)));
        if ($nums !== [] && $scored !== []) {
            $withNum = array_values(array_filter($scored, function ($x) use ($nums) {
                $n = mb_strtolower((string) $x['p']->nombre);
                $id = (int) $x['p']->id_producto;
                foreach ($nums as $num) {
                    if ($id === (int) $num) {
                        return true;
                    }
                    if (preg_match('/(^|\s)'.preg_quote($num, '/').'(\s|$)/u', $n)) {
                        return true;
                    }
                }

                return false;
            }));
            if ($withNum !== []) {
                $scored = $withNum;
            }
        }

        if ($scored === [] && $budget) {
            return $this->productsInBudget($base, $budget);
        }

        if (count($scored) >= 2 && $scored[0]['s'] >= $scored[1]['s'] + 8) {
            return [$scored[0]['p']];
        }

        return array_map(fn ($x) => $x['p'], array_slice($scored, 0, 6));
    }

    /** @return array{min: float, max: ?float}|null */
    private function parseBudget(string $message): ?array
    {
        $m = mb_strtolower($message);
        $m = str_replace(['s/', 's /'], ' ', $m);

        $num = '(\d+(?:[.,]\d+)?)';

        if (preg_match('/entre\s+'.$num.'\s+y\s+'.$num.'/u', $m, $x)) {
            $a = $this->toMoney($x[1]);
            $b = $this->toMoney($x[2]);

            return ['min' => min($a, $b), 'max' => max($a, $b)];
        }
        if (preg_match('/(?:menor(?:es)?\s+(?:a|de)|menos\s+de|m[aá]ximo|m[aá]x\.?|hasta|no\s+m[aá]s\s+de|no\s+mayor(?:es)?\s+(?:a|de)|por\s+debajo\s+de|bajo\s+(?:los?\s+)?|tope\s+de)\s+'.$num.'/u', $m, $x)) {
            return ['min' => 0.0, 'max' => $this->toMoney($x[1])];
        }
        if (preg_match('/'.$num.'\s*(?:soles?)?\s*(?:o\s+menos|como\s+m[aá]ximo|m[aá]ximo)/u', $m, $x)) {
            return ['min' => 0.0, 'max' => $this->toMoney($x[1])];
        }
        if (preg_match('/(?:m[aá]s\s+de|mayor(?:es)?\s+(?:a|de)|desde|m[ií]nimo|por\s+encima\s+de)\s+'.$num.'/u', $m, $x)) {
            return ['min' => $this->toMoney($x[1]), 'max' => null];
        }
        if (preg_match('/presupuesto.{0,24}'.$num.'/u', $m, $x)) {
            return ['min' => 0.0, 'max' => $this->toMoney($x[1])];
        }
        if (preg_match('/con\s+'.$num.'\s*(?:soles?|pe[n]?)\b/u', $m, $x)) {
            return ['min' => 0.0, 'max' => $this->toMoney($x[1])];
        }
        if (preg_match('/barat|econ[oó]mic|accesible|lo\s+m[aá]s\s+barato|poco\s+presupuesto/u', $m)) {
            return ['min' => 0.0, 'max' => 25.0];
        }

        return null;
    }

    private function toMoney(string $raw): float
    {
        $n = str_replace(',', '.', trim($raw));

        return max(0.0, (float) $n);
    }

    private function inBudget(Producto $p, array $budget): bool
    {
        $price = (float) $p->precio_final;
        $min = (float) ($budget['min'] ?? 0);
        $max = $budget['max'] ?? null;
        if ($min > 0 && $price < $min - 0.009) {
            return false;
        }
        if ($max !== null && $price > (float) $max + 0.009) {
            return false;
        }

        return true;
    }

    private function productsInBudget($base, array $budget): array
    {
        $max = $budget['max'];
        $q = (clone $base)->orderBy('precio_venta');
        if ($max !== null) {
            $q->where(function ($b) use ($max) {
                $b->where('precio_venta', '<=', ((float) $max) + 1)
                    ->orWhere('descuento_pct', '>', 0);
            });
        }
        if (($budget['min'] ?? 0) > 0) {
            $q->where('precio_venta', '>=', max(0, (float) $budget['min'] - 5));
        }

        return $q->limit(80)->get()
            ->filter(fn (Producto $p) => $this->inBudget($p, $budget) && (int) $p->stock > 0)
            ->sortBy(fn (Producto $p) => (float) $p->precio_final)
            ->take(6)
            ->values()
            ->all();
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
            'vendes', 'venden', 'sale', 'salen', 'quisiera', 'gustaria', 'gustaría', 'para',
            'años', 'ano', 'edad', 'regalo', 'regalar',
            'presupuesto', 'presu', 'menor', 'menores', 'mayor', 'mayores',
            'soles', 'sole', 'barato', 'barata', 'baratos', 'baratas', 'baratito',
            'economico', 'económico', 'economica', 'económica', 'accesible',
            'maximo', 'máximo', 'minimo', 'mínimo', 'hasta', 'desde', 'entre',
            'abajo', 'debajo', 'encima', 'rango', 'tope', 'plata', 'dinero',
        ];

        foreach ($stop as $w) {
            $q = preg_replace('/\b'.preg_quote($w, '/').'\b/u', ' ', $q) ?? $q;
        }

        $q = trim(preg_replace('/\s+/', ' ', $q) ?? '');
        $parts = $q === '' ? [] : explode(' ', $q);

        $tokens = [];
        foreach ($parts as $t) {
            $t = trim($t);
            if ($t === '') {
                continue;
            }
            // "detalle personalizado 18": el 18 identifica el SKU; no lo tiro.
            if (preg_match('/^\d{1,4}$/', $t)) {
                $tokens[] = $t;
                continue;
            }
            if (mb_strlen($t) < 3) {
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
            if (str_contains($t, 'dulce') || str_contains($t, 'chocolate') || str_contains($t, 'golosina') || str_contains($t, 'caramelo')) {
                $extra[] = 'dulces';
                $extra[] = 'dulce';
            }
            if (str_contains($t, 'cartera') || str_contains($t, 'billetera') || str_contains($t, 'monedero')) {
                $extra[] = 'billetera';
                $extra[] = 'cartera';
            }
            if (str_contains($t, 'novia') || str_contains($t, 'novio') || str_contains($t, 'pareja')
                || str_contains($t, 'enamorad') || str_contains($t, 'recomiend') || str_contains($t, 'recomend') || str_contains($t, 'suger')
                || str_contains($t, 'regalo') || str_contains($t, 'regalar')) {
                $extra[] = 'detalle';
            }
        }

        $blob = mb_strtolower($message);
        $paraHombre = (bool) preg_match('/\b(hermano|t[ií]o|primo|suegro|pap[aá]|padre|esposo|marido|novio|hombre|caballero|chico|var[oó]n)\b/u', $blob)
            && ! preg_match('/hermana|mujer|chica|dama|novia|mam[aá]|t[ií]a/u', $blob);
        $paraMujer = (bool) preg_match('/mujer|chica|dama|se[nñ]orita|novia/u', $blob)
            && ! $paraHombre;

        if (preg_match('/cumplea|cumple\b|aniversario|fiesta|graduac|san\s*valentin|valent[ií]n|d[ií]a\s+de\s+la\s+madre|d[ií]a\s+del\s+padre|amigo\s*secreto|navidad/u', $blob)) {
            $extra[] = 'cumpleaños';
            $extra[] = 'fiesta';
            $extra[] = 'globos';
            $extra[] = 'cajita';
            $extra[] = 'detalle';
            if (! $paraHombre) {
                $extra[] = 'flores';
            }
        }
        if ($paraMujer) {
            $extra[] = 'flores';
            $extra[] = 'peluche';
            $extra[] = 'cajita';
            $extra[] = 'detalle';
            $extra[] = 'perfume';
            $extra[] = 'romance';
        }
        if ($paraHombre) {
            $extra[] = 'caballero';
            $extra[] = 'billetera';
            $extra[] = 'accesorio';
            $extra[] = 'militar';
        }

        $seg = (new AudienceSegment)->parse($message);
        $extra = array_merge($extra, $seg->extraTokens());

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
            if ($cliente && preg_match('/\b(mi\s+pedidos?|mis\s+pedidos?|mis\s+compras|estado\s+de\s+mi|seguimiento|todos\s+mis)\b/iu', $message)) {
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
        string $message = '',
    ): string {
        $lines = [];
        $lines[] = 'intent_detectado: '.$intent;
        $seg = (new AudienceSegment)->parse($message);
        $lines[] = 'destinatario_regalo: '.$seg->label();
        $prio = $seg->boostTags();
        $lines[] = 'prioridad_tags: '.($prio === [] ? '-' : implode(', ', $prio));
        if ($seg->shouldAsk() && in_array($intent, ['product', 'mixed', 'catalog'], true)) {
            $lines[] = 'Si faltan género o edad del DESTINATARIO, pregunta UNA vez: ¿es para hombre o mujer, y más o menos 10, 20, 30, 40, 50 o 60 años?';
        }
        $lines[] = 'total_productos_activos: '.$catalogCount;
        $budget = $this->parseBudget($message);
        if ($budget) {
            $min = (float) ($budget['min'] ?? 0);
            $max = $budget['max'];
            $txt = $max === null
                ? 'desde S/ '.number_format($min, 2, '.', '')
                : 'hasta S/ '.number_format((float) $max, 2, '.', '').($min > 0 ? ' (mínimo S/ '.number_format($min, 2, '.', '').')' : '');
            $lines[] = 'presupuesto_cliente: '.$txt.'. SOLO habla de productos de la lista (ya filtrados). Si hay lista, no digas que no hay.';
        }
        $lines[] = 'Cliente: '.($cliente
            ? trim($cliente->nombre.' '.($cliente->apellido ?? '')).' (id '.$cliente->id_cliente.')'
            : 'invitado (no autenticado)');

        $lines[] = 'Productos encontrados:';
        if ($products === []) {
            $lines[] = '- (ninguno para esta consulta)';
        } else {
            foreach ($products as $p) {
                $lines[] = sprintf(
                    '- id=%d | %s | S/ %s | stock=%d | tags=%s | desc=%s',
                    $p->id_producto,
                    $p->nombre,
                    number_format((float) $p->precio_final, 2, '.', ''),
                    (int) $p->stock,
                    $p->etiquetas ?: '-',
                    mb_substr(trim((string) ($p->descripcion ?? '')), 0, 120) ?: '-'
                );
            }
        }

        $lines[] = 'Pedido:';
        $lines[] = $pedido === null
            ? '- (sin pedido en contexto)'
            : '- '.json_encode($pedido, JSON_UNESCAPED_UNICODE);

        $lines[] = 'Ayuda fija: registro correo o Google; compra en app/web; pagos Culqi prueba / Yape / efectivo; recojo gratis o envío express (Huancayo 8, Junín 12, Lima 18, resto 25 soles). Pedidos 24/7 en app. Recojo en Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.';

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
        if ($intent === 'help') {
            return 'Puedo ayudarte con productos, precios, stock, cómo comprar, formas de pago o el estado de tu pedido. ¿Buscas algo del catálogo o un regalo para alguien?';
        }
        if ($intent === 'courtesy') {
            return '¡Con gusto! Si se te ocurre otro regalo o producto, aquí estoy.';
        }

        if ($intent === 'account') {
            return 'Para crear tu usuario: Iniciar sesión → Regístrate (nombre, correo y contraseña) o «Continuar con Google» con tu Gmail.';
        }

        if ($intent === 'howto') {
            return 'Para comprar: 1) Elige el producto 2) Agregar al carrito 3) Entrega (recojo en tienda gratis o envío) 4) Paga con tarjeta, Yape o efectivo. En el chat también puedes tocar Agregar en la tarjeta. Si ya tienes un pedido, míralo en Mis compras.';
        }

        if ($intent === 'payment') {
            return 'Formas de pago en Estilo Dorado: tarjeta (Culqi en modo prueba), Yape y efectivo. El pago se hace en el flujo de compra o desde Mis compras si el pedido quedó pendiente.';
        }

        if ($intent === 'offtopic') {
            return 'Eso queda fuera de lo que puedo ayudarte: soy Dori, de la tienda Estilo Dorado (regalos y detalles). ¿Buscas un producto o un regalo? '.$this->contactoCorto();
        }

        if (in_array($intent, ['product', 'mixed'], true) && $products === []) {
            $budget = $this->parseBudget($message);
            if ($budget) {
                $tope = $budget['max'] !== null
                    ? 'S/ '.rtrim(rtrim(number_format((float) $budget['max'], 2, '.', ''), '0'), '.')
                    : 'ese rango';

                return "No tengo productos con stock en {$tope} ahora. ¿Subimos un poco el presupuesto o buscas otra categoría (cajita, flores, billetera)?";
            }
            if (preg_match('/\b(oso|osos|osito|ositos|teddy)\b/u', mb_strtolower($message))) {
                return 'No tenemos peluches de oso / ositos en el catálogo. En Inicio puedes ver otros peluches o detalles (por nombre, no como osos). ¿Buscas otra cosa, por ejemplo cerdita o cajita?';
            }

            if (preg_match('/gracias|bonit|precios|lind[oa]|hermos|encant|divin/u', mb_strtolower($message))) {
                return '¡Con gusto! Si se te ocurre otro regalo o producto, aquí estoy.';
            }
            return 'No encontré eso en el catálogo. Prueba otra palabra (cajita, flores, billetera) o mira Inicio. '.$this->contactoCorto();
        }

        if ($intent === 'catalog_count') {
            return "Tenemos {$catalogCount} productos en el catálogo. Dime para quién es el detalle o un nombre (cajita, flores, billetera) y te recomiendo algunos.";
        }

        if ($intent === 'catalog') {
            if ($products === []) {
                return "Tenemos {$catalogCount} productos en el catálogo. Ábrelo en Inicio para verlos todos, o dime para quién buscas y te recomiendo algunos.";
            }

            return "Tenemos más de {$catalogCount} productos. Te recomendamos estos; el precio y el stock van en las tarjetas. ¿Es para alguien en especial?";
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
            $budget = $this->parseBudget($message);
            if ($budget && $budget['max'] !== null) {
                $tope = 'S/ '.rtrim(rtrim(number_format((float) $budget['max'], 2, '.', ''), '0'), '.');

                return 'Dentro de tu presupuesto (hasta '.$tope.') te recomiendo estas opciones. Precio y stock van en las tarjetas; toca Ver o Agregar.';
            }
            if (count($products) === 1) {
                $p = $products[0];
                $stock = (int) $p->stock;

                return sprintf(
                    'Te recomiendo %s. Precio y stock están en la tarjeta; usa Ver o Agregar.%s',
                    $p->nombre,
                    $stock < 1 ? ' Por ahora está agotado.' : ''
                );
            }

            return 'Te recomendamos estas opciones. El precio y el stock van en las tarjetas; toca Ver o Agregar. ¿Quieres otra idea o un presupuesto?';
        }

        return 'No encontré un producto exacto con esa búsqueda. Prueba con otra palabra (ej. «cerdita», «cajita», «flores») o revisa el catálogo en Inicio. También puedo explicar cómo comprar o pagar.';
    }

    private function productCard(Producto $p): array
    {
        return [
            'id' => $p->id_producto,
            'nombre' => $p->nombre,
            'precio' => (float) $p->precio_final,
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
