@extends('layouts.app')

@section('title', 'Clientes')

@push('css')
    @vite(['resources/css/clientes.css'])
@endpush


@push('scripts')
    @vite(['resources/js/clientes.js'])
@endpush

@section('content')
<div id="rutasClientes" hidden
    data-update="{{ route('clientes.update', ['rutCliente' => '__RUT__']) }}"
    data-historial="{{ route('clientes.historial', ['rutCliente' => '__RUT__']) }}"></div>

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
            action="{{ route('clientes.registrarPago', ['idSalida' => '__ID_SALIDA__']) }}"
            data-action-template="{{ route('clientes.registrarPago', ['idSalida' => '__ID_SALIDA__']) }}"
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




@endsection
