<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Salida;


class VentaController extends Controller
{
    public function registrar(Request $request)
    {
        // Validar que los datos lleguen correctamente
        $request->validate([
            'carrito' => 'required|array|min:1',
            'metodoPago' => 'required|string',
        ]);

        $carrito = $request->input('carrito');
        $metodoPago = $request->input('metodoPago');

        // Iniciamos una transacción para garantizar la integridad de los datos
        try {
            DB::beginTransaction();

            $totalVenta = 0;

            // 1. Verificación previa de stock para evitar anomalías
            foreach ($carrito as $item) {
                $producto = Producto::where('codigo', $item['id'])->lockForUpdate()->first();

                if (!$producto || $producto->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para el producto: " . ($producto ? $producto->nombre : $item['id']));
                }

                $totalVenta += $producto->precioVenta * $item['cantidad'];
            }

            // 2. Crear el registro principal de la Venta
            // (Asumiendo que tienes un campo para el usuario autenticado o cajero)
            $venta = Salida::create([
                'fechaHora' => now(),
                'totalVenta' => $totalVenta,
                'metodoPago' => $metodoPago,
            ]);

            // 3. Registrar los detalles de venta y descontar el stock
            foreach ($carrito as $item) {
                $producto = Producto::where('codigo', $item['id'])->first();

                // Insertar en DetalleVenta (según tu modelo entidad-relación)
                DB::table('detalle_ventas')->insert([
                    'idVenta' => $venta->idVenta, // O id segun tu PK
                    'codigo_producto' => $producto->codigo,
                    'cantidad' => $item['cantidad'],
                    'precioCobrado' => $producto->precioVenta,
                ]);

                // Descontar el stock del producto
                $producto->stock -= $item['cantidad'];
                $producto->save();
            }

            // Si todo sale bien, confirmamos los cambios en la base de datos
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Venta registrada con éxito!',
                'idVenta' => $venta->idVenta
            ]);

        } catch (\Exception $e) {
            // Si ocurre algún error, revertimos todo
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
?>