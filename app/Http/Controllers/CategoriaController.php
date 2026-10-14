<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::with('productos')->withCount('productos')->get();

        $estadisticas = [
            'total' => $categorias->count(),
            'categorias_uso' => $categorias->count() - $categorias->where('productos_count', '>', 0)->count(),
            'productos_categorizados' => Producto::whereNotNull('idCategoria')->count(),
            'productos_NoCategorizados' => Producto::whereNull('idCategoria')->count(),
        ];
        return view('categorias.index', compact('categorias', 'estadisticas'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        Categoria::create([
            'nombre' => $request->nombre
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $categoria = Categoria::findOrFail($id);
        $categoria->update([
            'nombre' => $request->nombre
        ]);

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        // Validación antes de borrar: No borrar si tiene productos asociados (Por Integridad)
        if ($categoria->productos()->count() > 0) {
            return redirect()->route('categorias.index')->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada correctamente.');
    }
}