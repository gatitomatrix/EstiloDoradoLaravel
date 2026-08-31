<?php

namespace App\Services\Asistente;

/**
 * Casos que Dori no debe resolver: deriva a WhatsApp de la tienda (gratis, wa.me).
 */
class WhatsappEscalation
{
    public function match(string $message): ?string
    {
        $m = mb_strtolower(trim($message));

        $rules = [
            'humano' => '/p[aá]same con|hablar con (la |el )?(due[nñ]a|gerente|asesor|persona)|con marlene|agente humano|persona real|quiero un (asesor|humano)/u',
            'reclamo' => '/reclamo|queja|quejarme|me quejo|tengo una queja|inconform|molestia|(mi|el) producto lleg|empapad|aplastad|machucad|abollad|mojad|humedec|malograd|quebr[ao]|da[nñ]ad|no era lo que|producto incorrecto|me lleg[oó].{0,25}(mal|roto|aplast|machuc|aboll|mojad|empap|tarde|sucio|abierto)|lleg[oó].{0,20}(aplast|roto|machuc|empap)|no me lleg[oó]/u',
            'devolucion' => '/devoluci[oó]n|devolverlo|quiero cambiar(lo)? el producto|cambio (del|de) producto/u',
            'cobro' => '/cobr(aron|ado|aste).{0,24}(de\s*m[aá]s|demas|dem[aá]s|dos veces|doble)|doble cargo|me descontaron (de m[aá]s|dos)|cobro (de m[aá]s|demas|incorrecto)|me (han )?cobrado de/u',
            'comprobante' => '/anular (boleta|factura)|cambiar (el )?ruc|raz[oó]n social|comprobante mal|factura mal emitida/u',
            'mayoreo' => '/mayoreo|por mayor|descuento por (cantidad|lote)|precio especial|cincuenta cajas|\b\d{2,}\s*cajas/u',
            'medida' => '/a medida|con esta foto|este texto exacto|logo de mi (empresa|negocio)|dise[nñ]o (m[ií]o|propio)/u',
            'hora' => '/s[ií] o s[ií]|garantiza(r|me)? (que )?lleg|a las \d{1,2}.*(lleg|entreg)|hora exacta/u',
            'zona' => '/caser[ií]o|agencia shalom|cotiz(a|ame).*shalom|paraje|anexo (?!de)/u',
            'yape' => '/yape[eé].*(pendiente|no (aparece|figura|refleja))|pagu[eé].*no (aparece|figura)|transfer[ií].*no (aparece|figura)/u',
            'acceso' => '/no me deja entrar|no puedo (iniciar|entrar|logu)|me robaron la cuenta|olvid[eé] (el )?correo/u',
            'tercero' => '/cu[aá]nto compr[oó]|datos de (la|el|otra)|pedido de otra|sra\.|se[nñ]or(a|ita) p[eé]rez/u',
            'empleo' => '/vacante|contratan|pr[aá]cticas|trabajo con ustedes|est[aá]n contratando/u',
            'legal' => '/alquiler del local|ruc para tr[aá]mite|datos de la empresa para notario|escritura p[uú]blica/u',
            'horario' => '/horario (de )?(atenci[oó]n|tienda|local)|a qu[eé] hora abren|[aá]nen (hoy|ma[nñ]ana)|est[aá]n abiertos/u',
            'almacen' => '/almac[eé]n de atr[aá]s|stock f[ií]sico|tienen en tienda (aunque|si) no (sale|aparece)/u',
            'salud' => '/digesa|al[eé]rgen|apto para beb[eé]|certificaci[oó]n (m[eé]dica|sanitaria)/u',
            'abogado' => '/denuncia|abogado|demandar|indescope|indecopi|sunat (multa|fiscal)/u',
            'tecnico' => '/no carga el mapa|se cerr[oó] al pagar|error al pagar|crashe[oó]|se trab[oó] el pago/u',
            'direccion' => '/cambiar (la )?direcci[oó]n (del|de mi) pedido|ya pagu[eé].*otra direcci[oó]n/u',
            'motorizado' => '/d[oó]nde est[aá] el (motorizado|repartidor|courier)|rastrear (el )?motorizado/u',
            'cupon' => '/c[oó]digo (de )?descuento|cup[oó]n|promo (que )?me prometieron/u',
            'cancelar_pagado' => '/cancelar (el )?pedido (ya )?pagado|me arrepent[ií].*ya pagu[eé]/u',
            'insulto' => '/\b(estafa(dores)?|ladrones|imb[eé]cil|est[uú]pid|hdp|mierda|puta)\b/u',
        ];

        foreach ($rules as $key => $pat) {
            if (preg_match($pat, $m)) {
                return $key;
            }
        }

        return null;
    }

    /** "tengo una queja" sin decir qué pasó. */
    public function isVagueComplaint(string $message): bool
    {
        $m = mb_strtolower(trim($message));
        $vague = (bool) preg_match('/queja|reclamo|quejarme|me quejo|inconform|molestia/u', $m);
        if (! $vague) {
            return false;
        }
        if (preg_match('/aplast|empap|machuc|aboll|mojad|humed|malograd|quebr|da[nñ]ad|roto|sucio|cobr|demas|no me lleg|no lleg|aun no lleg|aún no lleg|devoluci|devolver|extravi|tarde|demor/u', $m)) {
            return false;
        }

        return true;
    }

    /** Tipo de queja a partir del texto, o null si no se entiende. */
    public function classifyQueja(string $message): ?string
    {
        $m = mb_strtolower($message);
        if (preg_match('/aplast|empap|machuc|aboll|mojad|humed|malograd|quebr|da[nñ]ad|roto|sucio|abierto|incomplet/u', $m)
            || preg_match('/(mi|el) producto lleg|me lleg[oó].{0,25}(mal|roto|aplast)/u', $m)) {
            return 'producto_danado';
        }
        if (preg_match('/cobr|demas|de m[aá]s|doble cargo|descontaron/u', $m)) {
            return 'cobro';
        }
        if (preg_match('/no me lleg|no lleg[oaó]|aun no lleg|aún no lleg|no aparece|extravi|perd[ií]d/u', $m)) {
            return 'no_llego';
        }
        if (preg_match('/devoluci|devolver|cambiar(lo)? el producto/u', $m)) {
            return 'devolucion';
        }
        if (preg_match('/tarde|demor/u', $m)) {
            return 'demora';
        }
        if (preg_match('/queja|reclamo|pas[oó] esto|el problema|me pas[oó]/u', $m) && mb_strlen(trim($message)) > 18) {
            return 'otro';
        }

        return null;
    }

    public function quejaLabel(string $tipo): string
    {
        return match ($tipo) {
            'producto_danado' => 'Producto dañado o malogrado',
            'cobro' => 'Cobro de más o doble cargo',
            'no_llego' => 'No llegó / extravío',
            'devolucion' => 'Cambio o devolución',
            'demora' => 'Demora en la entrega',
            default => 'Otro / a detallar',
        };
    }

    public function replyQueja(string $tipo): string
    {
        $cierre = 'Escríbenos por WhatsApp con tu número de pedido (y una foto si aplica) y te atiende alguien de la tienda. Gracias por confiar en Estilo Dorado.';
        $inicio = match ($tipo) {
            'producto_danado' => 'Lamento mucho que el producto haya llegado en mal estado. Eso lo vemos en persona, no lo resuelvo yo desde el chat.',
            'cobro' => 'Entiendo la preocupación por el cobro. Yo no veo tu banco o Yape; con una captura lo revisan rápido.',
            'no_llego' => 'Siento que no te haya llegado. El rastreo lo confirma la tienda, no el chat.',
            'devolucion' => 'Los cambios y devoluciones los coordina la tienda; yo no puedo autorizarlos aquí.',
            'demora' => 'Siento la demora. Los plazos exactos los confirma la tienda.',
            default => 'Gracias por contarme. Un reclamo así lo ve una persona de la tienda.',
        };

        return $inicio.' '.$cierre;
    }

    public function askComplaintDetail(): string
    {
        return 'Lamento que hayas tenido un problema. Para derivarte bien, cuéntame qué pasó: ¿el producto llegó dañado, te cobraron de más, no te llegó, quieres devolverlo u otra cosa?';
    }


    public function reply(string $key): string
    {
        $cierre = "Escríbenos por WhatsApp con tu número de pedido (y una foto si aplica) y te atiende alguien de la tienda. Gracias por confiar en Estilo Dorado.";

        $inicio = match ($key) {
            'reclamo' => 'Lamento mucho que el pedido no haya llegado como esperabas. Un reclamo así lo vemos en persona, no desde el chat.',
            'devolucion' => 'Los cambios y devoluciones los coordina la tienda; yo no puedo autorizarlos desde aquí.',
            'cobro' => 'Entiendo la preocupación. Yo no veo el movimiento de tu banco o Yape; con una captura lo revisan rápido.',
            'comprobante' => 'Boletas, facturas y datos de RUC los corrige la tienda. Yo no emito ni anulo comprobantes.',
            'mayoreo' => 'Los precios por volumen los cotiza la tienda según cantidad. Aquí solo veo el precio de catálogo.',
            'medida' => 'Si no está en el catálogo (foto, logo o texto exacto), te lo cotizan por WhatsApp.',
            'hora' => 'No puedo garantizar día y hora exactos: el envío es un estimado. La tienda te confirma si hay una fecha límite.',
            'zona' => 'No cotizo Shalom en vivo ni agencias raras. Con el destino te dicen si hay recojo o envío.',
            'yape' => 'No veo tu Yape al instante. Si ya pagaste y sigue pendiente, con la captura y el pedido lo cuadran.',
            'acceso' => 'Si no puedes entrar a tu cuenta, la tienda te orienta (no nos envíes tu contraseña).',
            'tercero' => 'No puedo dar datos de otras personas. Si es tu pedido, la tienda te ayuda por WhatsApp.',
            'empleo' => 'Vacantes y prácticas las ve la tienda, no el chat de compras.',
            'legal' => 'Datos legales o trámites de la empresa te los da la tienda, no el asistente.',
            'horario' => 'Puedes pedir en la app a cualquier hora. El horario de recojo en el local lo confirma la tienda.',
            'almacen' => 'Yo solo veo el stock de la app. Si crees que hay en el local y no aparece, pregúntales por WhatsApp.',
            'salud' => 'No certifico temas médicos ni sanitarios. Consulta el empaque o a la tienda.',
            'abogado' => 'Eso ya es un tema formal. Mejor escríbeles por WhatsApp y lo ve una persona.',
            'tecnico' => 'Si se trabó el pago o el mapa, prueba otra red. Si te cobraron, manda captura y número de pedido.',
            'direccion' => 'Cambiar la dirección de un pedido ya hecho lo confirma la tienda, no el chat.',
            'motorizado' => 'No rastreo al motorizado en vivo. La tienda te da el estado del envío.',
            'cupon' => 'No aplico cupones ni descuentos prometidos por otro canal. La tienda te lo confirma.',
            'cancelar_pagado' => 'Un pedido ya pagado no lo cancelo yo. La tienda te dice si se puede y cómo.',
            'humano' => 'No conecto llamadas desde aquí, pero por WhatsApp te atiende una persona de Estilo Dorado.',
            'insulto' => 'Prefiero seguir con respeto. Si hay un problema con tu compra, la tienda te atiende por WhatsApp.',
            default => 'Eso lo ve mejor una persona de Estilo Dorado; el chat es para el catálogo y tu compra.',
        };

        return $inicio.' '.$cierre;
    }

    public function action(string $userMessage, ?int $pedidoId = null): ?array
    {
        $num = preg_replace('/\D+/', '', (string) config('llm.whatsapp.number', '')) ?? '';
        if (strlen($num) === 9 && str_starts_with($num, '9')) {
            $num = '51'.$num;
        }
        if (strlen($num) < 11) {
            return null;
        }

        $text = 'Hola, soy cliente de Estilo Dorado.';
        if ($pedidoId) {
            $text .= ' Pedido N.° '.$pedidoId.'.';
        }
        $text .= ' Necesito ayuda: '.mb_substr(trim($userMessage), 0, 280);

        return [
            'type' => 'whatsapp',
            'url' => 'https://wa.me/'.$num.'?text='.rawurlencode($text),
            'label' => 'Escribir por WhatsApp',
        ];
    }
}
