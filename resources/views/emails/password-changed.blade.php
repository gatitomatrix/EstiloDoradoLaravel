@component('mail::message')
# Contraseña actualizada

Hola {{ $cliente->nombre }},

La contraseña de tu cuenta ({{ $cliente->email }}) se actualizó correctamente.

@component('mail::button', ['url' => $tiendaUrl])
Ir a la tienda
@endcomponent

Si **no** fuiste tú, escríbenos por WhatsApp.

**Estilo Dorado**
@endcomponent
