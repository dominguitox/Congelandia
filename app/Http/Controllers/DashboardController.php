<?php

namespace App\Http\Controllers;

use App\Models\ListaPrecio;
use App\Models\Salida;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Promocion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

use App\Models\Producto;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $salidasRecientes = Salida::with(['detalles', 'usuario'])
                ->orderByDesc('fecha')
                ->orderByDesc('idSalida')
                ->paginate(10, ['*'], 'pagina_ventas')
                ->withQueryString()
                ->fragment('ventas-recientes');


            // 1. Cálculos exclusivos para HOY
            $hoy = Carbon::now()->toDateString();

            // Filtramos las salidas de hoy desde la base de datos para los contadores
            $salidasHoy = Salida::with('detalles')->whereDate('fecha', $hoy)->get();

            $totalVentasHoy = $salidasHoy->sum('totalSalida');
            $ordenesHoy = $salidasHoy->count();

            $promocionesActivas = Promocion::vigentes()
                ->with('producto')
                ->orderBy('porcentajeDescuento', 'desc')
                ->get();
            $cantidadPromocionesActivas = $promocionesActivas->count();

            // Sumar la cantidad de todos los detalles de las ventas de hoy
            $productosVendidosHoy = $salidasHoy->sum(function ($salida) {
                return $salida->detalles->sum('cantidad');
            });

            $productos = Producto::conDetalle()->get();
            $alertasActivas = 0;
            $fechaHoyCarbon = Carbon::now()->startOfDay();
            $listaAlertas = [];

            foreach ($productos as $producto) {
                $stockBajo = $producto->stock < 10;

                $diasRestantes = 999; // Valor por defecto si no tiene fecha
                if ($producto->fechaVencimiento) {
                    $fechaVence = Carbon::parse($producto->fechaVencimiento)->startOfDay();
                    $diasRestantes = $fechaHoyCarbon->diffInDays($fechaVence, false);
                }

                if ($stockBajo || $diasRestantes < 10) {
                    $listaAlertas[] = ($producto);
                    $alertasActivas++;
                }
            }
            //dd($listaAlertas);

            // Descomentar para debug
            // dd($productos);
            // dd($salidasHoy);

            $proveedores = Proveedor::whereNull('deleted_at')->get();
            $categorias = Categoria::whereNull('deleted_at')->get();

            return view('dashboard.index', compact(
                'salidasHoy',
                'salidasRecientes',
                'totalVentasHoy',
                'proveedores',
                'categorias',
                'productos',
                'alertasActivas',
                'ordenesHoy',
                'productosVendidosHoy',
                'listaAlertas',
                'promocionesActivas',
                'cantidadPromocionesActivas',
            ));
        } catch (Exception $e) {
            report($e);
            abort(500, 'Error al cargar el dashboard.');
        }


    }
}
