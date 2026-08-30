<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Asistente\WhatsappEscalation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AsistenteAdminController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('asistente_logs')) {
            return response()->json([
                'items' => [],
                'stats' => ['total' => 0, 'sin_producto' => 0, 'whatsapp' => 0],
                'max_id' => 0,
                'nuevos' => [],
            ]);
        }

        $q = DB::table('asistente_logs');
        $maxId = (int) (clone $q)->max('id');

        $after = (int) $request->query('after_id', 0);
        $nuevos = [];
        if ($after > 0) {
            $nuevos = (clone $q)->where('id', '>', $after)->orderBy('id')->limit(20)->get()
                ->map(fn ($r) => $this->present($r));
        }

        $items = DB::table('asistente_logs')->orderByDesc('id')->limit(80)->get()
            ->map(fn ($r) => $this->present($r));

        return response()->json([
            'stats' => [
                'total' => (clone $q)->count(),
                'sin_producto' => (clone $q)->where('tipo', 'sin_producto')->count(),
                'whatsapp' => (clone $q)->where('whatsapp', 1)->count(),
            ],
            'items' => $items,
            'max_id' => $maxId,
            'nuevos' => $nuevos,
        ]);
    }

    private function present(object $r): object
    {
        $r->created_at = Carbon::parse($r->created_at)
            ->timezone('America/Lima')
            ->format('d/m/Y H:i');
        $tipo = (string) ($r->queja_tipo ?? '');
        $r->queja_label = $tipo !== '' ? (new WhatsappEscalation)->quejaLabel($tipo) : null;

        return $r;
    }
}
