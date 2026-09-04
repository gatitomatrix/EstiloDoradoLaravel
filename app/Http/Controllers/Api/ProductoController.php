<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    // GET /api/productos?q=&categoria=&proveedor=&estado=
    public function index(\Illuminate\Http\Request $r)
    {
        $q = Producto::query()->select([
            'id_producto',
            'nombre',
            'descripcion',
            'etiquetas',
            'precio_venta',
            'descuento_pct',
            'oferta_hasta',
            'stock',
            'id_categoria',
            'id_proveedor',
            'imagen_url',
            'estado',
            'slug',
        ]);

        // Catálogo público: solo activos, salvo que pidan otro estado
        if ($r->filled('estado')) {
            $q->where('estado', $r->estado);
        } else {
            $q->where(function ($w) {
                $w->whereNull('estado')->orWhere('estado', 'activo');
            });
        }

        if ($r->filled('q')) {
            $term = trim((string) $r->q);
            $tokens = preg_split('/\s+/u', mb_strtolower($term)) ?: [];
            $tokens = array_values(array_filter($tokens, fn ($t) => mb_strlen($t) >= 2));
            if ($tokens === []) {
                $tokens = [mb_strtolower($term)];
            }
            $q->where(function ($w) use ($tokens) {
                foreach ($tokens as $t) {
                    $variants = [$t];
                    if (str_ends_with($t, 'es') && mb_strlen($t) > 4) {
                        $variants[] = mb_substr($t, 0, -1);
                        $variants[] = mb_substr($t, 0, -2);
                    } elseif (str_ends_with($t, 's') && mb_strlen($t) > 3) {
                        $variants[] = mb_substr($t, 0, -1);
                    }
                    $syn = [
                        'cartera' => ['billetera'],
                        'carteras' => ['billetera', 'billeteras'],
                        'billetera' => ['cartera'],
                        'billeteras' => ['cartera', 'carteras'],
                        'monedero' => ['billetera', 'cartera'],
                    ];
                    foreach ($variants as $v) {
                        foreach ($syn[$v] ?? [] as $s) {
                            $variants[] = $s;
                        }
                    }
                    foreach (array_unique($variants) as $v) {
                        $like = '%'.$v.'%';
                        $w->orWhere('nombre', 'like', $like)
                            ->orWhere('descripcion', 'like', $like)
                            ->orWhere('slug', 'like', $like)
                            ->orWhere('etiquetas', 'like', $like);
                    }
                }
            });
        }
        if ($r->filled('categoria')) {
            $q->where('id_categoria', $r->categoria);
        }

        try {
            return $q->orderByDesc('id_producto')->get();
        } catch (\Throwable $e) {
            $q = Producto::query()->select([
                'id_producto',
                'nombre',
                'descripcion',
                'precio_venta',
                'stock',
                'id_categoria',
                'imagen_url',
                'estado',
            ]);
            if ($r->filled('estado')) {
                $q->where('estado', $r->estado);
            } else {
                $q->where(function ($w) {
                    $w->whereNull('estado')->orWhere('estado', 'activo');
                });
            }
            if ($r->filled('categoria')) {
                $q->where('id_categoria', $r->categoria);
            }

            return $q->orderByDesc('id_producto')->get();
        }
    }

    // GET /api/productos/{id}
    public function show($id)
    {
        $prod = Producto::find($id);
        if (!$prod) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json($prod, 200);
    }

    // POST /api/productos
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'        => 'required|string|max:100',
            'descripcion'   => 'nullable|string',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',
            'stock'         => 'nullable|integer|min:0',
            'id_categoria'  => 'required|integer|exists:categorias,id_categoria',
            'id_proveedor'  => 'required|integer|exists:proveedores,id_proveedor',
            'imagen_url'    => 'nullable|url|max:255',
            'estado'        => ['nullable', Rule::in(['activo','inactivo'])],
            'slug'          => 'nullable|string|max:140|unique:productos,slug',
        ]);

        // defaults
        if (!isset($data['stock']))  $data['stock'] = 0;
        if (!isset($data['estado'])) $data['estado'] = 'activo';

        // generar slug si no viene
        if (empty($data['slug'])) {
            $base = Str::slug($data['nombre']);
            $slug = $base;
            $i = 1;
            while (Producto::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        $prod = Producto::create($data);
        return response()->json($prod, 201);
    }

    // PUT/PATCH /api/productos/{id}
    public function update(Request $request, $id)
    {
        $prod = Producto::find($id);
        if (!$prod) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $data = $request->validate([
            'nombre'        => 'sometimes|required|string|max:100',
            'descripcion'   => 'sometimes|nullable|string',
            'precio_compra' => 'sometimes|required|numeric|min:0',
            'precio_venta'  => 'sometimes|required|numeric|min:0',
            'stock'         => 'sometimes|nullable|integer|min:0',
            'id_categoria'  => 'sometimes|required|integer|exists:categorias,id_categoria',
            'id_proveedor'  => 'sometimes|required|integer|exists:proveedores,id_proveedor',
            'imagen_url'    => 'sometimes|nullable|url|max:255',
            'estado'        => ['sometimes', 'required', Rule::in(['activo','inactivo'])],
            'slug'          => [
                'sometimes','nullable','string','max:140',
                Rule::unique('productos','slug')->ignore($prod->id_producto, 'id_producto')
            ],
        ]);

        // si se cambia nombre y no envías slug, podemos regenerarlo opcionalmente
        if (isset($data['nombre']) && !isset($data['slug'])) {
            $base = Str::slug($data['nombre']);
            $slug = $base;
            $i = 1;
            while (Producto::where('slug', $slug)
                    ->where('id_producto', '<>', $prod->id_producto)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        $prod->update($data);
        return response()->json($prod, 200);
    }

    // DELETE /api/productos/{id}
    public function destroy($id)
    {
        $prod = Producto::find($id);
        if (!$prod) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        $prod->delete();
        return response()->json(['message' => 'Producto eliminado'], 200);
    }
}
