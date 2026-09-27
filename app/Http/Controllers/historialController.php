<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class historialController extends Controller
{
    public function index()
    {
        try {

            // Obtiene las ventas generales
            $salidas = DB::select('CALL SP_listarSalidas()');


            // Obtiene los productos de cada venta
            foreach ($salidas as $salida) {

                $salida->detalle = DB::select(
                    'CALL SP_DetalleSalida(?)',
                    [$salida->idSalida]
                );

            }

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
