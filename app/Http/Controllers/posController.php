<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\Cliente;
use App\Models\Producto;



class PosController extends Controller
{
    public function index()
    {
        try {   
            // Traes todos los productos disponibles
            $productos = Producto::select(
                'Producto.codigo',
                'Producto.nombre',
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
                ->selectRaw('(IFNULL((SELECT SUM(cantidad) FROM Detalle_Ingreso WHERE codigoProducto = Producto.codigo), 0) - IFNULL((SELECT SUM(cantidad) FROM Detalle_Salida WHERE codigoProducto = Producto.codigo), 0)) AS stock')
                ->get();

            // activar para debug
            //   dd($productos);

            $categorias = Categoria::whereNull('deleted_at')->get();
            $clientes = Cliente::whereNull('deleted_at')->get();

            return view('pos.index', compact('productos', 'categorias', 'clientes'));

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
            if (!auth()->check()) {
                return response()->json(['success' => false, 'error' => 'Usuario no autenticado.'], 401);
            }
            $idUsuario = auth()->id();

            DB::select('CALL SP_RegistrarVenta(?, ?, ?, ?, @p_idVenta, @p_resultado, @p_mensaje)', [
                $request->idTipo,
                $idUsuario,
                $request->rutCliente,
                $request->totalVenta,
            ]);

            $consulta = DB::select('SELECT @p_idVenta AS idVenta, @p_resultado AS resultado, @p_mensaje AS mensaje');
            $idSalida = $consulta[0]->idVenta;
            foreach ($request->productos as $producto) {
                DB::table('Detalle_Salida')->insert([
                    'idSalida' => $idSalida,
                    'codigoProducto' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precioCobrado' => $producto['precio']
                ]);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Venta registrada e ingresada correctamente.'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            // En caso de error, success es false y se añade el código de estado HTTP 500
            return response()->json([
                'success' => false,
                'error' => 'Error al registrar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

}