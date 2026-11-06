<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use App\Models\PagoSalida;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Promocion;



class PosController extends Controller
{
    public function index()
    {
        try {
            // Traer todos los productos disponibles
            $productos = Producto::select(
                'producto.codigo',
                'producto.nombre',
                'categoria.nombre as categoria'
            )
                ->leftJoin('categoria', 'producto.idCategoria', '=', 'categoria.idCategoria')
                ->addSelect([
                    'precio' => DB::table('lista_precio')
                        ->select('precioVenta')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->where('fechaInicio', '<=', now())
                        ->whereNull('fechaFin')
                        ->whereNull('deleted_at')
                        ->orderByDesc('fechaInicio')
                        ->limit(1)
                ])
                ->addSelect([
                    'descuentoOferta' => DB::table('promocion')
                        ->select('porcentajeDescuento')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->where('activa', true)
                        ->where('porcentajeDescuento', '>', 0)
                        ->where('fechaInicio', '<=', now())
                        ->where('fechaFin', '>=', now())
                        ->whereNull('deleted_at')
                        ->orderByDesc('fechaInicio')
                        ->limit(1)
                ])
                ->addSelect([
                    'imagenProducto' => DB::table('imagen_producto')
                        ->select('rutaImagen')
                        ->whereColumn('codigoProducto', 'producto.codigo')
                        ->whereNull('deleted_at')
                        ->orderByDesc('id')
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

            // activar para debug
            //   dd($productos);

            $categorias = Categoria::whereNull('deleted_at')->get();
            $clientes = Cliente::whereNull('deleted_at')->get();
            $metodosPago = DB::table('metodo_pago')
                ->whereNull('deleted_at')
                ->orderBy('idMetodo')
                ->get();

            return view('pos.index', compact('productos', 'categorias', 'clientes', 'metodosPago'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el POS: ' . $e->getMessage());
        }

    }

    public function registrarVenta(Request $request)
    {
        $datos = $request->validate([
            'idTipo' => 'required|integer',
            'rutCliente' => 'nullable|string',
            'metodoPago' => 'required|integer|exists:metodo_pago,idMetodo',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'Usuario no autenticado.'], 401);
        }

        try {
            DB::beginTransaction();

            $lineasVenta = [];
            $totalVenta = 0;

            foreach ($datos['productos'] as $linea) {
                $producto = Producto::query()->where('codigo', $linea['id'])->first();
                if (! $producto) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'error' => 'Uno de los productos ya no está disponible. Recarga el POS.'], 422);
                }

                $precioNormal = DB::table('lista_precio')
                    ->where('codigoProducto', $producto->codigo)
                    ->where('fechaInicio', '<=', now())
                    ->whereNull('fechaFin')
                    ->whereNull('deleted_at')
                    ->orderByDesc('fechaInicio')
                    ->value('precioVenta');

                if ($precioNormal === null) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'error' => "El producto {$producto->nombre} no tiene un precio vigente."], 422);
                }

                $oferta = Promocion::vigentes()
                    ->where('codigoProducto', $producto->codigo)
                    ->orderByDesc('fechaInicio')
                    ->first();
                $descuento = (float) ($oferta?->porcentajeDescuento ?? 0);
                $precioCobrado = (float) round((float) $precioNormal * (1 - $descuento / 100));

                if (round((float) $linea['precio'], 2) !== round($precioCobrado, 2)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => "El precio de {$producto->nombre} cambió. Actualiza el POS y revisa el carrito antes de cobrar.",
                    ], 409);
                }

                $lineasVenta[] = [
                    'codigoProducto' => $producto->codigo,
                    'cantidad' => (int) $linea['cantidad'],
                    'precioCobrado' => $precioCobrado,
                ];
                $totalVenta += $precioCobrado * (int) $linea['cantidad'];
            }

            $salida = Salida::create([
                'fecha' => now(),
                'idTipo' => $request->idTipo,
                'idUsuario' => auth()->id(),
                'rutCliente' => $request->rutCliente,
                'totalSalida' => $totalVenta
            ]);

            foreach ($lineasVenta as $producto) {
                $salida->detalles()->create([
                    'codigoProducto' => $producto['codigoProducto'],
                    'cantidad' => $producto['cantidad'],
                    'precioCobrado' => $producto['precioCobrado']
                ]);
            }

            $metodoPago = DB::table('metodo_pago')
                ->where('idMetodo', $request->metodoPago)
                ->whereNull('deleted_at')
                ->first();

            $esFiado = $metodoPago
                && str_contains(mb_strtolower($metodoPago->nombre), 'fiado');

            PagoSalida::create([
                'idSalida' => $salida->idSalida,
                'idMetodo' => $request->metodoPago,
                'montoPagado' => $esFiado ? 0 : $totalVenta,
                'fechaPago' => now()->toDateString()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada e ingresada correctamente.'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Error al registrar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

}
