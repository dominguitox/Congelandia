@extends('layouts.app')

@section('title', 'Usuarios del sistema')


@section('content')
    <!-- Encabezado -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold">Usuarios</h2>
            <p class="text-muted">Ver y crear usuarios del sistema</p>
        </div>
        <div class="col-md-4 text-end">
            <!-- Botón que activa el Modal -->
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                data-bs-target="#modalCrearUsuario">
                + Nuevo Usuario
            </button>
        </div>
    </div>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm"> </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle" id="tabla-usuarios">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuarios as $usuario)
                                    <tr id="usuario-row-{{ $usuario->idUsuario }}">
                                        <td>{{ $usuario->idUsuario }}</td>
                                        <td>{{ $usuario->nombre }}</td>
                                        <td>{{ $usuario->email }}</td>
                                        <td>{{ $usuario->rol }}</td>
                                        <td>
                                            @if($usuario->activo)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning">
                                                Desactivar usuario
                                            </button>
                                            <button class="btn btn-sm btn-primary">
                                                Ver historial de acciones
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="sin-usuarios">
                                        <td colspan="5" class="text-center">No hay usuarios registrados en el sistema.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bootstrap para Crear Usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalCrearUsuarioLabel">Crear nuevo usuario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Cajero 2"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="correo@congelandia.cl" required>
                        </div>
                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="contrasena" name="contrasena"
                                placeholder="Mínimo 4 caracteres" required>
                        </div>
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol *</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="" selected disabled>Selecciona un rol</option>
                                <option value="Administrador">Administrador</option>
                                <option value="Jefe">Jefe</option>
                                <option value="Cajero">Cajero</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@vite(['resources/js/usuarios.js'])