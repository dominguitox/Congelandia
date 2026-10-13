@extends('layouts.app')

@section('title', 'Categorías')

@section('content')

<div class="categorias-page">

    {{-- =====================================================
         ENCABEZADO
         ===================================================== --}}

    <div class="categorias-header">

        <div class="categorias-title">
            <h1>Categorías</h1>

            <p>
                Gestiona las categorías de tus productos
            </p>
        </div>


        <div class="categorias-header-actions">

            <div class="categoria-search">

                <span class="search-icon">⌕</span>

                <input
                    type="text"
                    id="buscarCategoria"
                    placeholder="Buscar categoría..."
                    autocomplete="off"
                >

            </div>


            <button
                type="button"
                id="btnNuevaCategoria"
                class="btn-primary"
            >
                <span class="btn-plus">+</span>
                Nueva Categoría
            </button>

        </div>

    </div>


    {{-- =====================================================
         ESTADÍSTICAS
         ===================================================== --}}

    <div class="categoria-stats">

        <div class="stat-card">

            <div class="stat-icon blue">
                ◇
            </div>

            <div class="stat-content">

                <span>
                    Total Categorías
                </span>

                <strong>
                    {{ $totalCategorias }}
                </strong>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon blue">
                □
            </div>

            <div class="stat-content">

                <span>
                    Productos con categoría
                </span>

                <strong>
                    {{ $productosConCategoria }}
                </strong>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon orange">
                ◇
            </div>

            <div class="stat-content">

                <span>
                    Categorías en uso
                </span>

                <strong>
                    {{ $categoriasEnUso }}
                </strong>

            </div>

        </div>

    </div>


    {{-- =====================================================
         CONTENIDO
         ===================================================== --}}

    <div class="categorias-layout">


        {{-- =================================================
             LISTA
             ================================================= --}}

        <section class="categorias-table-card">

            <div class="table-header">

                <div>
                    Categoría
                </div>

                <div>
                    Productos
                </div>

                <div>
                    Acciones
                </div>

            </div>


            <div
                class="categorias-list"
                id="categoriasList"
            >

                @forelse($categorias as $categoria)

                    <div
                        class="categoria-row"
                        data-id="{{ $categoria->idCategoria }}"
                        data-nombre="{{ $categoria->nombre }}"
                        data-productos="{{ $categoria->productos->count() }}"
                    >

                        {{-- Categoría --}}

                        <div class="categoria-info">

                            <div class="categoria-icon">
                                ◇
                            </div>

                            <div class="categoria-name">

                                <strong>
                                    {{ $categoria->nombre }}
                                </strong>

                            </div>

                        </div>


                        {{-- Productos --}}

                        <div class="categoria-productos">

                            <span class="productos-badge">

                                {{ $categoria->productos->count() }}

                                @if($categoria->productos->count() === 1)
                                    producto
                                @else
                                    productos
                                @endif

                            </span>

                        </div>


                        {{-- Acciones --}}

                        <div class="categoria-actions">

                            <button
                                type="button"
                                class="action-btn action-expand"
                                data-action="expand"
                                data-id="{{ $categoria->idCategoria }}"
                                data-nombre="{{ $categoria->nombre }}"
                                title="Ver productos"
                            >
                                <span class="arrow-icon">›</span>
                            </button>


                            <button
                                type="button"
                                class="action-btn action-edit"
                                data-action="edit"
                                data-id="{{ $categoria->idCategoria }}"
                                data-nombre="{{ $categoria->nombre }}"
                                title="Editar categoría"
                            >
                                ✎
                            </button>


                            <button
                                type="button"
                                class="action-btn action-delete"
                                data-action="delete"
                                data-id="{{ $categoria->idCategoria }}"
                                data-nombre="{{ $categoria->nombre }}"
                                data-productos="{{ $categoria->productos->count() }}"
                                title="Eliminar categoría"
                            >
                                🗑
                            </button>

                        </div>


                        {{-- Productos para el panel lateral --}}

                        <template id="productos-{{ $categoria->idCategoria }}">

                            @forelse($categoria->productos as $producto)

                                <div class="producto-detail-card">

                                    <div class="producto-detail-info">

                                        <strong>
                                            {{ $producto->nombre }}
                                        </strong>

                                        <span>
                                            Código:
                                            {{ $producto->codigo }}
                                        </span>

                                    </div>

                                </div>

                            @empty

                                <div class="productos-empty">
                                    Esta categoría no tiene productos asociados.
                                </div>

                            @endforelse

                        </template>

                    </div>

                @empty

                    <div class="categorias-empty">

                        <div class="empty-icon">
                            ◇
                        </div>

                        <strong>
                            No hay categorías registradas
                        </strong>

                        <span>
                            Agrega una categoría para comenzar.
                        </span>

                    </div>

                @endforelse

            </div>


            <div
                class="search-empty"
                id="searchEmpty"
            >

                <strong>
                    No se encontraron categorías
                </strong>

                <span>
                    Prueba con otro nombre.
                </span>

            </div>

        </section>


        {{-- =================================================
             PANEL DERECHO
             ================================================= --}}

        <aside
            id="categoriaDetailPanel"
            class="categoria-detail-panel is-hidden"
            aria-hidden="true"
        >

            <div class="detail-panel-header">

                <div class="detail-title-wrapper">

                    <div class="detail-category-icon">
                        ◇
                    </div>

                    <h2 id="detailCategoriaNombre">
                        Categoría
                    </h2>

                </div>


                <button
                    type="button"
                    id="btnCerrarDetalle"
                    class="detail-close"
                >
                    ×
                </button>

            </div>


            <div class="detail-panel-content">

                <div
                    id="detailProductos"
                    class="detail-products"
                ></div>

            </div>

        </aside>

    </div>

</div>


{{-- =========================================================
     MODAL NUEVA CATEGORÍA
     AHORA ES <dialog>
     ========================================================= --}}

<dialog
    id="modalNuevaCategoria"
    class="categoria-dialog"
>

    <div class="dialog-header">

        <h2>
            Nueva Categoría
        </h2>

        <button
            type="button"
            class="dialog-close"
            data-close-dialog="modalNuevaCategoria"
            aria-label="Cerrar"
        >
            ×
        </button>

    </div>


    <form
        method="POST"
        action="{{ route('categorias.store') }}"
    >

        @csrf

        <div class="dialog-body">

            <label for="nombreNuevaCategoria">
                Nombre de la categoría
            </label>

            <input
                type="text"
                id="nombreNuevaCategoria"
                name="nombre"
                maxlength="100"
                placeholder="Ej: Bebidas"
                required
                autocomplete="off"
            >

        </div>


        <div class="dialog-footer">

            <button
                type="button"
                class="btn-dialog-secondary"
                data-close-dialog="modalNuevaCategoria"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="btn-dialog-primary"
            >
                Guardar
            </button>

        </div>

    </form>

</dialog>


{{-- =========================================================
     MODAL EDITAR
     ========================================================= --}}

<dialog
    id="modalEditarCategoria"
    class="categoria-dialog"
>

    <div class="dialog-header">

        <h2>
            Editar Categoría
        </h2>

        <button
            type="button"
            class="dialog-close"
            data-close-dialog="modalEditarCategoria"
            aria-label="Cerrar"
        >
            ×
        </button>

    </div>


    <form
        method="POST"
        id="formEditarCategoria"
    >

        @csrf

        @method('PUT')

        <div class="dialog-body">

            <label for="nombreEditarCategoria">
                Nombre de la categoría
            </label>

            <input
                type="text"
                id="nombreEditarCategoria"
                name="nombre"
                maxlength="100"
                required
                autocomplete="off"
            >

        </div>


        <div class="dialog-footer">

            <button
                type="button"
                class="btn-dialog-secondary"
                data-close-dialog="modalEditarCategoria"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="btn-dialog-primary"
            >
                Guardar
            </button>

        </div>

    </form>

</dialog>


{{-- =========================================================
     MODAL CONFIRMAR ELIMINACIÓN
     ========================================================= --}}

<dialog
    id="modalEliminarCategoria"
    class="categoria-dialog categoria-dialog-confirm"
>

    <div class="confirm-icon">
        !
    </div>


    <div class="confirm-content">

        <h2>
            Eliminar categoría
        </h2>

        <p id="eliminarMensaje">
            ¿Estás seguro de eliminar esta categoría?
        </p>

    </div>


    <form
        method="POST"
        id="formEliminarCategoria"
    >

        @csrf

        @method('DELETE')

        <div class="dialog-footer">

            <button
                type="button"
                class="btn-dialog-secondary"
                data-close-dialog="modalEliminarCategoria"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="btn-danger"
            >
                Eliminar
            </button>

        </div>

    </form>

</dialog>


{{-- =========================================================
     MODAL ERROR: TIENE PRODUCTOS
     ========================================================= --}}

<dialog
    id="modalErrorCategoria"
    class="categoria-dialog categoria-dialog-confirm"
>

    <div class="confirm-icon error">
        !
    </div>


    <div class="confirm-content">

        <h2>
            No se puede eliminar
        </h2>

        <p>
            La categoría
            <strong id="errorCategoriaNombre"></strong>
            tiene productos asociados.
        </p>

        <p class="confirm-help">
            Debes quitar los productos de esta categoría
            antes de eliminarla.
        </p>

    </div>


    <div class="dialog-footer single-button">

        <button
            type="button"
            class="btn-dialog-primary"
            data-close-dialog="modalErrorCategoria"
        >
            Entendido
        </button>

    </div>

</dialog>


{{-- =========================================================
     MENSAJES
     ========================================================= --}}

@if(session('success'))

    <div
        id="serverToast"
        class="toast-message toast-success"
    >
        {{ session('success') }}
    </div>

@endif


@if(session('error'))

    <div
        id="serverToast"
        class="toast-message toast-error"
    >
        {{ session('error') }}
    </div>

@endif


{{-- =========================================================
     CARGA DIRECTA DEL MÓDULO
     NO DEPENDE DE @stack('scripts')
     ========================================================= --}}

@vite([
    'resources/css/categorias.css',
    'resources/js/categorias.js'
])

@endsection