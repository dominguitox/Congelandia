<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Version;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    public function index()
    {
        $version = Version::where('activa', 1)->latest('id')->first();

        $productos = Producto::with(['imagenes', 'precioActual', 'categoria'])
            ->withMax(['promociones as mayor_descuento' => fn ($query) => $query->vigentes()], 'porcentajeDescuento')
            ->orderByDesc('mayor_descuento')
            ->orderBy('nombre')
            ->limit(6)
            ->get();

        $categorias = $this->categoriasConProductos();
        $totalProductos = $categorias->sum('productos_count');

        return view('public.index', compact(
            'productos',
            'categorias',
            'totalProductos',
            'version'
        ));
    }

    public function catalogo(Request $request)
    {
        $version = Version::where('activa', 1)->latest('id')->first();
        $categorias = $this->categoriasConProductos();
        $totalProductos = $categorias->sum('productos_count');

        $busqueda = trim((string) $request->query('q', ''));
        $categoriaId = $request->query('categoria');
        $orden = (string) $request->query('orden', 'nombre');

        $productos = Producto::with(['imagenes', 'precioActual', 'categoria'])
            ->withMax(['promociones as mayor_descuento' => fn ($query) => $query->vigentes()], 'porcentajeDescuento')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($query) use ($busqueda) {
                    $query->where('nombre', 'like', '%' . $busqueda . '%')
                        ->orWhere('descripcion', 'like', '%' . $busqueda . '%');
                });
            })
            ->when($categoriaId, fn ($query) => $query->where('idCategoria', $categoriaId))
            ->get();

        $productos = match ($orden) {
            'ofertas' => $productos->sortByDesc(fn ($producto) => $producto->mayor_descuento ?? -1),
            'precio-asc' => $productos->sortBy(fn ($producto) => $producto->precioActual?->precioVenta ?? PHP_INT_MAX),
            'precio-desc' => $productos->sortByDesc(fn ($producto) => $producto->precioActual?->precioVenta ?? -1),
            default => $productos->sortBy('nombre'),
        };

        $productos = $productos->values();

        $categoriaActiva = $categorias->firstWhere('idCategoria', (int) $categoriaId);
        $totalCategorias = $categorias->count();

        return view('public.catalogo', compact(
            'productos',
            'categorias',
            'categoriaActiva',
            'busqueda',
            'orden',
            'totalProductos',
            'totalCategorias',
            'version'
        ));
    }

    public function contacto()
    {
        $version = Version::where('activa', 1)->latest('id')->first();
        $totalProductos = Producto::count();

        return view('public.contacto', compact('version', 'totalProductos'));
    }

    /**
     * Categorias visibles en el sitio publico: solo las que
     * tienen productos y sin la categoria tecnica "Sin Categoria".
     */
    private function categoriasConProductos()
    {
        return Categoria::withCount('productos')
            ->orderBy('nombre')
            ->get()
            ->filter(fn ($categoria) => $categoria->productos_count > 0)
            ->reject(fn ($categoria) => Str::slug($categoria->nombre) === 'sin-categoria')
            ->values();
    }

    public function versionInfo()
    {
        $version = Version::where('activa', 1)->latest('id')->first();

        if (!$version) {
            return response('No hay datos de versión.', 200)->header('Content-Type', 'text/plain');
        }

        $fechaCreacion = Carbon::parse($version->fecha_creacion);
        $hoy = Carbon::now();

        $diasDiferencia = (int) $fechaCreacion->diffInDays($hoy);

        $texto = "APLICACIÓN: Congelandia\n";
        $texto .= "DESARROLLADORES: Domingo Herrera y Elías Pérez\n";
        $texto .= "VERSION: " . $version->numero . "\n";
        $texto .= "FECHA: " . ($version->fecha_creacion ?? 'No registrada') . " (" . $diasDiferencia . " días)\n";
        $texto .= "DETALLES: \n" . $version->comentarios . "\n";

        return response($texto, 200)->header('Content-Type', 'text/plain');
    }
}
