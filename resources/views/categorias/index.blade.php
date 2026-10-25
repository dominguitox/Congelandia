@extends('layouts.app')

@section('title', 'Categorias')

@push('css')
    @vite(['resources/css/categorias.css'])
@endpush

@section('content')

<div class="page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <div class="page-header">

        <div class="page-title">

            <h1>Categorías</h1>

            <p>Gestiona las categorías de productos y su catálogo</p>

        </div>

        <div class="page-actions">

            <button type="button" class="btn-solid" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <span>+</span> Nueva Categoría
            </button>

        </div>

    </div>


    {{-- =====================================================
         TARJETAS DE RESUMEN
    ====================================================== --}}

    <div class="stats-grid">

        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Total Categorías</span>
                <strong>{{ $estadisticas['total'] }}</strong>
            </div>
            <div class="card-icon proveedores-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 7h18"/>
                    <path d="M3 12h18"/>
                    <path d="M3 17h18"/>
                </svg>
            </div>
        </div>

        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Productos con categoría</span>
                <strong>{{ $estadisticas['productos_categorizados'] }}</strong>
            </div>
            <div class="card-icon stat-icon-ingreso">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m21 8-9-5-9 5 9 5 9-5Z"/>
                    <path d="M3 8v8l9 5 9-5V8"/>
                    <path d="M12 13v8"/>
                </svg>
            </div>
        </div>

        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Productos sin categoría</span>
                <strong>{{ $estadisticas['productos_NoCategorizados'] }}</strong>
            </div>
            <div class="card-icon alerta-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3 2.5 20h19L12 3Z"/>
                    <path d="M12 9v5"/>
                    <path d="M12 17h.01"/>
                </svg>
            </div>
        </div>

        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Categorías sin productos</span>
                <strong>{{ $estadisticas['categorias_uso'] }}</strong>
            </div>
            <div class="card-icon productos-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 8v5"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
        </div>

    </div>


    {{-- =====================================================
         CONTENIDO PRINCIPAL
    ====================================================== --}}

    <div class="categorias-grid">

        {{-- Tabla de categorías --}}
        <div class="panel">

            <div class="panel-head">
                <div>
                    <h3>Categorías registradas</h3>
                    <p>Listado de categorías del catálogo</p>
                </div>
            </div>

            <div class="panel-body panel-body--flush">

                <div class="data-table-wrap">

                    <table class="data-table">

                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th class="text-center">Productos</th>
                                <th class="col-acciones">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($categorias as $categoria)
                                <tr>
                                    <td><span class="celda-principal">{{ $categoria->nombre }}</span></td>

                                    <td class="text-center">
                                        <span class="pill pill--primary">{{ $categoria->productos_count }}</span>
                                    </td>

                                    <td class="col-acciones">
                                        <div class="acciones">

                                            <button type="button" class="btn-outline btn-compact btn-ver-productos"
                                                data-target="#panel-cat-{{ $categoria->idCategoria }}">
                                                Ver Productos
                                            </button>

                                            <button type="button" class="icon-btn icon-btn--info btn-editar" title="Editar categoría"
                                                data-id="{{ $categoria->idCategoria }}"
                                                data-nombre="{{ $categoria->nombre }}"
                                                data-bs-toggle="modal" data-bs-target="#modalEditar">
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 20h9"/>
                                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                                </svg>
                                            </button>

                                            <button type="button" class="icon-btn icon-btn--danger btn-eliminar" title="Eliminar categoría"
                                                data-id="{{ $categoria->idCategoria }}"
                                                data-nombre="{{ $categoria->nombre }}"
                                                data-count="{{ $categoria->productos_count }}"
                                                data-bs-toggle="modal" data-bs-target="#modalEliminar">
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18"/>
                                                    <path d="M8 6V4h8v2"/>
                                                    <path d="M19 6l-1 14H6L5 6"/>
                                                    <path d="M10 11v6"/>
                                                    <path d="M14 11v6"/>
                                                </svg>
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <div class="empty-icon">🏷️</div>
                                            <strong>No hay categorías registradas</strong>
                                            <p>Crea la primera categoría para organizar tus productos.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- Panel derecho dinámico --}}
        <div class="panel">

            <div class="panel-head">
                <div>
                    <h3>Productos Asociados</h3>
                </div>
            </div>

            <div class="panel-body panel-body--flush" id="contenedor-paneles">

                {{-- Panel por defecto --}}
                <div class="panel-detalle categorias-vacio" id="panel-default">
                    <p class="mb-0">Selecciona "Ver Productos" en la tabla para ver el detalle.</p>
                </div>

                {{-- Paneles generados dinámicamente --}}
                @foreach($categorias as $categoria)
                    <div class="panel-detalle d-none" id="panel-cat-{{ $categoria->idCategoria }}">

                        <div class="categoria-detalle-head">
                            <h5 class="mb-0">{{ $categoria->nombre }}</h5>
                        </div>

                        <ul class="categoria-productos">
                            @forelse($categoria->productos as $producto)
                                <li class="categoria-producto">
                                    <div>
                                        <div class="celda-principal">{{ $producto->nombre }}</div>
                                        <small class="celda-sub">Precio: ${{ number_format($producto->precio, 0, ',', '.') }}</small>
                                    </div>
                                    <span class="pill {{ $producto->stock > 0 ? 'pill--success' : 'pill--danger' }}">
                                        Stock: {{ $producto->stock }}
                                    </span>
                                </li>
                            @empty
                                <li class="categoria-producto categoria-producto--vacio">
                                    No hay productos registrados en esta categoría.
                                </li>
                            @endforelse
                        </ul>

                    </div>
                @endforeach

            </div>

        </div>

    </div>

</div>


{{-- =====================================================
     MODALES
====================================================== --}}

{{-- Modal Crear Categoría --}}
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categorias.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label">Nombre de la Categoría</label>
                        <input type="text" name="nombre" class="form-control" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-solid">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Editar Categoría --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditar" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label">Nombre de la Categoría</label>
                        <input type="text" name="nombre" id="inputEditarNombre" class="form-control" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-solid">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Eliminar Categoría --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Eliminar Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEliminar" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center py-4">
                    <p class="mb-1">¿Estás seguro que deseas eliminar la categoría?</p>
                    <h4 class="fw-bold text-danger mb-3" id="textoEliminarNombre"></h4>

                    <div id="alertaEliminar" class="alert alert-warning d-none text-start mb-0"></div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnSubmitEliminar" class="btn-solid btn-solid--danger">Sí, Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@vite(['resources/js/categorias.js'])
