<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Salida;
use App\Models\PagoSalida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /*
     * =========================================================
     * LISTADO DE CLIENTES
     * =========================================================
     */

    public function index()
    {
        // Clientes activos
        $clientes = Cliente::whereNull('deleted_at')->get();

        $clientesDeudores = collect();
        $clientesPagadosRecientes = collect();
        $deudaTotal = 0;

        foreach ($clientes as $cliente) {

            // Ventas realizadas al cliente
            $ventas = $cliente->salidas()
                ->whereHas('tipo', function ($query) {
                    $query->where('nombre', 'Venta');
                })
                ->get();

            // Total vendido
            $totalVentas = $ventas->sum('totalSalida');

            // Total pagado
            $totalPagado = 0;

            foreach ($ventas as $venta) {
                $totalPagado += $venta->pagos
                    ->sum('montoPagado');
            }

            // Deuda actual
            $deuda = $totalVentas - $totalPagado;

            // Guardamos deuda calculada para Blade
            $cliente->deudaCalculada = $deuda;

            // Última venta realizada
            $cliente->ultimaVenta = $ventas
                ->sortByDesc('fecha')
                ->first();

            // Último pago realizado
            $ultimoPago = null;

            foreach ($ventas as $venta) {

                $pago = $venta->pagos()
                    ->orderBy('fechaPago', 'desc')
                    ->first();

                if ($pago) {
                    $ultimoPago = $pago;
                    break;
                }
            }

            $cliente->ultimoPago = $ultimoPago;

            // Separación de clientes
            if ($deuda > 0) {

                $clientesDeudores->push($cliente);

                $deudaTotal += $deuda;

            } elseif ($cliente->ultimoPago) {

                $clientesPagadosRecientes->push($cliente);
            }
        }

        return view('clientes.index', compact(
            'clientesDeudores',
            'clientesPagadosRecientes',
            'deudaTotal'
        ));
    }


    /*
     * =========================================================
     * CREAR CLIENTE
     * =========================================================
     */

    public function store(Request $request)
    {
        $request->validate([

            'rutCliente' => 'required|string|max:15|unique:Cliente,rutCliente',

            'nombre' => 'required|string|max:100',

            'telefono' => 'nullable|string|max:20'

        ]);

        Cliente::create([

            'rutCliente' => $request->rutCliente,

            'nombre' => $request->nombre,

            'telefono' => $request->telefono,

            'saldoDeuda' => 0

        ]);

        return redirect()
            ->route('clientes.index')
            ->with(
                'success',
                'Cliente creado correctamente'
            );
    }


    /*
     * =========================================================
     * EDITAR CLIENTE
     * =========================================================
     */

    public function update(Request $request, $rutCliente)
    {
        // Buscar solamente clientes activos
        $cliente = Cliente::where('rutCliente', $rutCliente)
            ->whereNull('deleted_at')
            ->firstOrFail();

        // El RUT no se modifica
        $request->validate([

            'nombre' => [
                'required',
                'string',
                'max:100'
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:20'
            ]

        ]);

        // Actualizar información
        $cliente->nombre = $request->nombre;
        $cliente->telefono = $request->telefono;

        $cliente->save();

        return redirect()
            ->route('clientes.index')
            ->with(
                'success',
                'Cliente actualizado correctamente.'
            );
    }


    /*
     * =========================================================
     * HISTORIAL DEL CLIENTE
     * =========================================================
     */

    public function historial($rutCliente)
    {
        $cliente = Cliente::where('rutCliente', $rutCliente)
            ->whereNull('deleted_at')
            ->firstOrFail();

        // Obtener solamente las ventas
        $ventas = $cliente->salidas()
            ->whereHas('tipo', function ($query) {
                $query->where('nombre', 'Venta');
            })
            ->with([
                'pagos',
                'detalles'
            ])
            ->orderBy('fecha', 'desc')
            ->get();

        // Preparar información para JavaScript
        $historial = $ventas->map(function ($venta) {

            // Total pagado
            $totalPagado = $venta->pagos
                ->sum('montoPagado');

            // Saldo pendiente
            $saldoPendiente = max(
                0,
                $venta->totalSalida - $totalPagado
            );

            // Estado de la venta
            if ($saldoPendiente <= 0) {

                $estado = 'Pagada';

            } elseif ($totalPagado > 0) {

                $estado = 'Pago parcial';

            } else {

                $estado = 'Pendiente';
            }

            return [

                'idSalida' => $venta->idSalida,

                'fecha' => $venta->fecha,

                'total' => $venta->totalSalida,

                'totalPagado' => $totalPagado,

                'saldoPendiente' => $saldoPendiente,

                'estado' => $estado,

                'detalles' => $venta->detalles

            ];
        });

        return response()->json([

            'cliente' => [

                'rutCliente' => $cliente->rutCliente,

                'nombre' => $cliente->nombre,

                'telefono' => $cliente->telefono

            ],

            'ventas' => $historial
        ]);
    }


    /*
     * =========================================================
     * REGISTRAR PAGO
     * =========================================================
     */

    public function registrarPago(Request $request, $idSalida)
    {
        // Validar datos
        $request->validate([

            'montoPagado' => [
                'required',
                'numeric',
                'min:1'
            ],

            'idMetodo' => [
                'required',
                'integer',
                'exists:metodo_pago,idMetodo'
            ]

        ]);

        // Registrar dentro de una transacción
        DB::transaction(function () use ($request, $idSalida) {

            $venta = Salida::where('idSalida', $idSalida)
                ->whereHas('tipo', function ($query) {
                    $query->where('nombre', 'Venta');
                })
                ->lockForUpdate()
                ->firstOrFail();

            // Total pagado anteriormente
            $totalPagado = $venta->pagos()
                ->sum('montoPagado');

            // Saldo pendiente
            $saldoPendiente =
                $venta->totalSalida - $totalPagado;

            // Venta ya pagada
            if ($saldoPendiente <= 0) {

                abort(
                    422,
                    'Esta venta ya se encuentra completamente pagada.'
                );
            }

            // Monto del nuevo pago
            $montoPago = (float) $request->montoPagado;

            // No permitir pagar más que el saldo
            if ($montoPago > $saldoPendiente) {

                abort(
                    422,
                    'El monto del pago no puede superar el saldo pendiente de la venta.'
                );
            }

            // Registrar pago
            PagoSalida::create([

                'idSalida' => $venta->idSalida,

                'idMetodo' => $request->idMetodo,

                'montoPagado' => $montoPago

            ]);
        });

        return redirect()
            ->route('clientes.index')
            ->with(
                'success',
                'Pago registrado correctamente.'
            );
    }


    /*
     * =========================================================
     * ELIMINAR CLIENTE
     * =========================================================
     */

    public function destroy($rutCliente)
    {
        // Buscar solamente clientes activos
        $cliente = Cliente::where('rutCliente', $rutCliente)
            ->whereNull('deleted_at')
            ->firstOrFail();

        /*
         * Eliminación lógica.
         *
         * No se elimina físicamente el cliente.
         * Se conserva su historial y sus ventas.
         */

        $cliente->deleted_at = now();

        $cliente->save();

        return redirect()
            ->route('clientes.index')
            ->with(
                'success',
                'Cliente eliminado correctamente.'
            );
    }
}