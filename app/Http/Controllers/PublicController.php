<?php
namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\ListaPrecio;
use App\Models\Version;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{

    public function index()
    {
        $version = Version::where('activa', 1)->latest('id')->first();
        $productos = Producto::with(['imagenes', 'precioActual'])->get();
        return view('public.index', compact('productos', 'version'));

    }

    public function catalogo()
    {
        $version = Version::where('activa', 1)->latest('id')->first(); // Faltaba esto
        $categorias = Categoria::all();
        $productos = Producto::with('imagenes')
            ->addSelect([
                'precio' => ListaPrecio::select('precioVenta')
                    ->whereColumn('codigoProducto', 'producto.codigo')
                    ->orderByDesc('fechaInicio')
                    ->limit(1)
            ])
            ->get();

        return view('public.catalogo', compact('productos', 'categorias', 'version')); // Agrega 'version'
    }
    public function versionInfo()
    {
        $version = Version::where('activa', 1)->latest('id')->first();

        if (!$version) {
            return response("No hay datos de versión.", 200)->header('Content-Type', 'text/plain');
        }

        $fechaCreacion = Carbon::parse($version->fecha_creacion);
        $hoy = Carbon::now();

        // El (int) fuerza a que sea un número entero sin decimales
        $diasDiferencia = (int) $fechaCreacion->diffInDays($hoy);

        $texto = "APLICACIÓN: Congelandia\n";
        $texto .= "DESARROLLADORES: Domingo Herrera y Elías Pérez\n";
        $texto .= "VERSION: " . $version->numero . "\n";
        $texto .= "FECHA: " . ($version->fecha_creacion ?? 'No registrada') .  " (".$diasDiferencia . " días)\n";
        $texto .= "DETALLES: \n" . $version->comentarios . "\n";

        return response($texto, 200)->header('Content-Type', 'text/plain');
    }
}