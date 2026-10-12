```html
@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
    <div class="container mt-4">

        <!-- Encabezado -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2>Categorías</h2>
                <p class="text-muted">Gestiona las categorías de productos</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
                    + Nueva Categoría
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Total Categorías</h5>
                        <p class="card-text fs-3 fw-bold">{{ $estadisticas['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Productos con categoría</h5>
                        <p class="card-text fs-3 fw-bold">{{ $estadisticas['productos_categorizados'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                @if ($estadisticas['productos_NoCategorizados'] > 0)
                    <div class="card  bg-warning">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Productos sin categoría</h5>
                            <p class="card-text fs-3 fw-bold">{{ $estadisticas['productos_NoCategorizados'] }}</p>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Productos sin categoría</h5>
                            <p class="card-text fs-3 fw-bold">{{ $estadisticas['productos_NoCategorizados'] }}</p>
                        </div>
                    </div>
                @endif

            </div>
            <div class="col-md-3">
                @if ($estadisticas['categorias_uso'] > 0)
                        <div class="card bg-warning">
                            <div class="card-body">
                                <h5 class="card-title text-muted">Categorías sin uso</h5>
                                <p class="card-text fs-3 fw-bold">{{ $estadisticas['categorias_uso'] }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Categorías sin uso</h5>
                            <p class="card-text fs-3 fw-bold">{{ $estadisticas['categorias_uso'] }}</p>
                        </div>
                    </div>
                </div>
            @endif

    </div>

    <div class="row">
        <!-- Tabla Categorías -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Categoría</th>
                                <th class="text-center">Productos</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categorias as $categoria)
                                <tr>
                                    <td class="align-middle">{{ $categoria->nombre }}</td>
                                    <td class="text-center align-middle">{{ $categoria->productos_count }}</td>
                                    <td class="text-end">
                                        <!-- El atributo data-bs-toggle="pill" enlaza nativamente con el panel derecho -->
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="pill"
                                            data-bs-target="#panel-cat-{{ $categoria->idCategoria }}">
                                            Ver Productos
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#modalEditar-{{ $categoria->idCategoria }}">
                                            Editar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger">Eliminar</button>
                                    </td>
                                </tr>

                                <!-- Modal Editar (Se genera iterado para evitar JS extra) -->
                                <div class="modal fade" id="modalEditar-{{ $categoria->idCategoria }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="/categorias/{{ $categoria->idCategoria }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Categoría</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nombre</label>
                                                        <input type="text" name="nombre" class="form-control"
                                                            value="{{ $categoria->nombre }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel Derecho Dinámico -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    Productos Asociados
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- Panel visible por defecto -->
                        <div class="tab-pane fade show active p-4 text-center text-muted" id="default-panel">
                            Haz clic en "Ver Productos" en la tabla para cargar los detalles.
                        </div>

                        <!-- Paneles generados por categoría -->
                        @foreach($categorias as $categoria)
                            <div class="tab-pane fade" id="panel-cat-{{ $categoria->idCategoria }}">
                                <ul class="list-group list-group-flush">
                                    @forelse($categoria->productos as $producto)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $producto->nombre }}
                                            <span class="badge bg-secondary rounded-pill">ID: {{ $producto->id }}</span>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-center text-muted p-4">
                                            Esta categoría no tiene productos asignados.
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
    </div>

    <!-- Modal Crear -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/categorias" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva Categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

```