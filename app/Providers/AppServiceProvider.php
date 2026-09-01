<?php

namespace App\Providers;

use App\Models\Pedido;
use App\Observers\PedidoObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        date_default_timezone_set((string) (config('app.timezone') ?: 'America/Lima'));
        Pedido::observe(PedidoObserver::class);
    }
}
