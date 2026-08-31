<?php

namespace App\Services\Asistente;

use App\Models\Cliente;
use App\Models\Pedido;

class ComplaintFlow
{
    public function __construct(private WhatsappEscalation $whatsapp) {}

    public function afterTipo(string $tipo, string $message, ?Cliente $cliente, array $ctx): array
    {
        $ctx['tipo'] = $tipo;
        $ctx['mensaje'] = mb_substr($message, 0, 240);

        if ($cliente) {
            return $this->offerOrders($tipo, $message, $cliente, $ctx);
        }

        $wa = $this->whatsapp->action($this->waText($ctx, $message), null);

        return $this->pack(
            'Gracias por contarme ('.$this->whatsapp->quejaLabel($tipo).'). Si tienes cuenta, inicia sesión aquí mismo y ubico tu pedido. Si no puedes, sigue por WhatsApp: no te dejo sin atención.',
            [
                'awaiting' => 'complaint_login',
                'log_tipo' => 'queja_espera',
                'queja_tipo' => $tipo,
                'urgencia' => true,
                'action' => ['type' => 'login', 'label' => 'Iniciar sesión'],
                'actions' => array_values(array_filter([
                    ['type' => 'login', 'label' => 'Iniciar sesión'],
                    $wa,
                ])),
                'complaint' => $ctx,
            ]
        );
    }

    public function afterLogin(string $tipo, string $message, Cliente $cliente, array $ctx): array
    {
        $ctx['tipo'] = $tipo ?: ($ctx['tipo'] ?? 'otro');

        return $this->offerOrders($ctx['tipo'], $message, $cliente, $ctx);
    }

    public function pickOrder(string $message, Cliente $cliente, array $ctx): array
    {
        $tipo = $ctx['tipo'] ?? 'otro';
        $m = mb_strtolower($message);
        if (preg_match('/\b(otro|ninguno|whatsapp|no\s+est[aá]|no\s+sale)\b/u', $m)) {
            return $this->maybePhone($tipo, $message, $cliente, $ctx);
        }

        $orders = $this->lastPaid($cliente);
        $ids = array_map(fn ($o) => (int) $o['id_pedido'], $orders);
        $pick = null;
        if (preg_match('/\b(\d{1,6})\b/', $message, $hit)) {
            $n = (int) $hit[1];
            if (in_array($n, $ids, true)) {
                $pick = $n;
            } elseif ($n >= 1 && $n <= count($ids)) {
                $pick = $ids[$n - 1];
            }
        }
        if (preg_match('/primer|el\s+1\b|arriba/u', $m) && $ids) {
            $pick = $ids[0];
        }

        if (! $pick) {
            return $this->offerOrders($tipo, $message, $cliente, $ctx, 'No identifiqué el pedido. Elige 1, 2 o 3, el número, u «otro».');
        }

        $ctx['pedido_id'] = $pick;

        return $this->maybePhone($tipo, $message, $cliente, $ctx);
    }

    public function takePhone(string $message, ?Cliente $cliente, array $ctx): array
    {
        $m = mb_strtolower($message);
        if (! preg_match('/\b(no|skip|whatsapp|omit|luego)\b/u', $m)) {
            $digits = preg_replace('/\D+/', '', $message) ?? '';
            if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
                $ctx['phone'] = $digits;
            } elseif (strlen($digits) >= 11) {
                $ctx['phone'] = $digits;
            } else {
                return $this->pack(
                    'Pásame un celular de 9 dígitos (empezando en 9) o escribe «no» para seguir por WhatsApp.',
                    [
                        'awaiting' => 'complaint_phone',
                        'log_tipo' => 'queja_espera',
                        'queja_tipo' => $ctx['tipo'] ?? 'otro',
                        'complaint' => $ctx,
                    ]
                );
            }
        }

        return $this->finish($ctx['tipo'] ?? 'otro', $message, $cliente, $ctx);
    }

    private function offerOrders(string $tipo, string $message, Cliente $cliente, array $ctx, ?string $prefix = null): array
    {
        $orders = $this->lastPaid($cliente);
        if ($orders === []) {
            return $this->maybePhone($tipo, $message, $cliente, $ctx, true);
        }

        $txt = ($prefix ? $prefix.' ' : '')
            .'Estos son tus últimos pedidos pagados. Ver = ficha (el chat no se cierra). «Este es» = queja de ese pedido. O escribe «otro».';

        return $this->pack($txt, [
            'awaiting' => 'complaint_order',
            'log_tipo' => 'queja_espera',
            'queja_tipo' => $tipo,
            'urgencia' => true,
            'pedidos' => $orders,
            'complaint' => $ctx,
        ]);
    }

    private function maybePhone(string $tipo, string $message, Cliente $cliente, array $ctx, bool $sinPedidos = false): array
    {
        $tel = preg_replace('/\D+/', '', (string) ($cliente->telefono ?? '')) ?? '';
        if (strlen($tel) >= 9) {
            $ctx['phone'] = $tel;

            return $this->finish($tipo, $message, $cliente, $ctx, $sinPedidos);
        }

        $intro = $sinPedidos
            ? 'No veo pedidos pagados en esta cuenta. '
            : 'Pedido anotado. ';

        return $this->pack(
            $intro.'Para una atención más rápida, ¿a qué celular te escribimos? (9 dígitos). Si prefieres, escribe «no» y te dejo WhatsApp.',
            [
                'awaiting' => 'complaint_phone',
                'log_tipo' => 'queja_espera',
                'queja_tipo' => $tipo,
                'urgencia' => true,
                'complaint' => $ctx,
            ]
        );
    }

    private function finish(string $tipo, string $message, ?Cliente $cliente, array $ctx, bool $sinPedidos = false): array
    {
        $pid = isset($ctx['pedido_id']) ? (int) $ctx['pedido_id'] : null;
        $waMsg = $this->waText($ctx, $ctx['mensaje'] ?? $message);
        $extra = $sinPedidos ? ' Si no ves el pedido, igual la tienda te atiende.' : '';

        $summary = trim((string) ($ctx['mensaje'] ?? $message));
        if ($pid) {
            $summary = 'Pedido #'.$pid.' · '.$summary;
        }
        $summary = $this->whatsapp->quejaLabel($tipo).' · '.$summary;

        return $this->pack(
            $this->whatsapp->replyQueja($tipo).$extra,
            [
                'awaiting' => null,
                'log_tipo' => 'whatsapp',
                'log_mensaje' => mb_substr($summary, 0, 500),
                'queja_tipo' => $tipo,
                'urgencia' => true,
                'action' => $this->whatsapp->action($waMsg, $pid),
                'complaint' => $ctx,
                'pedido' => $pid ? ['id_pedido' => $pid] : null,
            ]
        );
    }

    private function lastPaid(Cliente $cliente): array
    {
        $rows = Pedido::query()
            ->with(['detalles.producto'])
            ->where('id_cliente', $cliente->id_cliente)
            ->whereNotIn('estado', ['pendiente', 'cancelado'])
            ->orderByDesc('id_pedido')
            ->limit(3)
            ->get();

        $out = [];
        foreach ($rows as $p) {
            $nom = $p->detalles->first()?->producto?->nombre ?? 'Pedido';
            $out[] = [
                'id_pedido' => (int) $p->id_pedido,
                'fecha' => optional($p->fecha_pedido)?->timezone('America/Lima')->format('d/m/Y') ?: '',
                'total' => number_format((float) $p->total, 2, '.', ''),
                'estado' => $p->estado,
                'resumen' => mb_substr((string) $nom, 0, 40),
            ];
        }

        return $out;
    }

    private function waText(array $ctx, string $fallback): string
    {
        $t = 'Queja: '.$this->whatsapp->quejaLabel($ctx['tipo'] ?? 'otro').'. ';
        if (! empty($ctx['pedido_id'])) {
            $t .= 'Pedido N.° '.$ctx['pedido_id'].'. ';
        }
        if (! empty($ctx['phone'])) {
            $t .= 'Celular: '.$ctx['phone'].'. ';
        }
        $t .= 'Detalle: '.mb_substr($ctx['mensaje'] ?? $fallback, 0, 160);

        return $t;
    }

    private function pack(string $reply, array $extra): array
    {
        return array_merge([
            'reply' => $reply,
            'driver' => 'rules',
            'products' => [],
            'pedido' => null,
            'suggestions' => [],
            'action' => null,
            'actions' => [],
            'pedidos' => [],
            'awaiting' => null,
            'urgencia' => false,
        ], $extra);
    }
}
