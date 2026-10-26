<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Version;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        $version = Version::where('activa', 1)->latest('id')->first();

        $productos = Producto::with(['imagenes', 'precioActual', 'categoria'])
            ->withMax(['promociones as mayor_descuento' => fn ($query) => $query->vigentes()], 'porcentajeDescuento')
            ->whereHas('promociones', fn ($query) => $query->vigentes())
            ->orderByDesc('mayor_descuento')
            ->orderBy('nombre')
            ->limit(6)
            ->get();

        return view('public.index', compact('productos', 'version'));
    }

    public function catalogo(Request $request)
    {
        $version = Version::where('activa', 1)->latest('id')->first();
        $categorias = Categoria::orderBy('nombre')->get();

        $busqueda = trim((string) $request->query('q', ''));
        $categoriaId = $request->query('categoria');

        $productos = Producto::with(['imagenes', 'precioActual', 'categoria'])
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($query) use ($busqueda) {
                    $query->where('nombre', 'like', '%' . $busqueda . '%')
                        ->orWhere('descripcion', 'like', '%' . $busqueda . '%');
                });
            })
            ->when($categoriaId, fn ($query) => $query->where('idCategoria', $categoriaId))
            ->orderBy('nombre')
            ->get();

        $categoriaActiva = $categorias->firstWhere('idCategoria', (int) $categoriaId);

        return view('public.catalogo', compact(
            'productos',
            'categorias',
            'categoriaActiva',
            'busqueda',
            'version'
        ));
    }

    public function contacto()
    {
        $version = Version::where('activa', 1)->latest('id')->first();

        return view('public.contacto', compact('version'));
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
