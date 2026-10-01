<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use App\Models\DetalleSalida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class historialController extends Controller
{
    public function index()
    {
        try {
            // Obtiene las ventas generales

            $salidas = DetalleSalida::select(
                'Producto.nombre as producto',
                'Detalle_Salida.cantidad',
                'Detalle_Salida.precioCobrado as subtotal',
                'Detalle_Salida.precioCobrado as precioUnitario',
            )
                ->leftJoin('Producto', 'Detalle_Salida.codigoProducto', '=', 'Producto.nombre')
                ->addSelect([
                    'precio' => DB::table('Lista_Precio')
                        ->select('precioVenta')
                        ->whereColumn('codigoProducto', 'Producto.codigo')
                        ->whereNull('fechaFin')
                        ->limit(1)
                ])
                ->get();

               // dd($salidas);


            return view('historial.index', compact('salidas'));

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
