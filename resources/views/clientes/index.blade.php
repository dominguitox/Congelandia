@extends('layouts.app')

@section('title', 'Clientes')

@push('css')
    @vite(['resources/css/clientes.css'])

    <style>

        /* =====================================================
           MODAL REGISTRAR PAGO
        ====================================================== */

        .modal-pago {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 20px;
        }

        .modal-pago-contenido {
            width: 100%;
            max-width: 560px;
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.20);
        }

        .modal-pago-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .modal-pago-header h3 {
            margin: 0 0 5px;
        }

        .modal-pago-header p {
            margin: 0;
            color: #47738c;
        }

        .cerrar-pago {
            border: none;
            background: transparent;
            font-size: 25px;
            cursor: pointer;
            color: #47738c;
        }

        .pago-form-group {
            margin-bottom: 18px;
        }

        .pago-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #102b42;
        }

        .pago-form-group select,
        .pago-form-group input {
            width: 100%;
            height: 48px;
            border: 1px solid #c5e2ef;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 15px;
            background: #ffffff;
            box-sizing: border-box;
        }

        .pago-resumen {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin: 20px 0;
        }

        .pago-resumen-item {
            background: #eefaff;
            border-radius: 12px;
            padding: 14px;
        }

        .pago-resumen-item span {
            display: block;
            font-size: 13px;
            color: #47738c;
            margin-bottom: 5px;
        }

        .pago-resumen-item strong {
            font-size: 17px;
            color: #102b42;
        }

        .pago-saldo {
            color: #e00000 !important;
        }

        .pago-metodos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .pago-metodo {
            position: relative;
        }

        .pago-metodo input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .pago-metodo label {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            border: 1px solid #c5e2ef;
            border-radius: 12px;
            cursor: pointer;
            background: #ffffff;
            transition: 0.2s;
        }

        .pago-metodo input:checked + label {
            background: #d9f1f8;
            border-color: #009fc3;
            color: #007f9d;
            font-weight: 700;
        }

        .pago-error {
            display: none;
            background: #ffe0e0;
            color: #a00000;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .pago-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
        }

        .pago-buttons button {
            border: none;
            border-radius: 12px;
            padding: 12px 22px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-pago-cancelar {
            background: #dceef5;
            color: #35667c;
        }

        .btn-pago-confirmar {
            background: #009fc3;
            color: white;
        }

        .btn-pago-confirmar:hover {
            background: #0085a3;
        }


        /* =====================================================
           MODAL EDITAR CLIENTE
        ====================================================== */

        .modal-editar {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            padding: 20px;
        }

        .modal-editar-contenido {
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.20);
        }

        .modal-editar-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .modal-editar-header h3 {
            margin: 0 0 5px;
            color: #102b42;
        }

        .modal-editar-header p {
            margin: 0;
            color: #47738c;
            font-size: 14px;
        }

        .cerrar-editar {
            border: none;
            background: transparent;
            font-size: 25px;
            cursor: pointer;
            color: #47738c;
        }

        .editar-form-group {
            margin-bottom: 18px;
        }

        .editar-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #102b42;
        }

        .editar-form-group input {
            width: 100%;
            height: 48px;
            border: 1px solid #c5e2ef;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .editar-form-group input:focus {
            outline: none;
            border-color: #009fc3;
        }

        .editar-rut {
            background: #f1f5f7;
            color: #6b7f8a;
            cursor: not-allowed;
        }

        .editar-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
        }

        .editar-buttons button {
            border: none;
            border-radius: 12px;
            padding: 12px 22px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-editar-cancelar {
            background: #dceef5;
            color: #35667c;
        }

        .btn-editar-guardar {
            background: #009fc3;
            color: white;
        }

        .btn-editar-guardar:hover {
            background: #0085a3;
        }


        @media (max-width: 600px) {

            .pago-resumen {
                grid-template-columns: 1fr;
            }

            .pago-metodos {
                grid-template-columns: 1fr;
            }

            .modal-pago-contenido,
            .modal-editar-contenido {
                padding: 22px;
            }
        }

    </style>
@endpush


@section('content')

<div class="clientes-container">


    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <div class="clientes-header">

        <h2>
            Clientes con Deudas
        </h2>

        <p>
            Gestiona los clientes que tienen deudas pendientes
        </p>

    </div>


    {{-- =====================================================
         TARJETAS
    ====================================================== --}}

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
                ${{ number_format($deudaTotal, 0, ',', '.') }}
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


    {{-- =====================================================
         BUSCADOR Y BOTÓN
    ====================================================== --}}

    <div class="clientes-actions">

        <input
            type="text"
            id="buscadorClientes"
            placeholder="Buscar por nombre o teléfono..."
            autocomplete="off"
        >

        <button
            type="button"
            onclick="toggleFormularioCliente()"
        >
            + Agregar Cliente
        </button>

    </div>


    {{-- =====================================================
         FORMULARIO NUEVO CLIENTE
    ====================================================== --}}

    <div
        class="nuevo-cliente"
        id="formularioCliente"
        style="display: none;"
    >

        <div class="nuevo-cliente-header">

            <h3>
                Nuevo Cliente
            </h3>

            <button
                type="button"
                onclick="toggleFormularioCliente()"
                title="Cerrar"
            >
                ✕
            </button>

        </div>


        <form
            method="POST"
            action="{{ route('clientes.store') }}"
        >

            @csrf

            <div class="form-grid">

                <div>

                    <label>
                        RUT *
                    </label>

                    <input
                        type="text"
                        name="rutCliente"
                        placeholder="11111111-1"
                        maxlength="15"
                        required
                    >

                </div>


                <div>

                    <label>
                        Nombre *
                    </label>

                    <input
                        type="text"
                        name="nombre"
                        placeholder="Nombre del cliente"
                        maxlength="100"
                        required
                    >

                </div>


                <div>

                    <label>
                        Teléfono
                    </label>

                    <input
                        type="text"
                        name="telefono"
                        placeholder="+56 9 1234 5678"
                        maxlength="20"
                    >

                </div>

            </div>


            <div class="form-buttons">

                <button
                    type="submit"
                    class="btn-agregar"
                >
                    Agregar
                </button>

                <button
                    type="button"
                    class="btn-cancelar"
                    onclick="toggleFormularioCliente()"
                >
                    Cancelar
                </button>

            </div>

        </form>

    </div>


    {{-- =====================================================
         DEUDAS ACTIVAS
    ====================================================== --}}

    <h3>
        Deudas Activas
    </h3>


    <div class="clientes-table">

        <table>

            <thead>

                <tr>

                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Deuda</th>
                    <th>Última Act.</th>
                    <th>Acciones</th>

                </tr>

            </thead>


            <tbody id="tablaDeudas">

                @if($clientesDeudores->count() > 0)

                    @foreach($clientesDeudores as $cliente)

                    <tr class="fila-cliente">

                        <td class="cliente-nombre">
                            {{ $cliente->nombre }}
                        </td>

                        <td class="cliente-telefono">
                            {{ $cliente->telefono }}
                        </td>

                        <td>
                            ${{ number_format($cliente->deudaCalculada, 0, ',', '.') }}
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

                                {{-- HISTORIAL --}}

                                <button
                                    type="button"
                                    class="btn-accion historial"
                                    title="Ver historial"
                                    onclick="verHistorialCliente('{{ $cliente->rutCliente }}')"
                                >
                                    🧾
                                </button>


                                {{-- REGISTRAR PAGO --}}

                                <button
                                    type="button"
                                    class="btn-accion pago"
                                    title="Registrar pago"
                                    onclick="abrirModalPago('{{ $cliente->rutCliente }}')"
                                >
                                    💵
                                </button>


                                {{-- EDITAR --}}

                                <button
                                    type="button"
                                    class="btn-accion editar"
                                    title="Editar cliente"
                                    onclick="abrirModalEditar(
                                        '{{ $cliente->rutCliente }}',
                                        @js($cliente->nombre),
                                        @js($cliente->telefono)
                                    )"
                                >
                                    ✏️
                                </button>


                                {{-- ELIMINAR --}}

                                <form
                                    method="POST"
                                    action="{{ route('clientes.destroy', $cliente->rutCliente) }}"
                                    style="display: inline;"
                                    onsubmit="return confirmarEliminacionCliente(@js($cliente->nombre));"
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn-accion eliminar"
                                        title="Eliminar cliente"
                                    >
                                        🗑️
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    @endforeach


                    <tr
                        id="sinResultadosDeudas"
                        style="display: none;"
                    >

                        <td colspan="5">
                            No se encontraron clientes que coincidan con la búsqueda.
                        </td>

                    </tr>

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


    {{-- =====================================================
         PAGADOS RECIENTEMENTE
    ====================================================== --}}

    <h3>
        Pagados Recientemente
    </h3>


    <div class="clientes-table">

        <table>

            <thead>

                <tr>

                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Monto Pagado</th>
                    <th>Fecha Pago</th>
                    <th>Acciones</th>

                </tr>

            </thead>


            <tbody id="tablaPagados">

                @if($clientesPagadosRecientes->count() > 0)

                    @foreach($clientesPagadosRecientes as $cliente)

                    <tr class="fila-cliente">

                        <td class="cliente-nombre">
                            {{ $cliente->nombre }}
                        </td>

                        <td class="cliente-telefono">
                            {{ $cliente->telefono }}
                        </td>

                        <td>

                            @if($cliente->ultimoPago)

                                ${{ number_format($cliente->ultimoPago->montoPagado, 0, ',', '.') }}

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

                                {{-- HISTORIAL --}}

                                <button
                                    type="button"
                                    class="btn-accion historial"
                                    title="Ver historial"
                                    onclick="verHistorialCliente('{{ $cliente->rutCliente }}')"
                                >
                                    🧾
                                </button>


                                {{-- EDITAR --}}

                                <button
                                    type="button"
                                    class="btn-accion editar"
                                    title="Editar cliente"
                                    onclick="abrirModalEditar(
                                        '{{ $cliente->rutCliente }}',
                                        @js($cliente->nombre),
                                        @js($cliente->telefono)
                                    )"
                                >
                                    ✏️
                                </button>


                                {{-- ELIMINAR --}}

                                <form
                                    method="POST"
                                    action="{{ route('clientes.destroy', $cliente->rutCliente) }}"
                                    style="display: inline;"
                                    onsubmit="return confirmarEliminacionCliente(@js($cliente->nombre));"
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn-accion eliminar"
                                        title="Eliminar cliente"
                                    >
                                        🗑️
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    @endforeach


                    <tr
                        id="sinResultadosPagados"
                        style="display: none;"
                    >

                        <td colspan="5">
                            No se encontraron clientes que coincidan con la búsqueda.
                        </td>

                    </tr>

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


{{-- =========================================================
     MODAL HISTORIAL
========================================================= --}}

<div
    id="modalHistorial"
    class="modal-historial"
    style="display: none;"
>

    <div class="modal-historial-contenido">

        <div class="modal-historial-header">

            <div>

                <h3>
                    Historial del Cliente
                </h3>

                <p id="historialClienteNombre">
                    -
                </p>

            </div>

            <button
                type="button"
                class="cerrar-historial"
                onclick="cerrarHistorial()"
            >
                ✕
            </button>

        </div>


        <div class="historial-info">

            <div>

                <span>RUT</span>

                <strong id="historialClienteRut">
                    -
                </strong>

            </div>


            <div>

                <span>Teléfono</span>

                <strong id="historialClienteTelefono">
                    -
                </strong>

            </div>


            <div>

                <span>Ventas</span>

                <strong id="historialCantidadVentas">
                    0
                </strong>

            </div>

        </div>


        <div class="historial-ventas">

            <h4>
                Ventas realizadas
            </h4>

            <div
                id="historialContenido"
                class="historial-lista"
            >

                <p class="historial-cargando">
                    Cargando historial...
                </p>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     MODAL REGISTRAR PAGO
========================================================= --}}

<div
    id="modalPago"
    class="modal-pago"
>

    <div class="modal-pago-contenido">

        <div class="modal-pago-header">

            <div>

                <h3>
                    Registrar Pago
                </h3>

                <p id="pagoClienteNombre">
                    -
                </p>

            </div>

            <button
                type="button"
                class="cerrar-pago"
                onclick="cerrarModalPago()"
            >
                ✕
            </button>

        </div>


        <div
            id="pagoError"
            class="pago-error"
        ></div>


        <form
            id="formRegistrarPago"
            method="POST"
            action=""
        >

            @csrf


            <div class="pago-form-group">

                <label for="pagoVenta">
                    Venta pendiente
                </label>

                <select
                    id="pagoVenta"
                    name="idSalida"
                    required
                >

                    <option value="">
                        Seleccione una venta
                    </option>

                </select>

            </div>


            <div
                id="pagoResumen"
                class="pago-resumen"
                style="display: none;"
            >

                <div class="pago-resumen-item">

                    <span>Total venta</span>

                    <strong id="pagoTotalVenta">
                        $0
                    </strong>

                </div>


                <div class="pago-resumen-item">

                    <span>Pagado</span>

                    <strong id="pagoTotalPagado">
                        $0
                    </strong>

                </div>


                <div class="pago-resumen-item">

                    <span>Saldo pendiente</span>

                    <strong
                        id="pagoSaldoPendiente"
                        class="pago-saldo"
                    >
                        $0
                    </strong>

                </div>

            </div>


            <div class="pago-form-group">

                <label for="montoPagado">
                    Monto a pagar
                </label>

                <input
                    type="number"
                    id="montoPagado"
                    name="montoPagado"
                    min="1"
                    step="1"
                    placeholder="Ingrese el monto"
                    required
                >

            </div>


            <div class="pago-form-group">

                <label>
                    Método de pago
                </label>

                <div class="pago-metodos">

                    <div class="pago-metodo">

                        <input
                            type="radio"
                            id="metodoEfectivo"
                            name="idMetodo"
                            value="1"
                            required
                        >

                        <label for="metodoEfectivo">
                            💵 Efectivo
                        </label>

                    </div>


                    <div class="pago-metodo">

                        <input
                            type="radio"
                            id="metodoDebito"
                            name="idMetodo"
                            value="2"
                            required
                        >

                        <label for="metodoDebito">
                            💳 Débito
                        </label>

                    </div>

                </div>

            </div>


            <div class="pago-buttons">

                <button
                    type="button"
                    class="btn-pago-cancelar"
                    onclick="cerrarModalPago()"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn-pago-confirmar"
                >
                    Registrar pago
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
     MODAL EDITAR CLIENTE
========================================================= --}}

<div
    id="modalEditar"
    class="modal-editar"
>

    <div class="modal-editar-contenido">

        <div class="modal-editar-header">

            <div>

                <h3>
                    Editar Cliente
                </h3>

                <p>
                    Modifica los datos del cliente
                </p>

            </div>

            <button
                type="button"
                class="cerrar-editar"
                onclick="cerrarModalEditar()"
            >
                ✕
            </button>

        </div>


        <form
            id="formEditarCliente"
            method="POST"
            action=""
        >

            @csrf

            @method('PUT')


            {{-- RUT --}}

            <div class="editar-form-group">

                <label for="editarRut">
                    RUT
                </label>

                <input
                    type="text"
                    id="editarRut"
                    class="editar-rut"
                    readonly
                >

            </div>


            {{-- NOMBRE --}}

            <div class="editar-form-group">

                <label for="editarNombre">
                    Nombre *
                </label>

                <input
                    type="text"
                    id="editarNombre"
                    name="nombre"
                    maxlength="100"
                    required
                >

            </div>


            {{-- TELÉFONO --}}

            <div class="editar-form-group">

                <label for="editarTelefono">
                    Teléfono
                </label>

                <input
                    type="text"
                    id="editarTelefono"
                    name="telefono"
                    maxlength="20"
                    placeholder="+56 9 1234 5678"
                >

            </div>


            <div class="editar-buttons">

                <button
                    type="button"
                    class="btn-editar-cancelar"
                    onclick="cerrarModalEditar()"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn-editar-guardar"
                >
                    Guardar cambios
                </button>

            </div>

        </form>

    </div>

</div>


<script>

/*
 * =========================================================
 * BUSCADOR
 * =========================================================
 */

document.addEventListener('DOMContentLoaded', function () {

    const buscador =
        document.getElementById('buscadorClientes');

    const tablaDeudas =
        document.getElementById('tablaDeudas');

    const tablaPagados =
        document.getElementById('tablaPagados');

    const sinResultadosDeudas =
        document.getElementById('sinResultadosDeudas');

    const sinResultadosPagados =
        document.getElementById('sinResultadosPagados');


    if (!buscador) {
        return;
    }


    buscador.addEventListener('input', function () {

        const texto =
            this.value.toLowerCase().trim();


        if (tablaDeudas) {

            const filas =
                tablaDeudas.querySelectorAll(
                    'tr.fila-cliente'
                );

            let encontrados = 0;


            filas.forEach(function (fila) {

                const nombre =
                    fila.querySelector(
                        '.cliente-nombre'
                    )
                    ?.textContent
                    .toLowerCase()
                    .trim() || '';


                const telefono =
                    fila.querySelector(
                        '.cliente-telefono'
                    )
                    ?.textContent
                    .toLowerCase()
                    .trim() || '';


                const coincide =
                    nombre.includes(texto) ||
                    telefono.includes(texto);


                fila.style.display =
                    coincide ? '' : 'none';


                if (coincide) {
                    encontrados++;
                }

            });


            if (sinResultadosDeudas) {

                sinResultadosDeudas.style.display =
                    texto !== '' && encontrados === 0
                        ? ''
                        : 'none';

            }

        }


        if (tablaPagados) {

            const filas =
                tablaPagados.querySelectorAll(
                    'tr.fila-cliente'
                );

            let encontrados = 0;


            filas.forEach(function (fila) {

                const nombre =
                    fila.querySelector(
                        '.cliente-nombre'
                    )
                    ?.textContent
                    .toLowerCase()
                    .trim() || '';


                const telefono =
                    fila.querySelector(
                        '.cliente-telefono'
                    )
                    ?.textContent
                    .toLowerCase()
                    .trim() || '';


                const coincide =
                    nombre.includes(texto) ||
                    telefono.includes(texto);


                fila.style.display =
                    coincide ? '' : 'none';


                if (coincide) {
                    encontrados++;
                }

            });


            if (sinResultadosPagados) {

                sinResultadosPagados.style.display =
                    texto !== '' && encontrados === 0
                        ? ''
                        : 'none';

            }

        }

    });

});


/*
 * =========================================================
 * FORMULARIO NUEVO CLIENTE
 * =========================================================
 */

function toggleFormularioCliente()
{
    const formulario =
        document.getElementById(
            'formularioCliente'
        );


    if (!formulario) {
        return;
    }


    if (
        formulario.style.display === 'none' ||
        formulario.style.display === ''
    ) {

        formulario.style.display = 'block';

    } else {

        formulario.style.display = 'none';

    }
}


/*
 * =========================================================
 * CONFIRMAR ELIMINACIÓN
 * =========================================================
 */

function confirmarEliminacionCliente(nombre)
{
    return confirm(
        '¿Está seguro de eliminar al cliente "' +
        nombre +
        '"?\n\n' +
        'El cliente dejará de aparecer en el listado, ' +
        'pero su historial de ventas se conservará.'
    );
}


/*
 * =========================================================
 * ABRIR MODAL EDITAR CLIENTE
 * =========================================================
 */

function abrirModalEditar(
    rutCliente,
    nombre,
    telefono
)
{
    const modal =
        document.getElementById(
            'modalEditar'
        );

    const formulario =
        document.getElementById(
            'formEditarCliente'
        );

    const rut =
        document.getElementById(
            'editarRut'
        );

    const nombreInput =
        document.getElementById(
            'editarNombre'
        );

    const telefonoInput =
        document.getElementById(
            'editarTelefono'
        );


    /*
     * Mostrar datos actuales.
     */

    rut.value =
        rutCliente;

    nombreInput.value =
        nombre || '';

    telefonoInput.value =
        telefono || '';


    /*
     * Generar URL de actualización.
     */

    formulario.action =
        `/clientes/${encodeURIComponent(rutCliente)}`;


    /*
     * Mostrar modal.
     */

    modal.style.display =
        'flex';


    /*
     * Enfocar nombre.
     */

    setTimeout(function () {

        nombreInput.focus();

    }, 100);
}


/*
 * =========================================================
 * CERRAR MODAL EDITAR
 * =========================================================
 */

function cerrarModalEditar()
{
    const modal =
        document.getElementById(
            'modalEditar'
        );


    if (modal) {

        modal.style.display =
            'none';

    }
}


/*
 * =========================================================
 * HISTORIAL
 * =========================================================
 */

async function verHistorialCliente(rutCliente)
{
    const modal =
        document.getElementById(
            'modalHistorial'
        );

    const nombre =
        document.getElementById(
            'historialClienteNombre'
        );

    const rut =
        document.getElementById(
            'historialClienteRut'
        );

    const telefono =
        document.getElementById(
            'historialClienteTelefono'
        );

    const cantidadVentas =
        document.getElementById(
            'historialCantidadVentas'
        );

    const contenido =
        document.getElementById(
            'historialContenido'
        );


    modal.style.display =
        'flex';


    nombre.textContent =
        'Cargando...';

    rut.textContent =
        '-';

    telefono.textContent =
        '-';

    cantidadVentas.textContent =
        '0';


    contenido.innerHTML = `
        <p class="historial-cargando">
            Cargando historial...
        </p>
    `;


    try {

        const response =
            await fetch(
                `/clientes/${encodeURIComponent(rutCliente)}/historial`
            );


        if (!response.ok) {

            throw new Error(
                'No fue posible obtener el historial.'
            );

        }


        const data =
            await response.json();


        nombre.textContent =
            data.cliente.nombre;

        rut.textContent =
            data.cliente.rutCliente;

        telefono.textContent =
            data.cliente.telefono ||
            'Sin teléfono';

        cantidadVentas.textContent =
            data.ventas.length;


        if (data.ventas.length === 0) {

            contenido.innerHTML = `
                <div class="historial-vacio">
                    Este cliente no tiene ventas registradas.
                </div>
            `;

            return;

        }


        contenido.innerHTML =
            data.ventas.map(function (venta) {

                const fecha =
                    new Date(venta.fecha)
                    .toLocaleDateString('es-CL');


                const total =
                    Number(venta.total)
                    .toLocaleString('es-CL');


                const pagado =
                    Number(venta.totalPagado)
                    .toLocaleString('es-CL');


                const saldo =
                    Number(venta.saldoPendiente)
                    .toLocaleString('es-CL');


                let claseEstado =
                    'estado-pagada';


                if (
                    venta.estado === 'Pendiente'
                ) {

                    claseEstado =
                        'estado-pendiente';

                } else if (
                    venta.estado === 'Pago parcial'
                ) {

                    claseEstado =
                        'estado-parcial';

                }


                let detallesHTML = '';


                if (
                    venta.detalles &&
                    venta.detalles.length > 0
                ) {

                    detallesHTML =
                        venta.detalles.map(
                            function (detalle) {

                                const precio =
                                    Number(
                                        detalle.precioCobrado
                                    )
                                    .toLocaleString('es-CL');


                                return `
                                    <div class="historial-producto">

                                        <span>
                                            ${detalle.codigoProducto}
                                        </span>

                                        <span>
                                            x${detalle.cantidad}
                                        </span>

                                        <strong>
                                            $${precio}
                                        </strong>

                                    </div>
                                `;

                            }
                        ).join('');

                } else {

                    detallesHTML = `
                        <p class="sin-detalles">
                            Sin detalle disponible
                        </p>
                    `;

                }


                return `
                    <div class="historial-venta">

                        <div class="historial-venta-header">

                            <div>

                                <strong>
                                    Venta #${venta.idSalida}
                                </strong>

                                <span>
                                    ${fecha}
                                </span>

                            </div>

                            <span class="${claseEstado}">
                                ${venta.estado}
                            </span>

                        </div>


                        <div class="historial-montos">

                            <div>

                                <span>
                                    Total
                                </span>

                                <strong>
                                    $${total}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Pagado
                                </span>

                                <strong class="monto-pagado">
                                    $${pagado}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Saldo
                                </span>

                                <strong class="monto-saldo">
                                    $${saldo}
                                </strong>

                            </div>

                        </div>


                        <div class="historial-productos">

                            <h5>
                                Productos
                            </h5>

                            ${detallesHTML}

                        </div>

                    </div>
                `;

            }).join('');


    } catch (error) {

        console.error(error);

        contenido.innerHTML = `
            <div class="historial-error">
                No fue posible cargar el historial del cliente.
            </div>
        `;

    }
}


/*
 * =========================================================
 * CERRAR HISTORIAL
 * =========================================================
 */

function cerrarHistorial()
{
    const modal =
        document.getElementById(
            'modalHistorial'
        );


    if (modal) {

        modal.style.display =
            'none';

    }
}


/*
 * =========================================================
 * ABRIR MODAL DE PAGO
 * =========================================================
 */

async function abrirModalPago(rutCliente)
{
    const modal =
        document.getElementById(
            'modalPago'
        );

    const selectVenta =
        document.getElementById(
            'pagoVenta'
        );

    const nombreCliente =
        document.getElementById(
            'pagoClienteNombre'
        );

    const resumen =
        document.getElementById(
            'pagoResumen'
        );

    const error =
        document.getElementById(
            'pagoError'
        );


    modal.style.display =
        'flex';


    selectVenta.innerHTML = `
        <option value="">
            Cargando ventas...
        </option>
    `;

    selectVenta.disabled =
        true;

    resumen.style.display =
        'none';

    error.style.display =
        'none';

    error.textContent =
        '';

    document.getElementById(
        'montoPagado'
    ).value =
        '';


    try {

        const response =
            await fetch(
                `/clientes/${encodeURIComponent(rutCliente)}/historial`
            );


        if (!response.ok) {

            throw new Error(
                'No fue posible obtener las ventas del cliente.'
            );

        }


        const data =
            await response.json();


        nombreCliente.textContent =
            data.cliente.nombre;


        const ventasPendientes =
            data.ventas.filter(function (venta) {

                return Number(
                    venta.saldoPendiente
                ) > 0;

            });


        if (ventasPendientes.length === 0) {

            selectVenta.innerHTML = `
                <option value="">
                    Este cliente no tiene deudas pendientes
                </option>
            `;

            selectVenta.disabled =
                true;

            error.textContent =
                'No existen ventas con saldo pendiente para este cliente.';

            error.style.display =
                'block';

            return;

        }


        selectVenta.innerHTML = `
            <option value="">
                Seleccione una venta
            </option>
        `;


        ventasPendientes.forEach(function (venta) {

            const option =
                document.createElement('option');


            option.value =
                venta.idSalida;


            option.textContent =
                `Venta #${venta.idSalida} - ${formatearMoneda(venta.saldoPendiente)} pendiente`;


            option.dataset.total =
                venta.total;


            option.dataset.pagado =
                venta.totalPagado;


            option.dataset.saldo =
                venta.saldoPendiente;


            selectVenta.appendChild(option);

        });


        selectVenta.disabled =
            false;


        selectVenta.onchange =
            function () {

                actualizarResumenPago();

            };


    } catch (errorFetch) {

        console.error(errorFetch);

        selectVenta.innerHTML = `
            <option value="">
                Error al cargar las ventas
            </option>
        `;

        selectVenta.disabled =
            true;

        error.textContent =
            'No fue posible cargar las ventas del cliente.';

        error.style.display =
            'block';
    }
}


/*
 * =========================================================
 * ACTUALIZAR RESUMEN PAGO
 * =========================================================
 */

function actualizarResumenPago()
{
    const select =
        document.getElementById(
            'pagoVenta'
        );

    const option =
        select.options[
            select.selectedIndex
        ];

    const resumen =
        document.getElementById(
            'pagoResumen'
        );

    const monto =
        document.getElementById(
            'montoPagado'
        );


    if (
        !option ||
        !option.value
    ) {

        resumen.style.display =
            'none';

        monto.removeAttribute(
            'max'
        );

        return;
    }


    const total =
        Number(
            option.dataset.total || 0
        );

    const pagado =
        Number(
            option.dataset.pagado || 0
        );

    const saldo =
        Number(
            option.dataset.saldo || 0
        );


    document.getElementById(
        'pagoTotalVenta'
    ).textContent =
        formatearMoneda(total);


    document.getElementById(
        'pagoTotalPagado'
    ).textContent =
        formatearMoneda(pagado);


    document.getElementById(
        'pagoSaldoPendiente'
    ).textContent =
        formatearMoneda(saldo);


    monto.max =
        saldo;

    monto.value =
        '';


    resumen.style.display =
        'grid';
}


/*
 * =========================================================
 * FORMATEAR MONEDA
 * =========================================================
 */

function formatearMoneda(valor)
{
    return '$' +
        Number(valor || 0)
        .toLocaleString('es-CL');
}


/*
 * =========================================================
 * VALIDAR PAGO
 * =========================================================
 */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const form =
            document.getElementById(
                'formRegistrarPago'
            );


        if (!form) {
            return;
        }


        form.addEventListener(
            'submit',
            function (event) {

                const select =
                    document.getElementById(
                        'pagoVenta'
                    );

                const monto =
                    Number(
                        document.getElementById(
                            'montoPagado'
                        ).value
                    );


                if (!select.value) {

                    event.preventDefault();

                    mostrarErrorPago(
                        'Debe seleccionar una venta.'
                    );

                    return;
                }


                const option =
                    select.options[
                        select.selectedIndex
                    ];


                const saldo =
                    Number(
                        option.dataset.saldo || 0
                    );


                if (monto <= 0) {

                    event.preventDefault();

                    mostrarErrorPago(
                        'El monto debe ser mayor que cero.'
                    );

                    return;
                }


                if (monto > saldo) {

                    event.preventDefault();

                    mostrarErrorPago(
                        'El monto no puede superar el saldo pendiente.'
                    );

                    return;
                }


                const metodo =
                    document.querySelector(
                        'input[name="idMetodo"]:checked'
                    );


                if (!metodo) {

                    event.preventDefault();

                    mostrarErrorPago(
                        'Debe seleccionar un método de pago.'
                    );

                    return;
                }

            }
        );

    }
);


/*
 * =========================================================
 * MOSTRAR ERROR PAGO
 * =========================================================
 */

function mostrarErrorPago(mensaje)
{
    const error =
        document.getElementById(
            'pagoError'
        );


    error.textContent =
        mensaje;

    error.style.display =
        'block';
}


/*
 * =========================================================
 * CERRAR MODAL PAGO
 * =========================================================
 */

function cerrarModalPago()
{
    const modal =
        document.getElementById(
            'modalPago'
        );


    modal.style.display =
        'none';


    const form =
        document.getElementById(
            'formRegistrarPago'
        );


    if (form) {
        form.reset();
    }


    document.getElementById(
        'pagoResumen'
    ).style.display =
        'none';


    document.getElementById(
        'pagoError'
    ).style.display =
        'none';
}


/*
 * =========================================================
 * CERRAR MODALES CON ESC
 * =========================================================
 */

document.addEventListener(
    'keydown',
    function (event) {

        if (event.key === 'Escape') {

            cerrarHistorial();

            cerrarModalPago();

            cerrarModalEditar();

        }

    }
);


/*
 * =========================================================
 * CERRAR HISTORIAL AL HACER CLICK FUERA
 * =========================================================
 */

document.addEventListener(
    'click',
    function (event) {

        const modal =
            document.getElementById(
                'modalHistorial'
            );


        if (
            event.target === modal
        ) {

            cerrarHistorial();

        }

    }
);


/*
 * =========================================================
 * CERRAR PAGO AL HACER CLICK FUERA
 * =========================================================
 */

document.addEventListener(
    'click',
    function (event) {

        const modal =
            document.getElementById(
                'modalPago'
            );


        if (
            event.target === modal
        ) {

            cerrarModalPago();

        }

    }
);


/*
 * =========================================================
 * CERRAR EDITAR AL HACER CLICK FUERA
 * =========================================================
 */

document.addEventListener(
    'click',
    function (event) {

        const modal =
            document.getElementById(
                'modalEditar'
            );


        if (
            event.target === modal
        ) {

            cerrarModalEditar();

        }

    }
);

</script>

@endsection