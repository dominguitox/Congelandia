<?php
namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\ListaPrecio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['imagenes', 'precioActual'])->get();
        return view('public.index', compact('productos'));
    }

    public function catalogo()
    {
        $categorias = Categoria::all();
        $productos = Producto::with('imagenes')
            ->addSelect([
                'precio' => ListaPrecio::select('precioVenta')
                    ->whereColumn('codigoProducto', 'producto.codigo')
                    ->orderByDesc('fechaInicio')
                    ->limit(1)
            ])
            ->get();

        return view('public.catalogo', compact('productos', 'categorias'));
    }
}