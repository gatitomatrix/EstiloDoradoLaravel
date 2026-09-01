<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $table = 'promociones';

    protected $fillable = [
        'titulo',
        'texto_cinta',
        'porcentaje',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'porcentaje' => 'float',
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];
}
