<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Services\CobroVenta;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PosController extends Controller
{
    public function index()
    {
        $productos = Producto::conDetalle()
            ->addSelect([
                'descuentoOferta' => DB::table('promocion')->select('porcentajeDescuento')
                    ->whereColumn('codigoProducto', 'producto.codigo')->where('activa', true)
                    ->where('porcentajeDescuento', '>', 0)->where('fechaInicio', '<=', now())
                    ->where('fechaFin', '>=', now())->whereNull('deleted_at')->orderByDesc('fechaInicio')->limit(1),
                'imagenProducto' => DB::table('imagen_producto')->select('rutaImagen')
                    ->whereColumn('codigoProducto', 'producto.codigo')->whereNull('deleted_at')->orderByDesc('id')->limit(1),
            ])->get();
        $categorias = Categoria::all();
        $clientes = Cliente::all();
        $metodosPago = DB::table('metodo_pago')->whereNull('deleted_at')->whereRaw('LOWER(nombre) NOT LIKE ?', ['%fiado%'])->orderBy('idMetodo')->get();
        $idTipoVenta = DB::table('tipo_salida')->where('nombre', 'Venta')->whereNull('deleted_at')->value('idTipo');

        return view('pos.index', compact('productos', 'categorias', 'clientes', 'metodosPago', 'idTipoVenta'));
    }

    public function registrarVenta(Request $request, CobroVenta $cobro)
    {
        $metodoActivo = fn () => Rule::exists('metodo_pago', 'idMetodo')->whereNull('deleted_at');
        $datos = $request->validate([
            'idTipo' => ['required', 'integer', Rule::exists('tipo_salida', 'idTipo')->where('nombre', 'Venta')->whereNull('deleted_at')],
            'rutCliente' => ['nullable', 'string', Rule::exists('cliente', 'rutCliente')->whereNull('deleted_at')],
            'metodoPago' => ['required_without:pagos', Rule::prohibitedIf($request->has('pagos')), 'nullable', 'integer', $metodoActivo()],
            'pagos' => ['required_without:metodoPago', 'array', 'min:1'],
            'pagos.*.idMetodo' => ['required', 'integer', 'distinct', $metodoActivo()],
            'pagos.*.monto' => ['required', 'integer', 'min:0', 'max:9999999999'],
            'efectivoRecibido' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'claveOperacion' => ['required_with:pagos', 'nullable', 'uuid'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.id' => ['required', 'string', 'distinct'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
            'productos.*.precio' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $resultado = $cobro->registrar($datos, (int) $request->user()->idUsuario);
            $salida = $resultado['salida'];

            return response()->json([
                'success' => true, 'message' => 'Venta registrada correctamente.',
                'idSalida' => $salida->idSalida, 'total' => (int) $salida->totalSalida,
                'efectivoRecibido' => $salida->efectivoRecibido === null ? null : (int) $salida->efectivoRecibido,
                'vuelto' => (int) $salida->vuelto, 'repetida' => $resultado['repetida'],
            ]);
        } catch (ValidationException|HttpResponseException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['success' => false, 'error' => 'No se pudo registrar la venta. Reintenta el mismo cobro.'], 500);
        }
    }
}
