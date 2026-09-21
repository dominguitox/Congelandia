<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProveedorController extends Controller
{
    public function listarProveedores(Request $request)
    {
        try {   
            $proveedores = DB::select('CALL SP_ListarProveedores()');
            return view('inventario.index', compact('proveedores'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'No se pudieron cargar los proveedores: ' . $e->getMessage());
        }
    }
}
