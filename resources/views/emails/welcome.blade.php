@component('mail::message')
# ¡Bienvenido a Estilo Dorado, {{ $cliente->nombre }}!

Tu cuenta ya está lista. Desde la tienda puedes ver el catálogo, armar tu carrito y pagar con tarjeta o Yape.

**Tu correo:** {{ $cliente->email }}

@component('mail::button', ['url' => $tiendaUrl])
Ir a la tienda
@endcomponent

Si no creaste esta cuenta, ignora este mensaje.

**Estilo Dorado**
@endcomponent
