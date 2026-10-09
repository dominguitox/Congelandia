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

use App\Models\Producto;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $salidas = Salida::with('detalles.producto')->get();
<<<<<<< Updated upstream
            $totalVentasHoy = $salidas->sum('totalSalida');
            //  dd($salidas);
=======

            // 1. Cálculos exclusivos para HOY
            $hoy = Carbon::now()->toDateString();

            // Filtramos las salidas de hoy desde la base de datos para los contadores
            $salidasHoy = Salida::with('detalles')->whereDate('fecha', $hoy)->get();

            $totalVentasHoy = $salidasHoy->sum('totalSalida');
            $ordenesHoy = $salidasHoy->count();

            $promocionesActivas = Promocion::whereDate('fechaInicio', '<=', $hoy)
                ->whereDate('fechaFin', '>=', $hoy)
                ->orderBy('porcentajeDescuento', 'desc')
                ->get();
            $cantidadPromocionesActivas = $promocionesActivas->count();

            // Sumar la cantidad de todos los detalles de las ventas de hoy
            $productosVendidosHoy = $salidasHoy->sum(function ($salida) {
                return $salida->detalles->sum('cantidad');
            });
>>>>>>> Stashed changes

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
                        ->limit(1)
                ])
                ->addSelect([
                    'costo' => DB::table('Detalle_Ingreso')
                        ->select('Detalle_Ingreso.precioCompra')
                        ->join('Ingreso', 'Detalle_Ingreso.idIngreso', '=', 'Ingreso.idIngreso')
                        ->whereColumn('Detalle_Ingreso.codigoProducto', 'Producto.codigo')
                        ->orderByDesc('Ingreso.fecha')
                        ->limit(1)
                ])
                ->addSelect([
                    'proveedor' => DB::table('Detalle_Ingreso')
                        ->select('Proveedor.nombre')
                        ->join('Ingreso', 'Detalle_Ingreso.idIngreso', '=', 'Ingreso.idIngreso')
                        ->join('Proveedor', 'Ingreso.idProveedor', '=', 'Proveedor.idProveedor')
                        ->whereColumn('Detalle_Ingreso.codigoProducto', 'Producto.codigo')
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
                        ->orderByDesc('Ingreso.fecha')
                        ->limit(1)
                ])
                ->selectRaw('(IFNULL((SELECT SUM(cantidad) FROM Detalle_Ingreso WHERE codigoProducto = Producto.codigo), 0) - IFNULL((SELECT SUM(cantidad) FROM Detalle_Salida WHERE codigoProducto = Producto.codigo), 0)) AS stock')
                ->get();
<<<<<<< Updated upstream
=======
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

>>>>>>> Stashed changes
            // Descomentar para debug
            // dd($productos);
            $proveedores = Proveedor::whereNull('deleted_at')->get();
            $categorias = Categoria::whereNull('deleted_at')->get();

<<<<<<< Updated upstream
            return view('dashboard.index', compact('salidas', 'totalVentasHoy', 'proveedores', 'categorias', 'productos'));
=======
            return view('dashboard.index', compact(
                'salidas',
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
>>>>>>> Stashed changes
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el historial: ' . $e->getMessage());
        }


    }
}