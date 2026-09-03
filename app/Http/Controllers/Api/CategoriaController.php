<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        return Categoria::select('id_categoria','nombre')->orderBy('id_categoria')->get();
    }

    public function productos($id, Request $request)
    {
        $per = (int)($request->get('per_page', 12));
        $sort = $request->get('sort','created_at');
        $order = $request->get('order','desc');

        $query = Producto::where('id_categoria',$id)
            ->where('estado','activo');

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function($w) use ($term){
                $w->where('nombre','like',"%$term%")
                  ->orWhere('descripcion','like',"%$term%");
            });
        }

        if ($request->filled('precio_min')) $query->where('precio_venta','>=',(float)$request->precio_min);
        if ($request->filled('precio_max')) $query->where('precio_venta','<=',(float)$request->precio_max);

        return $query->orderBy($sort,$order)->paginate($per);
    }
}
