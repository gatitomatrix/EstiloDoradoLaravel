<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AsistenteAdminController extends Controller
{
    public function index()
    {
        if (! Schema::hasTable('asistente_logs')) {
            return response()->json([
                'items' => [],
                'stats' => ['total' => 0, 'sin_producto' => 0, 'whatsapp' => 0],
            ]);
        }

        $q = DB::table('asistente_logs');

        $items = DB::table('asistente_logs')->orderByDesc('id')->limit(80)->get()
            ->map(function ($r) {
                $r->created_at = Carbon::parse($r->created_at)
                    ->timezone('America/Lima')
                    ->format('d/m/Y H:i');

                return $r;
            });

        return response()->json([
            'stats' => [
                'total' => (clone $q)->count(),
                'sin_producto' => (clone $q)->where('tipo', 'sin_producto')->count(),
                'whatsapp' => (clone $q)->where('whatsapp', 1)->count(),
            ],
            'items' => $items,
        ]);
    }
}
