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

        $tipoReporte = $request->input('tipo_reporte', 'crudo');

        switch ($tipoReporte) {
            case 'mas_vendidos':
                $datos = $this->productosMasVendidos($fechaInicio, $fechaFin);
                break;
            case 'ingresos':
                $datos = $this->totalIngresosPorFecha($fechaInicio, $fechaFin);
                break;
            case 'cantidad_ventas':
                $datos = $this->cantidadVentasEfectuadasPorFecha($fechaInicio, $fechaFin);
                break;
            case 'total_articulos':
                $datos = $this->cantidadArticulosVendidosPorFecha($fechaInicio, $fechaFin);
                break;
            case 'ticket_promedio':
                $datos = $this->ticketPromedio($fechaInicio, $fechaFin);
                break;
            case 'ventas_categoria':
                $datos = $this->ventasPorCategoria($fechaInicio, $fechaFin);
                break;
            case 'ventas_dia':
                $datos = $this->ventasPorDiaDeLaSemana($fechaInicio, $fechaFin);
                break;
            case 'hora_pico':
                $datos = $this->horariosConMasTrafico($fechaInicio, $fechaFin);
                break;
            case 'bajo_stock':
                $datos = $this->productosConBajoStock();
                break;
            case 'proximos_vencer':
                $datos = $this->productosProximosAVencer();
                break;
            default:
                $datos = $this->reporteCrudo($fechaInicio, $fechaFin);
                break;
        }

        return view('reportes.index', compact('datos', 'fechaInicio', 'fechaFin', 'tipoReporte'));
    }

    private function reporteCrudo($fechaInicio, $fechaFin)
    {
        return DB::table('detalle_salida')
            ->join('salida', 'detalle_salida.idSalida', '=', 'salida.idSalida')
            ->join('producto', 'detalle_salida.codigoProducto', '=', 'producto.codigo')
            ->whereNull('detalle_salida.deleted_at')
            ->whereNull('salida.deleted_at')
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
    }

    // 1. Productos más vendidos
    private function productosMasVendidos($fechaInicio, $fechaFin)
    {
        return DB::table('detalle_salida')
            ->join('salida', 'detalle_salida.idSalida', '=', 'salida.idSalida')
            ->join('producto', 'detalle_salida.codigoProducto', '=', 'producto.codigo')
            ->whereNull('detalle_salida.deleted_at')
            ->whereNull('salida.deleted_at')
            ->select(
                'producto.nombre',
                DB::raw('SUM(detalle_salida.cantidad) as total_unidades'),
                DB::raw('SUM(detalle_salida.cantidad * detalle_salida.precioCobrado) as dinero_generado')
            )
            ->whereBetween(DB::raw('DATE(salida.fecha)'), [$fechaInicio, $fechaFin])
            ->groupBy('producto.codigo', 'producto.nombre')
            ->orderBy('total_unidades', 'DESC')
            ->get();
    }

    // 2. Total de ingresos recaudados
    private function totalIngresosPorFecha($fechaInicio, $fechaFin)
    {
        return DB::table('detalle_salida')
            ->join('salida', 'detalle_salida.idSalida', '=', 'salida.idSalida')
            ->whereNull('detalle_salida.deleted_at')
            ->whereNull('salida.deleted_at')
            ->whereBetween(DB::raw('DATE(salida.fecha)'), [$fechaInicio, $fechaFin])
            ->sum(DB::raw('detalle_salida.cantidad * detalle_salida.precioCobrado'));


    }

    // 3. Cantidad de ventas efectuadas
    private function cantidadVentasEfectuadasPorFecha($fechaInicio, $fechaFin)
    {
        return DB::table('salida')
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(fecha)'), [$fechaInicio, $fechaFin])
            ->count('idSalida');
    }
    // 4. Total de artículos comercializados
    private function cantidadArticulosVendidosPorFecha($fechaInicio, $fechaFin)
    {
        return DB::table('detalle_salida')
            ->join('salida', 'detalle_salida.idSalida', '=', 'salida.idSalida')
            ->whereNull('detalle_salida.deleted_at')
            ->whereNull('salida.deleted_at')
            ->whereBetween(DB::raw('DATE(salida.fecha)'), [$fechaInicio, $fechaFin])
            ->sum('detalle_salida.cantidad');
    }

    // 5. Ticket promedio
    private function ticketPromedio($fechaInicio, $fechaFin)
    {
        $ingresos = $this->totalIngresosPorFecha($fechaInicio, $fechaFin);
        $ventas = $this->cantidadVentasEfectuadasPorFecha($fechaInicio, $fechaFin);

        return $ventas > 0 ? ($ingresos / $ventas) : 0;
    }
    // 6. Ventas por categoría
    private function ventasPorCategoria($fechaInicio, $fechaFin)
    {
        return DB::table('detalle_salida')
            ->join('salida', 'detalle_salida.idSalida', '=', 'salida.idSalida')
            ->join('producto', 'detalle_salida.codigoProducto', '=', 'producto.codigo')
            ->join('categoria', 'producto.idCategoria', '=', 'categoria.idCategoria')
            ->whereNull('detalle_salida.deleted_at')
            ->whereNull('salida.deleted_at')
            ->select(
                'categoria.nombre as categoria',
                DB::raw('SUM(detalle_salida.cantidad) as total_unidades'),
                DB::raw('SUM(detalle_salida.cantidad * detalle_salida.precioCobrado) as ingresos')
            )
            ->whereBetween(DB::raw('DATE(salida.fecha)'), [$fechaInicio, $fechaFin])
            ->groupBy('categoria.idCategoria', 'categoria.nombre')
            ->orderBy('ingresos', 'DESC')
            ->get();
    }

    // 7. Ventas por día de la semana
    private function ventasPorDiaDeLaSemana($fechaInicio, $fechaFin)
    {
        return DB::table('detalle_salida')
            ->join('salida', 'detalle_salida.idSalida', '=', 'salida.idSalida')
            ->whereNull('detalle_salida.deleted_at')
            ->whereNull('salida.deleted_at')
            ->select(
                DB::raw('DAYNAME(salida.fecha) as dia'),
                DB::raw('SUM(detalle_salida.cantidad * detalle_salida.precioCobrado) as ingresos')
            )
            ->whereBetween(DB::raw('DATE(salida.fecha)'), [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw('DAYNAME(salida.fecha)'), DB::raw('DAYOFWEEK(salida.fecha)'))
            ->orderBy(DB::raw('DAYOFWEEK(salida.fecha)'))
            ->get();
    }

    // 8. Horarios con más tráfico (Hora pico)
    private function horariosConMasTrafico($fechaInicio, $fechaFin)
    {
        return DB::table('salida')
            ->whereNull('deleted_at')
            ->select(
                DB::raw('HOUR(fecha) as hora'),
                DB::raw('COUNT(idSalida) as cantidad_transacciones')
            )
            ->whereBetween(DB::raw('DATE(fecha)'), [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw('HOUR(fecha)'))
            ->orderBy('cantidad_transacciones', 'DESC')
            ->get();
    }

    // 9. Productos con bajo stock
    private function productosConBajoStock()
    {
        $stock = $this->stockProductoSubquery();

        return DB::table('producto')
            ->whereNull('producto.deleted_at')
            ->select(
                'producto.nombre',
                DB::raw($stock . ' as stock_actual')
            )
            ->whereRaw($stock . ' < ?', [10])
            ->orderBy('stock_actual', 'ASC')
            ->get();
    }

    // 10. Productos próximos a vencer
    private function productosProximosAVencer()
    {
        $stock = $this->stockProductoSubquery();

        return DB::table('detalle_ingreso')
            ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
            ->join('producto', 'detalle_ingreso.codigoProducto', '=', 'producto.codigo')
            ->whereNull('detalle_ingreso.deleted_at')
            ->whereNull('ingreso.deleted_at')
            ->whereNull('producto.deleted_at')
            ->whereBetween('detalle_ingreso.fechaVencimiento', [
                now()->toDateString(),
                now()->addDays(30)->toDateString()
            ])
            ->select(
                'producto.nombre',
                'detalle_ingreso.fechaVencimiento as fecha_vencimiento',
                DB::raw($stock . ' as cantidad_disponible')
            )
            ->groupBy('producto.codigo', 'producto.nombre', 'detalle_ingreso.fechaVencimiento')
            ->havingRaw($stock . ' > 0')
            ->orderBy('detalle_ingreso.fechaVencimiento', 'ASC')
            ->get();
    }

    private function stockProductoSubquery(): string
    {
        return '(IFNULL((
                    SELECT SUM(di.cantidad)
                    FROM detalle_ingreso di
                    INNER JOIN ingreso i ON di.idIngreso = i.idIngreso
                    WHERE di.codigoProducto = producto.codigo
                        AND di.deleted_at IS NULL
                        AND i.deleted_at IS NULL
                ), 0) - IFNULL((
                    SELECT SUM(ds.cantidad)
                    FROM detalle_salida ds
                    INNER JOIN salida s ON ds.idSalida = s.idSalida
                    WHERE ds.codigoProducto = producto.codigo
                        AND ds.deleted_at IS NULL
                        AND s.deleted_at IS NULL
                ), 0))';
    }
}

/*  POR AGREGAR:
1 Productos más vendidos: Listado de los productos con mayor número de unidades comercializadas y el dinero generado por estos.   
2 Total de ingresos recaudados: Monto total de dinero generado por las ventas en el intervalo de fechas seleccionado.   
3 Cantidad de ventas efectuadas: Número total de transacciones o tickets emitidos durante el periodo.   
4 Total de artículos comercializados: Sumatoria total de las unidades físicas vendidas.   
5 Ticket promedio: Monto promedio de dinero gastado por transacción.   
6 Ventas por categoría: Ingresos y unidades vendidas desglosadas por agrupaciones como Bebidas, Panadería, Lácteos, etc.   
7 Ventas por día de la semana: Ingresos generados segmentados por cada día para detectar tendencias de consumo.   
8 Horarios con más tráfico (Hora pico): Estadísticas de los tramos horarios donde se concentra la mayor cantidad de ventas.   
9 Productos con bajo stock: Listado de artículos que alcanzaron el límite mínimo y requieren reposición.   
10 Productos próximos a vencer: Alertas sobre los artículos que se acercan a su fecha de caducidad.   
-------
11 Cantidad de clientes con deuda: Número de clientes de confianza que tienen saldos pendientes por pagar.   
12 Deuda total pendiente: Sumatoria del dinero adeudado por los clientes registrados.   
13 Ganancia estimada: Cálculo del margen de ganancia obtenido al cruzar el precio de venta con el costo del producto.   

-------
14 Mostrar productos mas vendidos por festividad predefinida (navidad, 18 de septiembre)
*/