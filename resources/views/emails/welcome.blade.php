@component('mail::message')
# ¡Bienvenido a Estilo Dorado, {{ $cliente->nombre }}! 🎉

Gracias por registrarte en nuestra tienda.  
Ahora puedes comprar todos nuestros productos con entrega a domicilio.

**Tu correo:** {{ $cliente->email }}

@component('mail::button', ['url' => 'http://localhost:8000'])
Ir a la tienda
@endcomponent

¡Que tengas un excelente día!  
**El equipo de Estilo Dorado**
@endcomponent