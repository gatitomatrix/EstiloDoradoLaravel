<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaAdminController extends Controller
{
    public function index(Request $request)
    {
        if ($request->boolean('all')) {
            return response()->json([
                'data' => Categoria::orderBy('nombre')->get(['id_categoria', 'nombre']),
            ]);
        }

        $per = (int) ($request->get('per_page', 20));
        $q = Categoria::query();

        if ($request->filled('q')) {
            $term = $request->q;
            $q->where('nombre', 'like', "%{$term}%");
        }

        return $q->orderBy('nombre')
            ->paginate($per, ['id_categoria', 'nombre', 'descripcion']);
    }

    public function show($id)
    {
        $c = Categoria::find($id);
        if (! $c) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return $c;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:categorias,nombre'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);
        $c = Categoria::create($data);

        return response()->json($c, 201);
    }

    public function update(Request $request, $id)
    {
        $c = Categoria::find($id);
        if (! $c) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('categorias', 'nombre')->ignore($c->id_categoria, 'id_categoria')],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);
        $c->update($data);

        return response()->json($c);
    }

    public function destroy($id)
    {
        $c = Categoria::find($id);
        if (! $c) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        if ($c->productos()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: hay productos en esta categoría'], 409);
        }
        $c->delete();

        return response()->json(['ok' => true]);
    }
}
