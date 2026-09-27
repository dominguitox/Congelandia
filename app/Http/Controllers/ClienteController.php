<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{

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
                ->whereHas('tipo', function($query){

                    $query->where('nombre','Venta');

                })
                ->get();



            // Total vendido
            $totalVentas = $ventas->sum('totalSalida');



            // Total pagado
            $totalPagado = 0;



            foreach($ventas as $venta){

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


            foreach($ventas as $venta){

                $pago = $venta->pagos()
                    ->orderBy('fechaPago','desc')
                    ->first();


                if($pago){

                    $ultimoPago = $pago;
                    break;

                }

            }


            $cliente->ultimoPago = $ultimoPago;




            // Separación de clientes

         if($deuda > 0){

            $clientesDeudores->push($cliente);

            $deudaTotal += $deuda;

}
else if($cliente->ultimoPago){

    $clientesPagadosRecientes->push($cliente);

    }


        }




        return view('clientes.index', compact(

            'clientesDeudores',

            'clientesPagadosRecientes',

            'deudaTotal'

        ));


    }
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
        ->with('success','Cliente creado correctamente');

    }
}