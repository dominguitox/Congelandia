<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PosController extends Controller
{
    public function index()
    {
        try {
            // Traes todos los productos disponibles
            $productos = DB::select('CALL SP_ListarProductos()');
            // Traes las categorías para los botones de filtro (Bebestibles, Carnes, etc.)
            $categorias = DB::select('CALL SP_ListarCategorias()');

            // Envías ambas variables a la vista del POS
            return view('pos.index', compact('productos', 'categorias'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el POS: ' . $e->getMessage());
        }


    }

    public function registrarVenta(Request $request)
    {
        $request->validate([
            'idTipo' => 'required|integer',
            'rutCliente' => 'nullable|string',
            'totalVenta' => 'required|numeric',
            'productos' => 'required|array',
        ]);
        try {
            DB::beginTransaction();
            $idUsuario = auth()->user()->id;
            DB::select('CALL SP_RegistrarVenta(?, ?, ?, ?, @p_idVenta)', [
                $request->idTipo,
                $idUsuario,
                $request->rutCliente,
                $request->totalVenta
            ]);

            $consulta = DB::select('SELECT @p_idVenta AS idVenta');
            $idVenta = $consulta[0]->idVenta;
            foreach ($request->productos as $producto) {
                DB::select('CALL SP_RegistrarDetalleVenta(?, ?, ?, ?)', [
                    $idVenta,
                    $producto['codigoProducto'],
                    $producto['productoCantidad'],
                    $producto['productoPrecioCobrado']
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Venta registrada e ingresada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar la venta: ' . $e->getMessage());
        }
    }


}