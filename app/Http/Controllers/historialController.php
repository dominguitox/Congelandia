<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class historialController extends Controller
{
    /**
     * =========================================================
     * HISTORIAL GENERAL DE MOVIMIENTOS
     * =========================================================
     *
     * Une:
     * - Ingresos de productos
     * - Salidas de productos
     *
     * Los resultados se ordenan desde el movimiento
     * más reciente al más antiguo.
     */
    public function index(Request $request)
    {
        try {

            /*
             * =====================================================
             * INGRESOS
             * =====================================================
             */

            $ingresos = DB::table('ingreso')
                ->join(
                    'proveedor',
                    'ingreso.idProveedor',
                    '=',
                    'proveedor.idProveedor'
                )
                ->join(
                    'usuario',
                    'ingreso.idUsuario',
                    '=',
                    'usuario.idUsuario'
                )
                ->select(
                    'ingreso.idIngreso',
                    'ingreso.fecha',
                    'ingreso.idProveedor',
                    'ingreso.idUsuario',
                    'ingreso.totalCompra',
                    'proveedor.nombre as proveedor',
                    'usuario.nombre as usuario'
                )
                ->whereNull('ingreso.deleted_at')
                ->orderBy('ingreso.fecha', 'desc')
                ->get();

            /*
             * Obtener los detalles de los ingresos.
             */
            $detallesIngresos = DB::table('detalle_ingreso')
                ->join(
                    'producto',
                    'detalle_ingreso.codigoProducto',
                    '=',
                    'producto.codigo'
                )
                ->select(
                    'detalle_ingreso.idIngreso',
                    'detalle_ingreso.codigoProducto',
                    'detalle_ingreso.cantidad',
                    'detalle_ingreso.precioCompra',
                    'detalle_ingreso.fechaVencimiento',
                    'producto.nombre as producto'
                )
                ->whereNull('detalle_ingreso.deleted_at')
                ->get()
                ->groupBy('idIngreso');


            /*
             * =====================================================
             * SALIDAS
             * =====================================================
             */

            $salidas = DB::table('salida')
                ->join(
                    'tipo_salida',
                    'salida.idTipo',
                    '=',
                    'tipo_salida.idTipo'
                )
                ->join(
                    'usuario',
                    'salida.idUsuario',
                    '=',
                    'usuario.idUsuario'
                )
                ->leftJoin(
                    'cliente',
                    'salida.rutCliente',
                    '=',
                    'cliente.rutCliente'
                )
                ->select(
                    'salida.idSalida',
                    'salida.fecha',
                    'salida.idTipo',
                    'salida.idUsuario',
                    'salida.rutCliente',
                    'salida.totalSalida',
                    'tipo_salida.nombre as tipoSalida',
                    'usuario.nombre as usuario'
                )
                ->whereNull('salida.deleted_at')
                ->orderBy('salida.fecha', 'desc')
                ->get();

            /*
             * Obtener los detalles de las salidas.
             */
            $detallesSalidas = DB::table('detalle_salida')
                ->join(
                    'producto',
                    'detalle_salida.codigoProducto',
                    '=',
                    'producto.codigo'
                )
                ->select(
                    'detalle_salida.idSalida',
                    'detalle_salida.codigoProducto',
                    'detalle_salida.cantidad',
                    'detalle_salida.precioCobrado',
                    'producto.nombre as producto'
                )
                ->whereNull('detalle_salida.deleted_at')
                ->get()
                ->groupBy('idSalida');


            /*
             * =====================================================
             * CONSTRUIR MOVIMIENTOS
             * =====================================================
             */

            $movimientos = collect();


            /*
             * Agregar ingresos.
             */
            foreach ($ingresos as $ingreso) {

                $detalles = $detallesIngresos
                    ->get($ingreso->idIngreso, collect());

                $movimientos->push([
                    'id' => $ingreso->idIngreso,
                    'fecha' => $ingreso->fecha,
                    'tipo' => 'Ingreso',
                    'tipo_clase' => 'ingreso',
                    'referencia' => 'Ingreso #' . $ingreso->idIngreso,
                    'usuario' => $ingreso->usuario ?? 'Sin usuario',
                    'proveedor' => $ingreso->proveedor ?? 'Sin proveedor',
                    'cantidad' => $detalles->sum('cantidad'),
                    'total' => $ingreso->totalCompra,
                    'detalles' => $detalles->map(function ($detalle) {

                        return [
                            'producto' => $detalle->producto ?? 'Producto desconocido',
                            'codigo' => $detalle->codigoProducto,
                            'cantidad' => $detalle->cantidad,
                            'precio' => $detalle->precioCompra,
                            'vencimiento' => $detalle->fechaVencimiento,
                        ];

                    })->values()->all(),
                ]);
            }


            /*
             * Agregar salidas.
             */
            foreach ($salidas as $salida) {

                $detalles = $detallesSalidas
                    ->get($salida->idSalida, collect());

                $movimientos->push([
                    'id' => $salida->idSalida,
                    'fecha' => $salida->fecha,
                    'tipo' => $salida->tipoSalida,
                    'tipo_clase' => 'salida',
                    'referencia' => 'Salida #' . $salida->idSalida,
                    'usuario' => $salida->usuario ?? 'Sin usuario',
                    'proveedor' => null,
                    'cantidad' => $detalles->sum('cantidad'),
                    'total' => $salida->totalSalida ?? 0,
                    'detalles' => $detalles->map(function ($detalle) {

                        return [
                            'producto' => $detalle->producto ?? 'Producto desconocido',
                            'codigo' => $detalle->codigoProducto,
                            'cantidad' => $detalle->cantidad,
                            'precio' => $detalle->precioCobrado,
                            'vencimiento' => null,
                        ];

                    })->values()->all(),
                ]);
            }


            /*
             * Ordenar todos los movimientos por fecha.
             */
            $movimientos = $movimientos
                ->sortByDesc('fecha')
                ->values();


            /*
             * Obtener los tipos de salida disponibles.
             *
             * Esto permite que si posteriormente agregan
             * Robo, Pérdida, Vencimiento, etc., aparezcan
             * automáticamente en el filtro.
             */
            $tiposSalida = DB::table('tipo_salida')
                ->whereNull('deleted_at')
                ->orderBy('nombre')
                ->pluck('nombre');


            /*
             * Estadísticas.
             */
            $totalMovimientos = $movimientos->count();

            $totalIngresos = $movimientos
                ->where('tipo_clase', 'ingreso')
                ->count();

            $totalSalidas = $movimientos
                ->where('tipo_clase', 'salida')
                ->count();


            return view(
                'historial.index',
                compact(
                    'movimientos',
                    'tiposSalida',
                    'totalMovimientos',
                    'totalIngresos',
                    'totalSalidas'
                )
            );

        } catch (Exception $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Error al cargar el historial: ' . $e->getMessage()
                );
        }
    }
}