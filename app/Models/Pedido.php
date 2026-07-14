<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'fecha_pedido',
        'estado',
        'total',
        'forma_pago',
        'culqi_id',
        'direccion_entrega',
        'observacion',           // ← Agregado
        'comprobante_tipo',
        'comprobante_serie',
        'comprobante_numero',
        'sunat_xml',
        'sunat_pdf',
        'sunat_cdr',
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
    ];

    // ==================== RELACIONES ====================

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function historial()
    {
        return $this->hasMany(PedidoEstadoHistorial::class, 'id_pedido', 'id_pedido');
    }
}