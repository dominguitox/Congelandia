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
                0
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
                $0
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
                0
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


        <button>
            + Agregar Cliente
        </button>


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


                <tr>


                    <td>
                        Sin clientes
                    </td>


                    <td>

                    </td>


                    <td>

                    </td>


                    <td>

                    </td>


                    <td>


                        <div class="acciones">


                            <button class="btn-accion historial"
                                    title="Ver historial">

                                🧾

                            </button>



                            <button class="btn-accion pago"
                                    title="Registrar pago">

                                💵

                            </button>



                            <button class="btn-accion editar"
                                    title="Editar cliente">

                                ✏️

                            </button>



                            <button class="btn-accion eliminar"
                                    title="Eliminar cliente">

                                🗑️

                            </button>


                        </div>


                    </td>


                </tr>


            </tbody>


        </table>


    </div>



</div>


@endsection