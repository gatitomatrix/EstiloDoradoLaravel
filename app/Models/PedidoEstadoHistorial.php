<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoEstadoHistorial extends Model
{
    protected $table = 'pedido_estado_historial';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido','estado_anterior','estado_nuevo','fecha','comentario','id_empleado'
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return \Carbon\Carbon::instance($date)
            ->timezone('America/Lima')
            ->format('Y-m-d H:i:s');
    }
}
