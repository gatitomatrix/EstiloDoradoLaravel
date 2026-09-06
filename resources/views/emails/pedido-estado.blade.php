<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $titulo }}</title>
</head>
<body style="margin:0;padding:0;background:#F7F1E6;font-family:Georgia,'Times New Roman',serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F1E6;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FFFEFA;border:1px solid #E7DAC6;border-radius:16px;overflow:hidden;">
          <tr>
            <td style="background:#2D2418;padding:22px 32px;text-align:center;">
              <img src="{{ $logoUrl }}" alt="Estilo Dorado" width="72" height="72" style="display:block;margin:0 auto 10px;border-radius:14px;border:2px solid #D4AF37;">
              <p style="margin:0;color:#D4AF37;letter-spacing:3px;font-size:12px;text-transform:uppercase;">Estilo Dorado</p>
              <h1 style="margin:10px 0 0;color:#FFFEFA;font-size:22px;font-weight:normal;">{{ $titulo }}</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:8px 24px 0;text-align:center;background:#FFFEFA;">
              <img src="{{ $doriUrl }}" alt="Dori" width="240" style="display:block;margin:12px auto 0;max-width:240px;width:100%;height:auto;border-radius:12px;">
              <p style="margin:10px 0 0;color:#8A6D1D;font-size:14px;font-style:italic;">Hola, soy Dori. Gracias por confiar en nosotros.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:24px 32px 8px;">
              <p style="margin:0 0 16px;color:#2D2418;font-size:16px;line-height:1.6;">
                Hola {{ $pedido->cliente->nombre ?? 'cliente' }},
              </p>
              <p style="margin:0 0 20px;color:#2D2418;font-size:15px;line-height:1.65;">
                {{ $intro }}
              </p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F1E6;border-radius:12px;margin:0 0 20px;">
                <tr>
                  <td style="padding:16px 18px;color:#2D2418;font-size:14px;line-height:1.7;">
                    <strong>Pedido:</strong> #{{ $pedido->id_pedido }}<br>
                    <strong>Estado:</strong> {{ $pedido->estado }}<br>
                    <strong>Total:</strong> S/ {{ number_format((float) $pedido->total, 2) }}<br>
                    <strong>Pago:</strong> {{ $pedido->forma_pago ?? '—' }}
                    @if(!empty($celular))
                    <br><strong>Celular:</strong> +51 {{ $celular }}
                    @endif
                  </td>
                </tr>
              </table>
              @if($pedido->detalles && $pedido->detalles->count())
              <p style="margin:0 0 8px;color:#2D2418;font-size:14px;"><strong>Lo que pediste</strong></p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;font-size:13px;color:#2D2418;">
                @foreach($pedido->detalles as $d)
                <tr>
                  <td style="padding:6px 0;border-bottom:1px solid #E7DAC6;">
                    {{ $d->producto->nombre ?? ('Producto '.$d->id_producto) }} × {{ $d->cantidad }}
                  </td>
                  <td style="padding:6px 0;border-bottom:1px solid #E7DAC6;text-align:right;white-space:nowrap;">
                    S/ {{ number_format((float) $d->precio_unitario * (int) $d->cantidad, 2) }}
                  </td>
                </tr>
                @endforeach
              </table>
              @endif
              <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto 24px;">
                <tr>
                  <td align="center" style="background:#D4AF37;border-radius:999px;">
                    <a href="{{ $pedidoUrl }}" style="display:inline-block;padding:14px 32px;color:#2D2418;text-decoration:none;font-size:15px;font-weight:bold;">
                      Ver mi pedido
                    </a>
                  </td>
                </tr>
              </table>
              <p style="margin:0;color:#8A7B65;font-size:13px;line-height:1.5;">
                Si el botón no abre: <a href="{{ $pedidoUrl }}" style="color:#B8860B;">{{ $pedidoUrl }}</a>
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:18px 32px 28px;border-top:1px solid #E7DAC6;color:#8A7B65;font-size:12px;text-align:center;">
              Estilo Dorado · Cerro de Pasco, Perú
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
