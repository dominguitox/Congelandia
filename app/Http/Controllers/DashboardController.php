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
            $salidas = Salida::with('detalles.producto')->get();
            $salidasRecientes = Salida::with('detalles.producto')
                ->orderBy('fecha', 'desc')
                ->get();


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

            $productos = Producto::select(
                'producto.codigo',
                'producto.nombre',
                'producto.idCategoria',
                'producto.descripcion',
                'categoria.nombre as categoria'
            )
                ->leftJoin('categoria', 'producto.idCategoria', '=', 'categoria.idCategoria')
                ->addSelect([
                    'precio' => DB::table('lista_precio')
                        ->select('precioVenta')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->whereNull('fechaFin')
                        ->whereNull('deleted_at')
                        ->limit(1)
                ])
                ->addSelect([
                    'costo' => DB::table('detalle_ingreso')
                        ->select('detalle_ingreso.precioCompra')
                        ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
                        ->whereColumn('detalle_ingreso.codigoProducto', 'producto.codigo')
                        ->whereNull('detalle_ingreso.deleted_at')
                        ->whereNull('ingreso.deleted_at')
                        ->orderByDesc('ingreso.fecha')
                        ->limit(1)
                ])
                ->addSelect([
                    'proveedor' => DB::table('detalle_ingreso')
                        ->select('proveedor.nombre')
                        ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
                        ->join('proveedor', 'ingreso.idProveedor', '=', 'proveedor.idProveedor')
                        ->whereColumn('detalle_ingreso.codigoProducto', 'producto.codigo')
                        ->whereNull('detalle_ingreso.deleted_at')
                        ->whereNull('ingreso.deleted_at')
                        ->whereNull('proveedor.deleted_at')
                        ->orderByDesc('ingreso.fecha')
                        ->limit(1)
                ])
                ->addSelect([
                    'precioVenta' => ListaPrecio::select('precioVenta')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->orderByDesc('fechaInicio')
                        ->limit(1)
                ])
                ->addSelect([
                    'fechaVencimiento' => DB::table('detalle_ingreso')
                        ->select('detalle_ingreso.fechaVencimiento')
                        ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
                        ->whereColumn('detalle_ingreso.codigoProducto', 'producto.codigo')
                        ->whereNull('detalle_ingreso.deleted_at')
                        ->whereNull('ingreso.deleted_at')
                        ->orderByDesc('ingreso.fecha')
                        ->limit(1)
                ])
                ->selectRaw('(IFNULL((SELECT SUM(di.cantidad) FROM detalle_ingreso di INNER JOIN ingreso i ON di.idIngreso = i.idIngreso WHERE di.codigoProducto = producto.codigo AND di.deleted_at IS NULL AND i.deleted_at IS NULL), 0) - IFNULL((SELECT SUM(ds.cantidad) FROM detalle_salida ds INNER JOIN salida s ON ds.idSalida = s.idSalida WHERE ds.codigoProducto = producto.codigo AND ds.deleted_at IS NULL AND s.deleted_at IS NULL), 0)) AS stock')
                ->get();
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
                'salidas',
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
