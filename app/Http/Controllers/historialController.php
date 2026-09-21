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
            // Trae solo salidas
            $salidas = DB::select('CALL SP_listarSalidas()');
            // salidas + entradas (historial)
        //    $salidas = DB::select('CALL SP_ListarHistorial()');


            // Envías ambas variables a la vista del POS
            return view('historial.index', compact('salidas'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el historial: ' . $e->getMessage());
        }
    }
}
