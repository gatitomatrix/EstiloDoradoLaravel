@component('mail::message')
# {{ $titulo }}

Hola {{ $pedido->cliente->nombre ?? 'cliente' }},

{{ $intro }}

**Pedido:** #{{ $pedido->id_pedido }}  
**Estado:** {{ $pedido->estado }}  
**Total:** S/ {{ number_format((float) $pedido->total, 2) }}  
**Pago:** {{ $pedido->forma_pago ?? '—' }}

@if($pedido->detalles && $pedido->detalles->count())
**Detalle**
@foreach($pedido->detalles as $d)
- {{ $d->producto->nombre ?? ('Producto '.$d->id_producto) }} × {{ $d->cantidad }} — S/ {{ number_format((float) $d->precio_unitario, 2) }}
@endforeach
@endif

@component('mail::button', ['url' => config('app.url')])
Ver la tienda
@endcomponent

Gracias por confiar en **Estilo Dorado**.
@endcomponent
