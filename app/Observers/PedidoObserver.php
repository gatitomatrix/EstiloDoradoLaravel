<?php

namespace App\Observers;

use App\Models\Pedido;
use App\Services\PedidoMailer;
use Illuminate\Support\Facades\DB;

class PedidoObserver
{
    public function created(Pedido $pedido): void
    {
        $this->queue($pedido);
    }

    public function updated(Pedido $pedido): void
    {
        if ($pedido->wasChanged('estado')) {
            $this->queue($pedido);
        }
    }

    private function queue(Pedido $pedido): void
    {
        $id = (int) $pedido->id_pedido;
        $run = function () use ($id) {
            $fresh = Pedido::with(['cliente', 'detalles.producto'])->find($id);
            if ($fresh) {
                app(PedidoMailer::class)->notify($fresh);
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($run);
        } else {
            $run();
        }
    }
}
