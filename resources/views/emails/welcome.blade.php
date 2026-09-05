<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bienvenido a Estilo Dorado</title>
</head>
<body style="margin:0;padding:0;background:#F7F1E6;font-family:Georgia,'Times New Roman',serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F1E6;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FFFEFA;border:1px solid #E7DAC6;border-radius:16px;overflow:hidden;">
          <tr>
            <td style="background:#2D2418;padding:28px 32px;text-align:center;">
              <p style="margin:0;color:#D4AF37;letter-spacing:3px;font-size:12px;text-transform:uppercase;">Estilo Dorado</p>
              <h1 style="margin:10px 0 0;color:#FFFEFA;font-size:26px;font-weight:normal;">¡Bienvenido{{ $cliente->nombre ? ', '.$cliente->nombre : '' }}!</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <p style="margin:0 0 16px;color:#2D2418;font-size:16px;line-height:1.6;">
                Gracias por unirte a nuestra tienda. Ya puedes explorar detalles, florales y regalos pensados para cada ocasión.
              </p>
              <p style="margin:0 0 24px;color:#8A7B65;font-size:14px;line-height:1.6;">
                Tu cuenta quedó asociada a <strong style="color:#2D2418;">{{ $cliente->email }}</strong>.
                El catálogo, el carrito y el pago (tarjeta o Yape) te esperan en un clic.
              </p>
              <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto 28px;">
                <tr>
                  <td align="center" style="background:#D4AF37;border-radius:999px;">
                    <a href="{{ $tiendaUrl }}" style="display:inline-block;padding:14px 32px;color:#2D2418;text-decoration:none;font-size:15px;font-weight:bold;letter-spacing:0.4px;">
                      Ir a la tienda
                    </a>
                  </td>
                </tr>
              </table>
              <p style="margin:0;color:#8A7B65;font-size:13px;line-height:1.5;">
                Si el botón no abre, copia este enlace:<br>
                <a href="{{ $tiendaUrl }}" style="color:#B8860B;word-break:break-all;">{{ $tiendaUrl }}</a>
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:18px 32px 28px;border-top:1px solid #E7DAC6;color:#8A7B65;font-size:12px;text-align:center;">
              Si no creaste esta cuenta, puedes ignorar este mensaje.<br>
              Estilo Dorado · Lima, Perú
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
