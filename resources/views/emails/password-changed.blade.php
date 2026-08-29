@component('mail::message')
# Contraseña actualizada

Hola {{ $cliente->nombre }},

La contraseña de tu cuenta ({{ $cliente->email }}) se actualizó correctamente.

Si **no** fuiste tú, escríbenos por WhatsApp a la tienda.

@component('mail::button', ['url' => config('app.url')])
Ir a Estilo Dorado
@endcomponent

**Estilo Dorado**
@endcomponent
