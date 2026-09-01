<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = true; // tu tabla tiene created_at y updated_at

    protected $fillable = [
        'nombre',
        'descripcion',
        'etiquetas',
        'precio_compra',
        'precio_venta',
        'descuento_pct',
        'oferta_hasta',
        'stock',
        'id_categoria',
        'id_proveedor',
        'imagen_url',
        'estado',   // 'activo' | 'inactivo'
        'slug',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta'  => 'decimal:2',
        'descuento_pct' => 'float',
        'oferta_hasta'  => 'date',
        'stock'         => 'integer',
        'id_categoria'  => 'integer',
        'id_proveedor'  => 'integer',
        // 'estado' es enum string, lo dejamos como string
    ];

    protected $appends = ['precio_final', 'descuento_aplicado', 'en_oferta'];

    public function getPrecioFinalAttribute(): float
    {
        return app(\App\Services\PrecioService::class)->precioFinal($this);
    }

    public function getDescuentoAplicadoAttribute(): float
    {
        return app(\App\Services\PrecioService::class)->pctEfectivo($this);
    }

    public function getEnOfertaAttribute(): bool
    {
        return $this->descuento_aplicado > 0;
    }

    public function categoria()
    {
        return $this->belongsTo(\App\Models\Categoria::class, 'id_categoria', 'id_categoria');
    }
}
