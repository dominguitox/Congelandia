<?php
//El controlador de la tabla Producto. 
// Tiene los metodos: 
// Crear, 
namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\ListaPrecio;
use App\Models\Promocion;
use App\Models\Ingreso;
use App\Models\DetalleIngreso;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Exception;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;


class ProductoController extends Controller
{
    public function crearProducto(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'string', 'max:50', Rule::unique('Producto', 'codigo')],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'idCategoria' => ['required', 'integer', Rule::exists('Categoria', 'idCategoria')->whereNull('deleted_at')],
            'precioVenta' => 'required|numeric|min:0',
            'idProveedor' => [Rule::requiredIf(fn () => $request->integer('stockInicial') > 0), 'nullable', 'integer', Rule::exists('Proveedor', 'idProveedor')->whereNull('deleted_at')],
            'stockInicial' => 'nullable|integer|min:0',
            'precioCompra' => [Rule::requiredIf(fn () => $request->integer('stockInicial') > 0), 'nullable', 'numeric', 'min:0'],
            'fechaVencimiento' => [Rule::requiredIf(fn () => $request->integer('stockInicial') > 0), 'nullable', 'date', 'after_or_equal:today'],
            'imagen' => 'nullable|image|max:2048'
        ]);

        try {
            DB::transaction(function () use ($request) {

                $idUsuario = auth()->user()->idUsuario;
                $producto = Producto::create([
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
                if ($request->hasFile('imagen')) {
                    $archivo = $request->file('imagen');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();

                    $archivo->move(public_path('images/productos'), $nombreArchivo);
                    \App\Models\ImagenProducto::create([
                        'rutaImagen' => 'images/productos/' . $nombreArchivo,
                        'codigoProducto' => $producto->codigo,
                        'alt' => $request->nombre,
                        'descripcion' => 'Imagen de ' . $request->nombre
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
                'producto.codigo',
                'producto.nombre',
                'producto.idCategoria',
                'producto.descripcion',
                'categoria.nombre as categoria'
            )
                ->leftJoin('categoria', 'producto.idCategoria', '=', 'categoria.idCategoria')
                ->addSelect([
                    'precio' => DB::table('lista_precio')
                        ->select('precioVenta')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->whereNull('fechaFin')
                        ->whereNull('deleted_at')
                        ->limit(1)
                ])
                ->addSelect([
                    'costo' => DB::table('detalle_ingreso')
                        ->select('detalle_ingreso.precioCompra')
                        ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
                        ->whereColumn('detalle_ingreso.codigoProducto', 'producto.codigo')
                        ->whereNull('detalle_ingreso.deleted_at')
                        ->whereNull('ingreso.deleted_at')
                        ->orderByDesc('ingreso.fecha')
                        ->limit(1)
                ])
                ->addSelect([
                    'proveedor' => DB::table('detalle_ingreso')
                        ->select('proveedor.nombre')
                        ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
                        ->join('proveedor', 'ingreso.idProveedor', '=', 'proveedor.idProveedor')
                        ->whereColumn('detalle_ingreso.codigoProducto', 'producto.codigo')
                        ->whereNull('detalle_ingreso.deleted_at')
                        ->whereNull('ingreso.deleted_at')
                        ->whereNull('proveedor.deleted_at')
                        ->orderByDesc('ingreso.fecha')
                        ->limit(1)
                ])
                ->addSelect([
                    'precioVenta' => ListaPrecio::select('precioVenta')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->orderByDesc('fechaInicio')
                        ->limit(1)
                ])
                ->selectRaw('(IFNULL((SELECT SUM(di.cantidad) FROM detalle_ingreso di INNER JOIN ingreso i ON di.idIngreso = i.idIngreso WHERE di.codigoProducto = producto.codigo AND di.deleted_at IS NULL AND i.deleted_at IS NULL), 0) - IFNULL((SELECT SUM(ds.cantidad) FROM detalle_salida ds INNER JOIN salida s ON ds.idSalida = s.idSalida WHERE ds.codigoProducto = producto.codigo AND ds.deleted_at IS NULL AND s.deleted_at IS NULL), 0)) AS stock')
                ->get();
            // Descomentar para debug
            // dd($productos);
            $proveedores = Proveedor::whereNull('deleted_at')->get();
            $categorias = Categoria::whereNull('deleted_at')->get();
            $ofertasActivas = Promocion::vigentes()
                ->with('producto')
                ->orderBy('fechaFin')
                ->get();
            $ofertas = Promocion::with('producto')
                ->orderByDesc('fechaInicio')
                ->get();

            return view('inventario.index', compact('productos', 'proveedores', 'categorias', 'ofertasActivas', 'ofertas'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function registrarIngreso(Request $request)
    {
        $datos = $request->validate([
            'codigoProducto' => ['required', 'string', Rule::exists('producto', 'codigo')->whereNull('deleted_at')],
            'idProveedor' => ['required', 'integer', Rule::exists('proveedor', 'idProveedor')->whereNull('deleted_at')],
            'cantidad' => ['required', 'integer', 'min:1'],
            'precioCompra' => ['required', 'numeric', 'min:0'],
            'fechaVencimiento' => ['required', 'date', 'after_or_equal:today'],
            '_modal' => ['required', 'in:modalRegistrarIngreso'],
        ]);

        DB::transaction(function () use ($datos) {
            $producto = DB::table('producto')
                ->where('codigo', $datos['codigoProducto'])
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            $proveedor = DB::table('proveedor')
                ->where('idProveedor', $datos['idProveedor'])
                ->whereNull('deleted_at')
                ->first();
            abort_if(! $producto || ! $proveedor, 404);
            $totalCompra = $datos['cantidad'] * $datos['precioCompra'];

            $idIngreso = DB::table('ingreso')->insertGetId([
                'fecha' => now(),
                'idProveedor' => $proveedor->idProveedor,
                'idUsuario' => auth()->user()->idUsuario,
                'totalCompra' => $totalCompra,
            ], 'idIngreso');

            DB::table('detalle_ingreso')->insert([
                'idIngreso' => $idIngreso,
                'codigoProducto' => $producto->codigo,
                'cantidad' => $datos['cantidad'],
                'precioCompra' => $datos['precioCompra'],
                'fechaVencimiento' => $datos['fechaVencimiento'],
            ]);
        });

        return redirect()->route('inventario.index')->with('success', 'Ingreso registrado correctamente.');
    }

    public function registrarSalida(Request $request)
    {
        $datos = $request->validate([
            'codigoProducto' => ['required', 'string', Rule::exists('producto', 'codigo')->whereNull('deleted_at')],
            'motivo' => ['required', Rule::in(['Merma por Vencimiento', 'Merma por Daño'])],
            'cantidad' => ['required', 'integer', 'min:1'],
            '_modal' => ['required', 'in:modalRegistrarSalida'],
        ]);

        DB::transaction(function () use ($datos) {
            $producto = DB::table('producto')
                ->where('codigo', $datos['codigoProducto'])
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            abort_if(! $producto, 404);

            $cantidadIngresada = DB::table('detalle_ingreso')
                ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
                ->where('detalle_ingreso.codigoProducto', $producto->codigo)
                ->whereNull('detalle_ingreso.deleted_at')
                ->whereNull('ingreso.deleted_at')
                ->sum('detalle_ingreso.cantidad');

            $cantidadSalida = DB::table('detalle_salida')
                ->join('salida', 'detalle_salida.idSalida', '=', 'salida.idSalida')
                ->where('detalle_salida.codigoProducto', $producto->codigo)
                ->whereNull('detalle_salida.deleted_at')
                ->whereNull('salida.deleted_at')
                ->sum('detalle_salida.cantidad');

            $stockDisponible = $cantidadIngresada - $cantidadSalida;
            if ($datos['cantidad'] > $stockDisponible) {
                throw ValidationException::withMessages([
                    'cantidad' => "La cantidad supera el stock disponible ({$stockDisponible}).",
                ]);
            }

            $tipo = DB::table('tipo_salida')
                ->where('nombre', $datos['motivo'])
                ->lockForUpdate()
                ->first();

            if ($tipo) {
                $idTipo = $tipo->idTipo;
                if ($tipo->deleted_at !== null) {
                    DB::table('tipo_salida')->where('idTipo', $idTipo)->update(['deleted_at' => null]);
                }
            } else {
                $idTipo = DB::table('tipo_salida')->insertGetId(['nombre' => $datos['motivo']], 'idTipo');
            }

            $idSalida = DB::table('salida')->insertGetId([
                'fecha' => now(),
                'idTipo' => $idTipo,
                'idUsuario' => auth()->user()->idUsuario,
                'rutCliente' => null,
                'totalSalida' => 0,
            ], 'idSalida');

            DB::table('detalle_salida')->insert([
                'idSalida' => $idSalida,
                'codigoProducto' => $producto->codigo,
                'cantidad' => $datos['cantidad'],
                'precioCobrado' => 0,
            ]);
        });

        return redirect()->route('inventario.index')->with('success', 'Salida por merma registrada correctamente.');
    }

    public function editarProducto(Request $request, $codigo)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'idCategoria' => ['required', 'integer', Rule::exists('Categoria', 'idCategoria')->whereNull('deleted_at')],
            'precioVenta' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|max:2048' // 1. Agregada la validación de la imagen
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

                // 5. Lógica para actualizar la imagen si se subió una nueva
                if ($request->hasFile('imagen')) {
                    $archivo = $request->file('imagen');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $archivo->move(public_path('images/productos'), $nombreArchivo);
                    
                    $nuevaRutaImagen = 'images/productos/' . $nombreArchivo;

                    // Buscar si el producto ya tiene una imagen registrada
                    $imagenActual = \App\Models\ImagenProducto::where('codigoProducto', $codigo)->first();

                    if ($imagenActual) {
                        // Eliminar el archivo físico anterior si existe en el servidor
                        if (file_exists(public_path($imagenActual->rutaImagen))) {
                            unlink(public_path($imagenActual->rutaImagen));
                        }
                        
                        // Actualizar el registro en la base de datos
                        $imagenActual->update([
                            'rutaImagen' => $nuevaRutaImagen,
                            'alt' => $request->nombre,
                            'descripcion' => 'Imagen de ' . $request->nombre
                        ]);
                    } else {
                        // Si no tenía imagen previa, se crea un registro nuevo
                        \App\Models\ImagenProducto::create([
                            'rutaImagen' => $nuevaRutaImagen,
                            'codigoProducto' => $codigo,
                            'alt' => $request->nombre,
                            'descripcion' => 'Imagen de ' . $request->nombre
                        ]);
                    }
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
                'producto.codigo',
                'producto.nombre',
                'categoria.nombre as categoria'
            )
                ->leftJoin('categoria', 'producto.idCategoria', '=', 'categoria.idCategoria')
                ->where('producto.codigo', $codigo)
                ->addSelect([
                    'precio' => DB::table('lista_precio')
                        ->select('precioVenta')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->whereNull('fechaFin')
                        ->whereNull('deleted_at')
                        ->limit(1)
                ])
                ->addSelect([
                    'costo' => DB::table('detalle_ingreso')
                        ->select('detalle_ingreso.precioCompra')
                        ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
                        ->whereColumn('detalle_ingreso.codigoProducto', 'producto.codigo')
                        ->whereNull('detalle_ingreso.deleted_at')
                        ->whereNull('ingreso.deleted_at')
                        ->orderByDesc('ingreso.fecha')
                        ->limit(1)
                ])
                ->addSelect([
                    'proveedor' => DB::table('detalle_ingreso')
                        ->select('proveedor.nombre')
                        ->join('ingreso', 'detalle_ingreso.idIngreso', '=', 'ingreso.idIngreso')
                        ->join('proveedor', 'ingreso.idProveedor', '=', 'proveedor.idProveedor')
                        ->whereColumn('detalle_ingreso.codigoProducto', 'producto.codigo')
                        ->whereNull('detalle_ingreso.deleted_at')
                        ->whereNull('ingreso.deleted_at')
                        ->whereNull('proveedor.deleted_at')
                        ->orderByDesc('ingreso.fecha')
                        ->limit(1)
                ])
                ->selectRaw('(IFNULL((SELECT SUM(di.cantidad) FROM detalle_ingreso di INNER JOIN ingreso i ON di.idIngreso = i.idIngreso WHERE di.codigoProducto = producto.codigo AND di.deleted_at IS NULL AND i.deleted_at IS NULL), 0) - IFNULL((SELECT SUM(ds.cantidad) FROM detalle_salida ds INNER JOIN salida s ON ds.idSalida = s.idSalida WHERE ds.codigoProducto = producto.codigo AND ds.deleted_at IS NULL AND s.deleted_at IS NULL), 0)) AS stock')
                ->get();

            // Como DB::select devuelve un arreglo, extraemos el primer objeto (el producto)
            $producto = !empty($resultado) ? $resultado[0] : null;

            return view('inventario.show', compact('producto'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }



}
