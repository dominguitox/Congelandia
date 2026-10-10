<?php
//El controlador de la tabla Producto. 
// Tiene los metodos: 
// Crear, 
namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\ListaPrecio;
use App\Models\Ingreso;
use App\Models\DetalleIngreso;

use App\Models\Proveedor;
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
            'precioVenta' => 'nullable|numeric|min:0',
            'idProveedor' => 'nullable|integer',
            'stockInicial' => 'nullable|integer|min:0',
            'precioCompra' => 'nullable|numeric|min:0',
            'fechaVencimiento' => 'nullable|date'
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

                if ($request->stockInicial > 0 && $request->filled('idProveedor') && $request->filled('precioCompra') && $request->filled('fechaVencimiento')) {
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
            $productos = Producto::select(
                'Producto.codigo',
                'Producto.nombre',
                'Producto.idCategoria',
                'Producto.descripcion',
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
                ->addSelect([
                    'precioVenta' => ListaPrecio::select('precioVenta')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->orderByDesc('fechaInicio')
                        ->limit(1)
                ])
                ->selectRaw('(IFNULL((SELECT SUM(cantidad) FROM Detalle_Ingreso WHERE codigoProducto = Producto.codigo), 0) - IFNULL((SELECT SUM(cantidad) FROM Detalle_Salida WHERE codigoProducto = Producto.codigo), 0)) AS stock')
                ->get();
            // Descomentar para debug
            // dd($productos);
            $proveedores = Proveedor::whereNull('deleted_at')->get();
            $categorias = Categoria::whereNull('deleted_at')->get();
            return view('inventario.index', compact('productos', 'proveedores', 'categorias'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function editarProducto(Request $request, $codigo)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'idCategoria' => 'required|integer',
            'precioVenta' => 'required|numeric|min:0'
        ]);

        try {
            DB::transaction(function () use ($request, $codigo) {

                // 1. Buscar el producto específico
                $producto = Producto::where('codigo', $codigo)->firstOrFail();

                // 2. Actualizar solo los datos del catálogo
                $producto->update([
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'idCategoria' => $request->idCategoria,
                ]);

                // 3. Consultar el precio activo actual
                $precioActual = ListaPrecio::where('codigoProducto', $codigo)
                    ->orderBy('fechaInicio', 'desc')
                    ->first();

                // 4. Insertar en el historial solo si el precio cambió
                if (!$precioActual || $precioActual->precioVenta != $request->precioVenta) {
                    ListaPrecio::create([
                        'codigoProducto' => $codigo,
                        'precioVenta' => $request->precioVenta,
                        'fechaInicio' => now(),
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Producto editado correctamente.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al editar el producto: ' . $e->getMessage());
        }
    }
    public function show(string $codigo)
    {
        try {
            // El signo de interrogación inyecta la variable de forma segura
            $resultado = Producto::select(
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

            // Como DB::select devuelve un arreglo, extraemos el primer objeto (el producto)
            $producto = !empty($resultado) ? $resultado[0] : null;

            return view('inventario.show', compact('producto'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }



}