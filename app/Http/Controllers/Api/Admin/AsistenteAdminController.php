<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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

        return response()->json([
            'stats' => [
                'total' => (clone $q)->count(),
                'sin_producto' => (clone $q)->where('tipo', 'sin_producto')->count(),
                'whatsapp' => (clone $q)->where('whatsapp', 1)->count(),
            ],
            'items' => DB::table('asistente_logs')->orderByDesc('id')->limit(80)->get(),
        ]);
    }
}
