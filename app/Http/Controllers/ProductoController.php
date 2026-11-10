<?php

// El controlador de la tabla Producto.
// Tiene los metodos:
// Crear,

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\DetalleIngreso;
use App\Models\ImagenProducto;
use App\Models\Ingreso;
use App\Models\ListaPrecio;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Proveedor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    public function crearProducto(Request $request)
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:50', Rule::unique('producto', 'codigo')],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            ...$this->reglasRelacion($request, 'idCategoria', 'nuevaCategoria', 'categoria'),
            'precioVenta' => 'required|numeric|min:0',
            ...($request->integer('stockInicial') > 0
                ? $this->reglasRelacion($request, 'idProveedor', 'nuevoProveedor', 'proveedor')
                : ['idProveedor' => ['exclude'], 'nuevoProveedor' => ['exclude']]),
            'stockInicial' => 'nullable|integer|min:0',
            'precioCompra' => [Rule::requiredIf(fn () => $request->integer('stockInicial') > 0), 'nullable', 'numeric', 'min:0'],
            'fechaVencimiento' => [Rule::requiredIf(fn () => $request->integer('stockInicial') > 0), 'nullable', 'date', 'after_or_equal:today'],
            'imagen' => 'nullable|image|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $datos) {

                $idUsuario = auth()->user()->idUsuario;
                $producto = Producto::create([
                    'codigo' => $request->codigo,
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'idCategoria' => $this->resolverRelacion(Categoria::class, $datos['idCategoria'] ?? null, $datos['nuevaCategoria'] ?? null),
                ]);

                ListaPrecio::create([
                    'codigoProducto' => $request->codigo,
                    'precioVenta' => $request->precioVenta,
                    'fechaInicio' => now(),
                ]);

                if ($request->integer('stockInicial') > 0) {
                    $totalCompra = $request->precioCompra * $request->stockInicial;
                    $ingreso = Ingreso::create([
                        'fecha' => now(),
                        'idProveedor' => $this->resolverRelacion(Proveedor::class, $datos['idProveedor'] ?? null, $datos['nuevoProveedor'] ?? null),
                        'idUsuario' => $idUsuario,
                        'totalCompra' => $totalCompra,
                    ]);

                    DetalleIngreso::create([
                        'idIngreso' => $ingreso->idIngreso,
                        'codigoProducto' => $request->codigo,
                        'cantidad' => $request->stockInicial,
                        'precioCompra' => $request->precioCompra,
                        'fechaVencimiento' => $request->fechaVencimiento,
                    ]);
                }
                if ($request->hasFile('imagen')) {
                    $archivo = $request->file('imagen');
                    $nombreArchivo = time().'_'.$archivo->getClientOriginalName();

                    $archivo->move(public_path('images/productos'), $nombreArchivo);
                    ImagenProducto::create([
                        'rutaImagen' => 'images/productos/'.$nombreArchivo,
                        'codigoProducto' => $producto->codigo,
                        'alt' => $request->nombre,
                        'descripcion' => 'Imagen de '.$request->nombre,
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Producto creado e ingresado al stock correctamente.');

        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al crear el producto: '.$e->getMessage());
        }
    }

    public function listarProductos(Request $request)
    {
        try {
            $productos = Producto::conDetalle()->get();
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
            return redirect()->back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function registrarIngreso(Request $request)
    {
        // Crear proveedores conserva el mismo permiso que el módulo administrativo.
        abort_if($request->input('idProveedor') === 'nuevo' && ! $request->user()->tieneRol('Administrador'), 404);

        $datos = $request->validate([
            'codigoProducto' => ['required', 'string', Rule::exists('producto', 'codigo')->whereNull('deleted_at')],
            ...$this->reglasRelacion($request, 'idProveedor', 'nuevoProveedor', 'proveedor'),
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
            abort_if(! $producto, 404);
            $idProveedor = $this->resolverRelacion(Proveedor::class, $datos['idProveedor'] ?? null, $datos['nuevoProveedor'] ?? null);
            $totalCompra = $datos['cantidad'] * $datos['precioCompra'];

            $idIngreso = DB::table('ingreso')->insertGetId([
                'fecha' => now(),
                'idProveedor' => $idProveedor,
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
        $datos = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            ...$this->reglasRelacion($request, 'idCategoria', 'nuevaCategoria', 'categoria'),
            'precioVenta' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|max:2048', // 1. Agregada la validación de la imagen
        ]);

        try {
            DB::transaction(function () use ($request, $codigo, $datos) {

                // 1. Buscar el producto específico
                $producto = Producto::where('codigo', $codigo)->firstOrFail();

                // 2. Actualizar solo los datos del catálogo
                $producto->update([
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'idCategoria' => $this->resolverRelacion(Categoria::class, $datos['idCategoria'] ?? null, $datos['nuevaCategoria'] ?? null),
                ]);

                // 3. Consultar el precio activo actual
                $precioActual = ListaPrecio::where('codigoProducto', $codigo)
                    ->orderBy('fechaInicio', 'desc')
                    ->first();

                // 4. Insertar en el historial solo si el precio cambió
                if (! $precioActual || $precioActual->precioVenta != $request->precioVenta) {
                    ListaPrecio::create([
                        'codigoProducto' => $codigo,
                        'precioVenta' => $request->precioVenta,
                        'fechaInicio' => now(),
                    ]);
                }

                // 5. Lógica para actualizar la imagen si se subió una nueva
                if ($request->hasFile('imagen')) {
                    $archivo = $request->file('imagen');
                    $nombreArchivo = time().'_'.$archivo->getClientOriginalName();
                    $archivo->move(public_path('images/productos'), $nombreArchivo);

                    $nuevaRutaImagen = 'images/productos/'.$nombreArchivo;

                    // Buscar si el producto ya tiene una imagen registrada
                    $imagenActual = ImagenProducto::where('codigoProducto', $codigo)->first();

                    if ($imagenActual) {
                        // Eliminar el archivo físico anterior si existe en el servidor
                        if (file_exists(public_path($imagenActual->rutaImagen))) {
                            unlink(public_path($imagenActual->rutaImagen));
                        }

                        // Actualizar el registro en la base de datos
                        $imagenActual->update([
                            'rutaImagen' => $nuevaRutaImagen,
                            'alt' => $request->nombre,
                            'descripcion' => 'Imagen de '.$request->nombre,
                        ]);
                    } else {
                        // Si no tenía imagen previa, se crea un registro nuevo
                        ImagenProducto::create([
                            'rutaImagen' => $nuevaRutaImagen,
                            'codigoProducto' => $codigo,
                            'alt' => $request->nombre,
                            'descripcion' => 'Imagen de '.$request->nombre,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Producto editado correctamente.');

        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al editar el producto: '.$e->getMessage());
        }
    }

    private function reglasRelacion(Request $request, string $campo, string $nombre, string $tabla): array
    {
        return [
            $campo => ['nullable', Rule::when($request->input($campo) !== 'nuevo', [
                'integer', Rule::exists($tabla, $campo)->whereNull('deleted_at'),
            ])],
            $nombre => ['exclude_unless:'.$campo.',nuevo', 'required', 'string', 'max:100'],
        ];
    }

    /** Se ejecuta dentro de la transacción del producto o ingreso. */
    private function resolverRelacion(string $modelo, mixed $seleccion, ?string $nombre): ?int
    {
        if ($seleccion === null || $seleccion === '') {
            return null;
        }

        if ($seleccion !== 'nuevo') {
            return (int) $modelo::query()->lockForUpdate()->findOrFail($seleccion)->getKey();
        }

        $nombre = trim($nombre);
        $existente = $modelo::query()->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)])
            ->lockForUpdate()->first();

        return (int) ($existente ?? $modelo::create(['nombre' => $nombre]))->getKey();
    }

    public function show(string $codigo)
    {
        try {
            // El signo de interrogación inyecta la variable de forma segura
            $resultado = Producto::conDetalle()->where('producto.codigo', $codigo)->get();

            // Como DB::select devuelve un arreglo, extraemos el primer objeto (el producto)
            $producto = ! empty($resultado) ? $resultado[0] : null;

            return view('inventario.show', compact('producto'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: '.$e->getMessage());
        }
    }
}
