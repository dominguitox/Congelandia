<?php
// app/Http/Controllers/ReporteController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        $datos = DB::table('detalle_salida')
            ->join('salida', 'detalle_salida.idSalida', '=', 'salida.idSalida')
            ->join('producto', 'detalle_salida.codigoProducto', '=', 'producto.codigo')
            ->select(
                DB::raw('DATE(salida.fecha) as fecha'),
                'producto.nombre',
                DB::raw('SUM(detalle_salida.cantidad) as total_unidades')
            )
            ->whereBetween(DB::raw('DATE(salida.fecha)'), [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw('DATE(salida.fecha)'), 'producto.nombre')
            ->orderBy('fecha', 'DESC')
            ->orderBy('total_unidades', 'DESC')
            ->get();

        return view('reportes.index', compact('datos', 'fechaInicio', 'fechaFin'));
    }
    /*  POR AGREGAR:
    Productos más vendidos: Listado de los productos con mayor número de unidades comercializadas y el dinero generado por estos.   

    Total de ingresos recaudados: Monto total de dinero generado por las ventas en el intervalo de fechas seleccionado.   

    Cantidad de ventas efectuadas: Número total de transacciones o tickets emitidos durante el periodo.   

    Total de artículos comercializados: Sumatoria total de las unidades físicas vendidas.   

    Ticket promedio: Monto promedio de dinero gastado por transacción.   

    Ventas por categoría: Ingresos y unidades vendidas desglosadas por agrupaciones como Bebidas, Panadería, Lácteos, etc.   

    Ventas por día de la semana: Ingresos generados segmentados por cada día para detectar tendencias de consumo.   

    Horarios con más tráfico (Hora pico): Estadísticas de los tramos horarios donde se concentra la mayor cantidad de ventas.   

    Productos con bajo stock: Listado de artículos que alcanzaron el límite mínimo y requieren reposición.   

    Productos próximos a vencer: Alertas sobre los artículos que se acercan a su fecha de caducidad.   

    Cantidad de clientes con deuda: Número de clientes de confianza que tienen saldos pendientes por pagar.   

    Deuda total pendiente: Sumatoria del dinero adeudado por los clientes registrados.   

    Ganancia estimada: Cálculo del margen de ganancia obtenido al cruzar el precio de venta con el costo del producto.   
    */
}