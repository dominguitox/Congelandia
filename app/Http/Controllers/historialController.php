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
            $salidas = Salida::with('detalles.producto')->get();

            return view('historial.index', compact('salidas'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el historial: ' . $e->getMessage());
        }
    }
}
