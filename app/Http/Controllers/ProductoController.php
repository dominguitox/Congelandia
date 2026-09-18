<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductoController extends Controller
{
    // 1. Cambiamos el nombre a 'store' para coincidir con tu web.php
    public function crearProducto(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'idCategoria' => 'required|integer',
            'precioVenta' => 'required|numeric|min:0',
            'idProveedor' => 'required|integer',
            'stockInicial' => 'required|integer|min:0',
            'precioCompra' => 'required|numeric|min:0',
            'fechaVencimiento' => 'required|date'
        ]);

        try {
            $idUsuario = auth()->user()->idUsuario;

            DB::statement('CALL SP_CrearProducto(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->codigo,
                $request->nombre,
                $request->descripcion,
                $request->idCategoria,
                $request->precioVenta,
                $request->idProveedor,
                $idUsuario,
                $request->stockInicial,
                $request->precioCompra,
                $request->fechaVencimiento

            ]);

            return redirect()->back()->with('success', 'Producto creado e ingresado al stock correctamente.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el producto: ' . $e->getMessage());
            
        }
    }

    public function listarProductos(Request $request)
    {
        try {
            $productos = DB::select('CALL SP_ListarProductos()');
            return view('inventario.index', compact('productos'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // 2. Renombramos a 'show' y pasamos el $codigo como string (el código de barras no siempre es un número entero)
    public function show(string $codigo)
    {
        try {
            // El signo de interrogación inyecta la variable de forma segura
            $resultado = DB::select('CALL SP_ObtenerProductoPorId(?)', [$codigo]);

            // Como DB::select devuelve un arreglo, extraemos el primer objeto (el producto)
            $producto = !empty($resultado) ? $resultado[0] : null;

            return view('inventario.show', compact('producto'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}