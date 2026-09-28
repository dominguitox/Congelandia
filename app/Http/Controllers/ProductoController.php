<?php
//El controlador de la tabla Producto. 
// Tiene los metodos: 
// Crear, 
namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ListaPrecio;
use App\Models\Ingreso;
use App\Models\DetalleIngreso;

use Illuminate\Http\Request;
use Exception;

use Illuminate\Support\Facades\DB;


class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::whereNull('deleted_at')->get();

    }
    public function crearProducto(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'idCategoria' => 'required|integer',
            'precioVenta' => 'required|numeric|min:0',
            'idProveedor' => 'required|integer',
            'stockInicial' => 'required|integer|min:0',
            'precioCompra' => 'required|numeric|min:0',
            'fechaVencimiento' => 'required|date'
        ]);

        try {
            DB::transaction(function () use ($request) {

                $idUsuario = auth()->user()->idUsuario;
                Producto::create([
                    'codigo' => $request->codigo,
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'idCategoria' => $request->idCategoria,
                ]);
                ListaPrecio::create([
                    'codigoProducto' => $request->codigo,
                    'precioVenta' => $request->precioVenta,
                    'fechaInicio' => now(),
                ]);
                if ($request->stockInicial > 0) {
                    $totalCompra = $request->precioCompra * $request->stockInicial;
                    $ingreso = Ingreso::create([
                        'fecha' => now(),
                        'idProveedor' => $request->idProveedor,
                        'idUsuario' => $idUsuario,
                        'totalCompra' => $totalCompra,
                    ]);
                    DetalleIngreso::create([
                        'idIngreso' => $ingreso->idIngreso,
                        'codigoProducto' => $request->codigo,
                        'cantidad' => $request->stockInicial,
                        'precioCompra' => $request->precioCompra,
                        'fechaVencimiento' => $request->fechaVencimiento
                    ]);
                }

            });
            return redirect()->back()->with('success', 'Producto creado e ingresado al stock correctamente.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }

    }
    public function listarProductos(Request $request)
    {
        try {
            $productos = DB::select('CALL SP_ListarProductos()');
            $proveedores = DB::select('CALL SP_ListarProveedores()');
            $categorias = DB::select('CALL SP_ListarCategorias()');

            return view('inventario.index', compact('productos', 'proveedores', 'categorias'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


    // 2. Renombramos a 'show' y pasamos el $codigo como string (el código de barras no siempre es un número entero)
    public function show(string $codigo)
    {
        try {
            // El signo de interrogación inyecta la variable de forma segura
            $resultado = DB::select('CALL SP_ObtenerProductoPorId(?)', [$codigo]);

            // Como DB::select devuelve un arreglo, extraemos el primer objeto (el producto)
            $producto = !empty($resultado) ? $resultado[0] : null;

            return view('inventario.show', compact('producto'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }



}