<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PromocionAdminController extends Controller
{
    public function show()
    {
        if (! Schema::hasTable('promociones')) {
            return response()->json(['message' => 'Ejecuta php artisan migrate'], 503);
        }
        $row = Promocion::query()->orderByDesc('id')->first();
        if (! $row) {
            $row = Promocion::create([
                'titulo' => 'Campaña de temporada',
                'texto_cinta' => '',
                'porcentaje' => 0,
                'activo' => false,
            ]);
        }

        return response()->json($row);
    }

    public function update(Request $request)
    {
        if (! Schema::hasTable('promociones')) {
            return response()->json(['message' => 'Ejecuta php artisan migrate'], 503);
        }
        $data = $request->validate([
            'titulo' => ['nullable', 'string', 'max:120'],
            'texto_cinta' => ['nullable', 'string', 'max:255'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:90'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['required', 'boolean'],
        ]);
        $row = Promocion::query()->orderByDesc('id')->first() ?? new Promocion();
        $row->fill($data);
        $row->save();

        return response()->json($row);
    }
}
