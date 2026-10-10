<?php
namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        $productos = Producto::take(6)->get();
        return view('public.index', compact('productos'));
    }

    public function catalogo()
    {
        $categorias = Categoria::all();
        $productos = Producto::all();
        return view('public.catalogo', compact('productos', 'categorias'));
    }
}