@extends('layouts.app')

@section('title','Clientes')


@push('css')
    @vite(['resources/css/clientes.css'])
@endpush



@section('content')


<div class="clientes-container">


    <div class="clientes-header">

        <h2>
            Clientes con Deudas
        </h2>

        <p>
            Gestiona los clientes que tienen deudas pendientes
        </p>

    </div>




    <div class="clientes-cards">


        <div class="cliente-card">

            <span>
                Clientes con Deuda
            </span>

            <strong>
                {{ $clientesDeudores->count() }}
            </strong>

            <div class="icon warning">
                !
            </div>

        </div>



        <div class="cliente-card">

            <span>
                Deuda Total
            </span>

            <strong>
                ${{ number_format($deudaTotal,0,',','.') }}
            </strong>

            <div class="icon danger">
                $
            </div>

        </div>



        <div class="cliente-card">

            <span>
                Pagados
            </span>

            <strong>
                {{ $clientesPagadosRecientes->count() }}
            </strong>

            <div class="icon success">
                ✓
            </div>

        </div>


    </div>





    <div class="clientes-actions">


        <input
            type="text"
            placeholder="Buscar por nombre o teléfono..."
        >



        <button
            type="button"
            onclick="toggleFormularioCliente()">

            + Agregar Cliente

        </button>


    </div>





    {{-- FORMULARIO NUEVO CLIENTE --}}

    <div 
        class="nuevo-cliente" 
        id="formularioCliente"
        style="display:none;">



        <div class="nuevo-cliente-header">


            <h3>
                Nuevo Cliente
            </h3>


            <button
                type="button"
                onclick="toggleFormularioCliente()">

                ✕

            </button>


        </div>




        <form 
            method="POST"
            action="{{ route('clientes.store') }}">


            @csrf



            <div class="form-grid">



                <div>


                    <label>
                        Nombre *
                    </label>


                    <input
                        type="text"
                        name="nombre"
                        placeholder="Nombre del cliente"
                        required>


                </div>




                <div>


                    <label>
                        Teléfono
                    </label>


                    <input
                        type="text"
                        name="telefono"
                        placeholder="+56 9 1234 5678">


                </div>



            </div>



            <div class="form-buttons">


                <button
                    type="submit"
                    class="btn-agregar">

                    Agregar

                </button>



                <button
                    type="button"
                    class="btn-cancelar"
                    onclick="toggleFormularioCliente()">

                    Cancelar

                </button>



            </div>



        </form>



    </div>





    <h3>
        Deudas Activas
    </h3>




    <div class="clientes-table">


        <table>


            <thead>

                <tr>

                    <th>
                        Cliente
                    </th>


                    <th>
                        Teléfono
                    </th>


                    <th>
                        Deuda
                    </th>


                    <th>
                        Última Act.
                    </th>


                    <th>
                        Acciones
                    </th>


                </tr>


            </thead>



            <tbody>


            @if($clientesDeudores->count() > 0)


                @foreach($clientesDeudores as $cliente)


                <tr>


                    <td>
                        {{ $cliente->nombre }}
                    </td>


                    <td>
                        {{ $cliente->telefono }}
                    </td>


                    <td>
                        ${{ number_format($cliente->deudaCalculada,0,',','.') }}
                    </td>


                    <td>

                        @if($cliente->ultimaVenta)

                            {{ \Carbon\Carbon::parse($cliente->ultimaVenta->fecha)->format('d-m-Y') }}

                        @else

                            Sin actividad

                        @endif


                    </td>



                    <td>


                        <div class="acciones">


                            <button class="btn-accion historial">
                                🧾
                            </button>


                            <button class="btn-accion pago">
                                💵
                            </button>


                            <button class="btn-accion editar">
                                ✏️
                            </button>


                            <button class="btn-accion eliminar">
                                🗑️
                            </button>


                        </div>


                    </td>


                </tr>


                @endforeach



            @else


                <tr>

                    <td colspan="5">

                        No existen clientes con deuda activa

                    </td>


                </tr>


            @endif



            </tbody>


        </table>


    </div>





    <h3>
        Pagados Recientemente
    </h3>




    <div class="clientes-table">


        <table>


            <thead>

                <tr>

                    <th>
                        Cliente
                    </th>


                    <th>
                        Teléfono
                    </th>


                    <th>
                        Monto Pagado
                    </th>


                    <th>
                        Fecha Pago
                    </th>


                    <th>
                        Acciones
                    </th>


                </tr>


            </thead>




            <tbody>



            @if($clientesPagadosRecientes->count() > 0)



                @foreach($clientesPagadosRecientes as $cliente)



                <tr>


                    <td>
                        {{ $cliente->nombre }}
                    </td>



                    <td>
                        {{ $cliente->telefono }}
                    </td>



                    <td>

                        @if($cliente->ultimoPago)

                            ${{ number_format($cliente->ultimoPago->montoPagado,0,',','.') }}

                        @else

                            Sin registro

                        @endif

                    </td>



                    <td>

                        @if($cliente->ultimoPago)

                            {{ $cliente->ultimoPago->fechaPago->format('d-m-Y') }}

                        @else

                            Sin registro

                        @endif


                    </td>



                    <td>


                        <div class="acciones">


                            <button class="btn-accion historial">
                                🧾
                            </button>


                            <button class="btn-accion eliminar">
                                🗑️
                            </button>


                        </div>


                    </td>


                </tr>



                @endforeach



            @else



                <tr>

                    <td colspan="5">

                        No existen clientes pagados

                    </td>

                </tr>



            @endif



            </tbody>


        </table>


    </div>




</div>


@endsection