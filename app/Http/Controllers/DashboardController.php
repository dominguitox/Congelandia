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
                'Producto.codigo',
                'Producto.nombre',
                'Producto.idCategoria',
                'Producto.descripcion',
                'Categoria.nombre as categoria'
            )
                ->leftJoin('Categoria', 'Producto.idCategoria', '=', 'Categoria.idCategoria')
                ->addSelect([
                    'precio' => DB::table('Lista_Precio')
                        ->select('precioVenta')
                        ->whereColumn('codigoProducto', 'Producto.codigo')
                        ->whereNull('fechaFin')
                        ->whereNull('deleted_at')
                        ->limit(1)
                ])
                ->addSelect([
                    'costo' => DB::table('Detalle_Ingreso')
                        ->select('Detalle_Ingreso.precioCompra')
                        ->join('Ingreso', 'Detalle_Ingreso.idIngreso', '=', 'Ingreso.idIngreso')
                        ->whereColumn('Detalle_Ingreso.codigoProducto', 'Producto.codigo')
                        ->whereNull('Detalle_Ingreso.deleted_at')
                        ->whereNull('Ingreso.deleted_at')
                        ->orderByDesc('Ingreso.fecha')
                        ->limit(1)
                ])
                ->addSelect([
                    'proveedor' => DB::table('Detalle_Ingreso')
                        ->select('Proveedor.nombre')
                        ->join('Ingreso', 'Detalle_Ingreso.idIngreso', '=', 'Ingreso.idIngreso')
                        ->join('Proveedor', 'Ingreso.idProveedor', '=', 'Proveedor.idProveedor')
                        ->whereColumn('Detalle_Ingreso.codigoProducto', 'Producto.codigo')
                        ->whereNull('Detalle_Ingreso.deleted_at')
                        ->whereNull('Ingreso.deleted_at')
                        ->whereNull('Proveedor.deleted_at')
                        ->orderByDesc('Ingreso.fecha')
                        ->limit(1)
                ])
                ->addSelect([
                    'precioVenta' => ListaPrecio::select('precioVenta')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->orderByDesc('fechaInicio')
                        ->limit(1)
                ])
                ->addSelect([
                    'fechaVencimiento' => DB::table('Detalle_Ingreso')
                        ->select('Detalle_Ingreso.fechaVencimiento')
                        ->join('Ingreso', 'Detalle_Ingreso.idIngreso', '=', 'Ingreso.idIngreso')
                        ->whereColumn('Detalle_Ingreso.codigoProducto', 'Producto.codigo')
                        ->whereNull('Detalle_Ingreso.deleted_at')
                        ->whereNull('Ingreso.deleted_at')
                        ->orderByDesc('Ingreso.fecha')
                        ->limit(1)
                ])
                ->selectRaw('(IFNULL((SELECT SUM(di.cantidad) FROM Detalle_Ingreso di INNER JOIN Ingreso i ON di.idIngreso = i.idIngreso WHERE di.codigoProducto = Producto.codigo AND di.deleted_at IS NULL AND i.deleted_at IS NULL), 0) - IFNULL((SELECT SUM(ds.cantidad) FROM Detalle_Salida ds INNER JOIN Salida s ON ds.idSalida = s.idSalida WHERE ds.codigoProducto = Producto.codigo AND ds.deleted_at IS NULL AND s.deleted_at IS NULL), 0)) AS stock')
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
