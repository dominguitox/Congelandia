<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PosController extends Controller
{
    public function index()
    {
        try {
            // Traes todos los productos disponibles
            $productos = DB::select('CALL SP_ListarProductos()');
            // Traes las categorías para los botones de filtro (Bebestibles, Carnes, etc.)
            $categorias = DB::select('CALL SP_ListarCategorias()');

            // Envías ambas variables a la vista del POS
            return view('pos.index', compact('productos', 'categorias'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el POS: ' . $e->getMessage());
        }
    }
}