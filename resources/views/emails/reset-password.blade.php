@component('mail::message')
# Recuperar contraseña

Hola {{ $cliente->nombre }},

Recibimos un pedido para cambiar la contraseña de **{{ $cliente->email }}**.

**Tu código (válido 60 minutos):** {{ $code }}

También puedes abrirlo en la tienda:

@component('mail::button', ['url' => $resetUrl])
Restablecer contraseña
@endcomponent

Si no fuiste tú, ignora este correo. Tu clave no cambia hasta que uses el código.

**Estilo Dorado**
@endcomponent
