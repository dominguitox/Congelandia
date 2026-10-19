@extends('layouts.app')

@section('title', 'Usuarios del sistema')

@section('content')

@php
    $listaUsuarios = collect($usuarios);
    $totalUsuarios = $listaUsuarios->count();
    $usuariosActivos = $listaUsuarios->where('activo', true)->count();
    $usuariosInactivos = $totalUsuarios - $usuariosActivos;
    $usuariosAdmin = $listaUsuarios->where('rol', 'Administrador')->count();
@endphp

<div class="page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <div class="page-header">

        <div class="page-title">

            <h1>Usuarios</h1>

            <p>Administra los accesos y roles del sistema</p>

        </div>

        <div class="page-actions">

            <button
                type="button"
                class="btn-solid"
                data-bs-toggle="modal"
                data-bs-target="#modalCrearUsuario"
            >
                <span>+</span> Nuevo Usuario
            </button>

        </div>

    </div>


    {{-- =====================================================
         TARJETAS DE RESUMEN
    ====================================================== --}}

    <div class="stats-grid">

        <div class="stat-tile">

            <div class="stat-tile-info">
                <span>Total de Usuarios</span>
                <strong>{{ $totalUsuarios }}</strong>
            </div>

            <div class="card-icon proveedores-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>

        </div>


        <div class="stat-tile">

            <div class="stat-tile-info">
                <span>Usuarios Activos</span>
                <strong>{{ $usuariosActivos }}</strong>
            </div>

            <div class="card-icon stat-icon-ingreso">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </div>

        </div>


        <div class="stat-tile">

            <div class="stat-tile-info">
                <span>Usuarios Inactivos</span>
                <strong>{{ $usuariosInactivos }}</strong>
            </div>

            <div class="card-icon alerta-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 8v5"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>

        </div>


        <div class="stat-tile">

            <div class="stat-tile-info">
                <span>Administradores</span>
                <strong>{{ $usuariosAdmin }}</strong>
            </div>

            <div class="card-icon productos-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2 4 6v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-4Z"/>
                </svg>
            </div>

        </div>

    </div>


    {{-- =====================================================
         SECCION USUARIOS
    ====================================================== --}}

    <div class="panel">

        <div class="panel-head">
            <div>
                <h3>Usuarios registrados</h3>
                <p>Lista de usuarios con acceso al sistema</p>
            </div>
        </div>

        <div class="panel-body panel-body--flush">

            <div class="data-table-wrap">

                <table class="data-table" id="tabla-usuarios">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th class="col-acciones">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr id="usuario-row-{{ $usuario->idUsuario }}">

                                <td>#{{ $usuario->idUsuario }}</td>

                                <td>
                                    <span class="celda-principal">{{ $usuario->nombre }}</span>
                                </td>

                                <td>{{ $usuario->email }}</td>

                                <td>
                                    <span class="pill pill--muted">{{ $usuario->rol }}</span>
                                </td>

                                <td>
                                    @if($usuario->activo)
                                        <span class="pill pill--success">Activo</span>
                                    @else
                                        <span class="pill pill--danger">Inactivo</span>
                                    @endif
                                </td>

                                <td class="col-acciones">
                                    <div class="acciones">

                                        <button type="button" class="icon-btn icon-btn--warn" title="Desactivar usuario">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="M5.5 5.5 18.5 18.5"/>
                                            </svg>
                                        </button>

                                        <button type="button" class="icon-btn icon-btn--info" title="Ver historial de acciones">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 3v5h5"/>
                                                <path d="M3.05 13a9 9 0 1 0 .5-4.5"/>
                                                <path d="M12 7v5l4 2"/>
                                            </svg>
                                        </button>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr id="sin-usuarios">
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon">👤</div>
                                        <strong>No hay usuarios registrados</strong>
                                        <p>Crea el primer usuario para comenzar.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


{{-- =====================================================
     MODAL CREAR USUARIO
====================================================== --}}

<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearUsuarioLabel">Crear nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Cajero 2" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="correo@congelandia.cl" required>
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Mínimo 4 caracteres" required>
                    </div>
                    <div class="mb-0">
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
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-solid">Crear usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@vite(['resources/js/usuarios.js'])
