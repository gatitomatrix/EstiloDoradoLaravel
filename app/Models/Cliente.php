<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // Importante: autenticable
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Cliente extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public const PROVIDER_LOCAL = 'local';
    public const PROVIDER_GOOGLE = 'google';

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    public $timestamps = false; // tu tabla solo tiene created_at por defecto; si quisieras, puedes mapearlo

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'email',
        'direccion',
        'contrasena', // hash
        'auth_provider',
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    // Sobrescribimos para que Laravel use 'contrasena' en lugar de 'password'
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function esGoogle(): bool
    {
        return strtolower((string) ($this->auth_provider ?: self::PROVIDER_LOCAL)) === self::PROVIDER_GOOGLE;
    }

    /** Payload de sesión para web y app. No incluye contraseña. */
    public function toAuthArray(): array
    {
        return [
            'id_cliente' => $this->id_cliente,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'telefono' => \App\Support\Celular::deCliente($this->telefono),
            'email' => $this->email,
            'direccion' => $this->direccion,
            'auth_provider' => $this->esGoogle() ? self::PROVIDER_GOOGLE : self::PROVIDER_LOCAL,
            'es_google' => $this->esGoogle(),
        ];
    }
}
