@extends('layouts.app')

@section('title', 'Proveedores')

@push('css')
    @vite(['resources/css/proveedores.css'])
@endpush


@section('content')

<div class="proveedores-container">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <div class="proveedores-header">

        <div class="proveedores-title">

            <h2>Proveedores</h2>

            <p>
                Gestiona los proveedores y sus productos asociados
            </p>

        </div>


        <div class="proveedores-actions">

            <div class="buscador-proveedor">

                <svg
                    width="19"
                    height="19"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>

                <input
                    type="text"
                    id="buscarProveedor"
                    placeholder="Buscar proveedor..."
                    autocomplete="off"
                >

            </div>


            <button
                type="button"
                class="btn-nuevo-proveedor"
                id="btnNuevoProveedor"
            >

                <span class="btn-plus">+</span>

                Nuevo Proveedor

            </button>

        </div>

    </div>


    {{-- =====================================================
         TARJETAS DE RESUMEN
    ====================================================== --}}

    <div class="proveedores-cards">

        <div class="proveedor-card">

            <div class="card-info">

                <span>Total de Proveedores</span>

                <strong>
                    {{ $totalProveedores }}
                </strong>

            </div>

            <div class="card-icon proveedores-icon">

                <svg width="23" height="23" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                    <path d="M8 8h8"/>
                    <path d="M8 12h8"/>
                    <path d="M8 16h5"/>
                </svg>

            </div>

        </div>


        <div class="proveedor-card">

            <div class="card-info">

                <span>Productos con Proveedor</span>

                <strong>
                    {{ $cantidadProductosConProveedor }}
                </strong>

            </div>

            <div class="card-icon productos-icon">

                <svg width="23" height="23" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m21 8-9-5-9 5 9 5 9-5Z"/>
                    <path d="M3 8v8l9 5 9-5V8"/>
                    <path d="M12 13v8"/>
                </svg>

            </div>

        </div>


        <div class="proveedor-card">

            <div class="card-info">

                <span>Productos sin Proveedor</span>

                <strong>
                    {{ $productosSinProveedor }}
                </strong>

            </div>

            <div class="card-icon alerta-icon">

                <svg width="23" height="23" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M12 3 2.5 20h19L12 3Z"/>
                    <path d="M12 9v5"/>
                    <path d="M12 17h.01"/>
                </svg>

            </div>

        </div>

    </div>


    {{-- =====================================================
         SECCIÓN PROVEEDORES
    ====================================================== --}}

    <div class="proveedores-section">

        <div class="section-title">

            <h3>Proveedores registrados</h3>

            <p>
                Lista de proveedores actualmente registrados
            </p>

        </div>


        <div class="proveedores-table-container">

            <table class="proveedores-table">

                <thead>

                    <tr>

                        <th>Proveedor</th>

                        <th>Teléfono</th>

                        <th>Correo</th>

                        <th>Productos</th>

                        <th>Acciones</th>

                    </tr>

                </thead>


                <tbody id="tablaProveedores">

                    @forelse($proveedores as $proveedor)

                        {{-- FILA PRINCIPAL --}}

                        <tr
                            class="proveedor-row"
                            data-nombre="{{ strtolower($proveedor->nombre) }}"
                            data-telefono="{{ strtolower($proveedor->telefono ?? '') }}"
                            data-correo="{{ strtolower($proveedor->correo ?? '') }}"
                        >

                            <td>

                                <div class="proveedor-nombre">

                                    <div class="proveedor-avatar">

                                        {{ strtoupper(substr($proveedor->nombre, 0, 1)) }}

                                    </div>


                                    <div class="proveedor-datos">

                                        <strong>
                                            {{ $proveedor->nombre }}
                                        </strong>

                                        <small>
                                            ID #{{ $proveedor->idProveedor }}
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <span class="dato-proveedor">

                                    @if($proveedor->telefono)

                                        <svg
                                            width="15"
                                            height="15"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2
                                                19.79 19.79 0 0 1-8.63-3.07
                                                19.5 19.5 0 0 1-6-6
                                                19.79 19.79 0 0 1-3.07-8.67
                                                A2 2 0 0 1 4.11 2h3a2 2 0 0 1
                                                2 1.72 12.84 12.84 0 0 0 .7 2.81
                                                2 2 0 0 1-.45 2.11L8.09 9.91
                                                a16 16 0 0 0 6 6l1.27-1.27
                                                a2 2 0 0 1 2.11-.45
                                                12.84 12.84 0 0 0 2.81.7
                                                A2 2 0 0 1 22 16.92z"
                                            />
                                        </svg>

                                        {{ $proveedor->telefono }}

                                    @else

                                        Sin teléfono

                                    @endif

                                </span>

                            </td>


                            <td>

                                <span class="dato-proveedor">

                                    {{ $proveedor->correo ?? 'Sin correo' }}

                                </span>

                            </td>


                            <td>

                                <span class="productos-badge">

                                    {{ $proveedor->productos_count }}

                                    {{ $proveedor->productos_count == 1
                                        ? 'producto'
                                        : 'productos' }}

                                </span>

                            </td>


                            {{-- ACCIONES --}}

                            <td>

                                <div class="acciones-proveedor">


                                    {{-- FLECHA --}}

                                    <button
                                        type="button"
                                        class="btn-accion-proveedor btn-expandir"
                                        title="Ver productos"
                                        aria-expanded="false"
                                    >

                                        <svg
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <polyline points="9 18 15 12 9 6"/>
                                        </svg>

                                    </button>


                                    {{-- EDITAR --}}

                                    <button
                                        type="button"
                                        class="btn-accion-proveedor btn-editar"
                                        title="Editar proveedor"

                                        data-id="{{ $proveedor->idProveedor }}"
                                        data-nombre="{{ $proveedor->nombre }}"
                                        data-telefono="{{ $proveedor->telefono }}"
                                        data-correo="{{ $proveedor->correo }}"
                                    >

                                        <svg
                                            width="17"
                                            height="17"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0
                                                0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>

                                    </button>


                                    {{-- ELIMINAR --}}

                                    <button
                                        type="button"
                                        class="btn-accion-proveedor btn-eliminar"
                                        title="Eliminar proveedor"

                                        data-id="{{ $proveedor->idProveedor }}"
                                        data-nombre="{{ $proveedor->nombre }}"
                                        data-productos="{{ $proveedor->productos_count }}"
                                        data-delete-url="{{ route('proveedores.destroy', $proveedor->idProveedor) }}"
                                    >

                                        <svg
                                            width="17"
                                            height="17"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 14H6L5 6"/>
                                            <path d="M10 11v5"/>
                                            <path d="M14 11v5"/>
                                        </svg>

                                    </button>

                                </div>

                            </td>

                        </tr>


                        {{-- FILA DE DETALLES --}}

                        <tr class="productos-detalle-row">

                            <td colspan="5">

                                <div class="productos-detalle">

                                    <div class="productos-detalle-header">

                                        <div>

                                            <strong>
                                                Productos asociados
                                            </strong>

                                            <span>
                                                {{ $proveedor->productos_count }}
                                                {{ $proveedor->productos_count == 1
                                                    ? 'producto'
                                                    : 'productos' }}
                                            </span>

                                        </div>

                                    </div>


                                    @if($proveedor->productos_count > 0)

                                        <div class="productos-grid">

                                            @foreach($proveedor->productos as $producto)

                                                <div class="producto-card">

                                                    <div class="producto-card-main">

                                                        <div>

                                                            <strong>
                                                                {{ $producto->nombre }}
                                                            </strong>

                                                            <small>
                                                                {{ $producto->descripcion ?? 'Sin descripción' }}
                                                            </small>

                                                        </div>


                                                        <strong class="producto-precio">

                                                            ${{ number_format(
                                                                $producto->ultimoPrecioCompra ?? 0,
                                                                0,
                                                                ',',
                                                                '.'
                                                            ) }}

                                                        </strong>

                                                    </div>


                                                    <div class="producto-card-meta">

                                                        <span>
                                                            Código:
                                                            {{ $producto->codigo }}
                                                        </span>

                                                        <span>
                                                            Cantidad:
                                                            {{ $producto->cantidadIngresada ?? 0 }}
                                                        </span>

                                                        <span>

                                                            Vence:

                                                            @if($producto->ultimaFechaVencimiento)

                                                                {{ \Carbon\Carbon::parse(
                                                                    $producto->ultimaFechaVencimiento
                                                                )->format('d-m-Y') }}

                                                            @else

                                                                Sin registro

                                                            @endif

                                                        </span>

                                                    </div>

                                                </div>

                                            @endforeach

                                        </div>

                                    @else

                                        <div class="productos-vacios">

                                            <strong>
                                                Sin productos asociados
                                            </strong>

                                            <p>
                                                Este proveedor todavía no tiene productos registrados.
                                            </p>

                                        </div>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5">

                                <div class="tabla-vacia">

                                    <div class="tabla-vacia-icon">
                                        📦
                                    </div>

                                    <strong>
                                        No hay proveedores registrados
                                    </strong>

                                    <p>
                                        Comienza agregando un nuevo proveedor.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse


                    {{-- SIN RESULTADOS DE BÚSQUEDA --}}

                    <tr
                        id="sinResultadosBusqueda"
                        class="fila-oculta"
                    >

                        <td colspan="5">

                            <div class="tabla-vacia">

                                <div class="tabla-vacia-icon">
                                    🔍
                                </div>

                                <strong>
                                    No se encontraron proveedores
                                </strong>

                                <p>
                                    Intenta con otro nombre, teléfono o correo.
                                </p>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =========================================================
     MODAL NUEVO PROVEEDOR
========================================================= --}}

<div
    class="modal-overlay"
    id="modalNuevoProveedor"
    aria-hidden="true"
>

    <div class="modal-proveedor">

        <div class="modal-header">

            <div>

                <h3>Nuevo Proveedor</h3>

                <p>
                    Registra un nuevo proveedor
                </p>

            </div>

            <button
                type="button"
                class="btn-cerrar-modal"
                data-cerrar-modal="modalNuevoProveedor"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            action="{{ route('proveedores.store') }}"
            id="formNuevoProveedor"
        >

            @csrf

            <div class="form-proveedor">

                <div class="campo-proveedor campo-completo">

                    <label for="nombre">
                        Nombre <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Ej: Distribuidora del Norte"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="campo-proveedor">

                    <label for="telefono">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        placeholder="Ej: +56 9 1234 5678"
                        maxlength="20"
                    >

                </div>


                <div class="campo-proveedor">

                    <label for="correo">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="proveedor@correo.cl"
                        maxlength="100"
                    >

                </div>

            </div>


            <div class="modal-buttons">

                <button
                    type="button"
                    class="btn-modal cancelar"
                    data-cerrar-modal="modalNuevoProveedor"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn-modal guardar"
                >
                    Guardar Proveedor
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
     MODAL EDITAR PROVEEDOR
========================================================= --}}

<div
    class="modal-overlay"
    id="modalEditarProveedor"
    aria-hidden="true"
>

    <div class="modal-proveedor">

        <div class="modal-header">

            <div>

                <h3>Editar Proveedor</h3>

                <p>
                    Modifica la información del proveedor
                </p>

            </div>

            <button
                type="button"
                class="btn-cerrar-modal"
                data-cerrar-modal="modalEditarProveedor"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            id="formEditarProveedor"
        >

            @csrf

            @method('PUT')

            <div class="form-proveedor">

                <div class="campo-proveedor campo-completo">

                    <label for="editarNombre">
                        Nombre <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="editarNombre"
                        name="nombre"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="campo-proveedor">

                    <label for="editarTelefono">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        id="editarTelefono"
                        name="telefono"
                        maxlength="20"
                    >

                </div>


                <div class="campo-proveedor">

                    <label for="editarCorreo">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="editarCorreo"
                        name="correo"
                        maxlength="100"
                    >

                </div>

            </div>


            <div class="modal-buttons">

                <button
                    type="button"
                    class="btn-modal cancelar"
                    data-cerrar-modal="modalEditarProveedor"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn-modal guardar"
                >
                    Guardar Cambios
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
     MODAL ELIMINAR
========================================================= --}}

<div
    class="modal-overlay"
    id="modalEliminarProveedor"
    aria-hidden="true"
>

    <div class="modal-confirmacion">

        <div
            class="confirmacion-icon"
            id="confirmacionIcon"
        >
            !
        </div>


        <h3 id="confirmacionTitulo">
            ¿Eliminar proveedor?
        </h3>


        <p id="confirmacionMensaje">
            ¿Estás seguro de que deseas eliminar este proveedor?
        </p>


        <div class="modal-buttons confirmacion-buttons">

            <button
                type="button"
                class="btn-modal cancelar"
                data-cerrar-modal="modalEliminarProveedor"
            >
                Cancelar
            </button>


            <form
                method="POST"
                id="formEliminarProveedor"
            >

                @csrf

                @method('DELETE')

                <button
                    type="submit"
                    class="btn-modal eliminar-confirmar"
                    id="btnConfirmarEliminar"
                >
                    Eliminar
                </button>

            </form>

        </div>

    </div>

</div>


@push('scripts')

    @vite(['resources/js/proveedores.js'])

@endpush

@endsection