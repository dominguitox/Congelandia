@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
    <div class="container mt-4">

        <!-- Mensajes de Alerta -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Encabezado -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold">Categorías</h2>
                <p class="text-muted">Gestiona las categorías de productos</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrear">
                    + Nueva Categoría
                </button>
            </div>
        </div>

        <!-- Estadísticas (Las 4 Cards) -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase small fw-bold">Total Categorías</h6>
                        <p class="card-text fs-3 fw-bold text-dark mb-0">{{ $estadisticas['total'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase small fw-bold">Productos con categoría</h6>
                        <p class="card-text fs-3 fw-bold text-dark mb-0">{{ $estadisticas['productos_categorizados'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-0 {{ $estadisticas['productos_NoCategorizados'] > 0 ? 'bg-warning bg-opacity-25' : '' }}">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase small fw-bold">Productos sin categoría</h6>
                        <p class="card-text fs-3 fw-bold text-dark mb-0">{{ $estadisticas['productos_NoCategorizados'] }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-0 {{ $estadisticas['categorias_uso'] > 0 ? 'bg-warning bg-opacity-25' : '' }}">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase small fw-bold">Categorías sin productos</h6>
                        <p class="card-text fs-3 fw-bold text-dark mb-0">{{ $estadisticas['categorias_uso'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="row">
            <!-- Tabla Categorías -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Categoría</th>
                                        <th class="text-center">Productos</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categorias as $categoria)
                                        <tr>
                                            <td class="ps-4 fw-medium">{{ $categoria->nombre }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                    {{ $categoria->productos_count }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-info btn-ver-productos"
                                                    data-target="#panel-cat-{{ $categoria->idCategoria }}">
                                                    Ver Productos
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary btn-editar"
                                                    data-id="{{ $categoria->idCategoria }}"
                                                    data-nombre="{{ $categoria->nombre }}" data-bs-toggle="modal"
                                                    data-bs-target="#modalEditar">
                                                    Editar
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger btn-eliminar"
                                                    data-id="{{ $categoria->idCategoria }}"
                                                    data-nombre="{{ $categoria->nombre }}"
                                                    data-count="{{ $categoria->productos_count }}" data-bs-toggle="modal"
                                                    data-bs-target="#modalEliminar">
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho Dinámico -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Productos Asociados</h6>
                    </div>
                    <div class="card-body p-0 bg-light" id="contenedor-paneles">

                        <!-- Panel por defecto -->
                        <div class="p-5 text-center text-muted panel-detalle" id="panel-default">
                            <p class="mb-0">Selecciona "Ver Productos" en la tabla para ver el detalle.</p>
                        </div>

                        <!-- Paneles generados dinámicamente -->
                        @foreach($categorias as $categoria)
                            <div class="panel-detalle d-none h-100" id="panel-cat-{{ $categoria->idCategoria }}">
                                <div class="p-3 bg-white border-bottom">
                                    <h5 class="mb-0 text-primary">{{ $categoria->nombre }}</h5>
                                </div>
                                <ul class="list-group list-group-flush">
                                    @forelse($categoria->productos as $producto)
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-start bg-transparent py-3">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold">{{ $producto->nombre }}</div>
                                                <small class="text-muted">Precio:
                                                    ${{ number_format($producto->precio, 0, ',', '.') }}</small>
                                            </div>
                                            <span class="badge bg-{{ $producto->stock > 0 ? 'success' : 'danger' }} rounded-pill">
                                                Stock: {{ $producto->stock }}
                                            </span>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-center text-muted py-5 bg-transparent">
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
    </div>

    <!-- ==========================================
                 MODALES ÚNICOS (DOM Limpio)
                 ========================================== -->

    <!-- Modal Crear Categoría -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('categorias.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Nombre de la Categoría</label>
                            <input type="text" name="nombre" class="form-control" required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Categoría -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Editar Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditar" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Nombre de la Categoría</label>
                            <input type="text" name="nombre" id="inputEditarNombre" class="form-control" required
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Categoría -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-bottom-0">
                    <h5 class="modal-title fw-bold">Eliminar Categoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEliminar" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center py-4">
                        <p class="mb-1">¿Estás seguro que deseas eliminar la categoría?</p>
                        <h4 class="fw-bold text-danger mb-3" id="textoEliminarNombre"></h4>

                        <div id="alertaEliminar" class="alert alert-warning d-none text-start mb-0">
                            <!-- El texto se inyecta por JS -->
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 justify-content-center">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnSubmitEliminar" class="btn btn-danger px-4">Sí, Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@vite(['resources/js/categorias.js'])