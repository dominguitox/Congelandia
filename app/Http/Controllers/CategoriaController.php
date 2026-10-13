<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    /**
     * Mostrar el módulo de categorías.
     */
    public function index()
    {
        $categorias = Categoria::with([
            'productos' => function ($query) {
                $query->whereNull('deleted_at')
                    ->orderBy('nombre');
            }
        ])
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->get();

        /*
         * Estadísticas.
         */
        $totalCategorias = Categoria::whereNull('deleted_at')
            ->count();

        /*
         * Cantidad de productos activos que tienen
         * una categoría asignada.
         */
        $productosConCategoria = DB::table('producto')
            ->join(
                'categoria',
                'producto.idCategoria',
                '=',
                'categoria.idCategoria'
            )
            ->whereNull('producto.deleted_at')
            ->whereNull('categoria.deleted_at')
            ->whereNotNull('producto.idCategoria')
            ->distinct()
            ->count('producto.codigo');

        /*
         * Cantidad de categorías que actualmente
         * tienen al menos un producto.
         */
        $categoriasEnUso = DB::table('producto')
            ->join(
                'categoria',
                'producto.idCategoria',
                '=',
                'categoria.idCategoria'
            )
            ->whereNull('producto.deleted_at')
            ->whereNull('categoria.deleted_at')
            ->whereNotNull('producto.idCategoria')
            ->distinct()
            ->count('producto.idCategoria');

        return view(
            'categorias.index',
            compact(
                'categorias',
                'totalCategorias',
                'productosConCategoria',
                'categoriasEnUso'
            )
        );
    }


    /**
     * Crear categoría.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100'
            ],
        ], [
            'nombre.required' =>
                'El nombre de la categoría es obligatorio.',

            'nombre.max' =>
                'El nombre no puede superar los 100 caracteres.',
        ]);

        Categoria::create([
            'nombre' => trim($request->nombre),
        ]);

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                'Categoría creada correctamente.'
            );
    }


    /**
     * Editar categoría.
     */
    public function update(
        Request $request,
        $idCategoria
    ) {
        $categoria = Categoria::whereNull('deleted_at')
            ->findOrFail($idCategoria);

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100'
            ],
        ], [
            'nombre.required' =>
                'El nombre de la categoría es obligatorio.',

            'nombre.max' =>
                'El nombre no puede superar los 100 caracteres.',
        ]);

        $categoria->update([
            'nombre' => trim($request->nombre),
        ]);

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                'Categoría actualizada correctamente.'
            );
    }


    /**
     * Eliminar categoría.
     *
     * No se permite eliminar una categoría que tenga
     * productos activos asociados.
     */
    public function destroy($idCategoria)
    {
        $categoria = Categoria::whereNull('deleted_at')
            ->findOrFail($idCategoria);

        /*
         * Comprobar si existen productos activos
         * asociados a esta categoría.
         */
        $tieneProductos = DB::table('producto')
            ->where('idCategoria', $idCategoria)
            ->whereNull('deleted_at')
            ->exists();

        if ($tieneProductos) {
            return redirect()
                ->route('categorias.index')
                ->with(
                    'error',
                    'No se puede eliminar la categoría porque tiene productos asociados.'
                );
        }

        /*
         * Soft delete.
         */
        $categoria->delete();

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                'Categoría eliminada correctamente.'
            );
    }
}