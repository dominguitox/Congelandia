@extends('layouts.app')
@section('title', 'Historial de acciones')

@section('content')
<div class="page">
    <div class="page-header">
        <div class="page-title"><h1>Historial de acciones</h1><p>Accesos y operaciones realizadas por los usuarios del sistema</p></div>
        <a class="btn-outline" href="{{ route('usuarios.index') }}">Ver usuarios</a>
    </div>
    <div class="panel mb-4"><div class="panel-body">
        @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
        <form method="GET" action="{{ route('acciones.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label" for="filtro-usuario">Usuario</label>
                <select class="form-select" id="filtro-usuario" name="usuario">
                    <option value="">Todos los usuarios</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->idUsuario }}" @selected((string) ($filtros['usuario'] ?? '') === (string) $usuario->idUsuario)>{{ $usuario->nombre }}{{ ! $usuario->activo || $usuario->trashed() ? ' (inactivo)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label" for="filtro-modulo">Módulo</label>
                <select class="form-select" id="filtro-modulo" name="modulo">
                    <option value="">Todos los módulos</option>
                    @foreach($modulos as $modulo)
                        <option value="{{ $modulo }}" @selected(($filtros['modulo'] ?? '') === $modulo)>{{ $modulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filtro-desde">Desde</label>
                <input class="form-control" type="date" id="filtro-desde" name="desde" value="{{ $filtros['desde'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filtro-hasta">Hasta</label>
                <input class="form-control" type="date" id="filtro-hasta" name="hasta" value="{{ $filtros['hasta'] ?? '' }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" type="submit">Filtrar</button>
                <a class="btn btn-outline-secondary" href="{{ route('acciones.index') }}">Limpiar</a>
            </div>
        </form>
    </div></div>
    <div class="panel">
        <div class="panel-head"><h2 class="fs-5">Acciones registradas <span class="text-muted">({{ $acciones->total() }})</span></h2></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th scope="col">Fecha</th><th scope="col">Usuario</th><th scope="col">Acción</th><th scope="col" class="d-none d-md-table-cell">Módulo</th><th scope="col" class="d-none d-md-table-cell">Referencia</th></tr></thead>
                <tbody>
                    @forelse($acciones as $accion)
                        <tr>
                            <td><time datetime="{{ $accion->fecha->toIso8601String() }}">{{ $accion->fecha->format('d/m/Y H:i:s') }}</time></td>
                            <td class="text-break">{{ $accion->usuarioNombre }}</td>
                            <td class="text-break">{{ $accion->accion }}<small class="d-block d-md-none text-muted">{{ $accion->modulo }} · {{ $accion->referencia ?? 'Sin referencia' }}</small></td>
                            <td class="d-none d-md-table-cell">{{ $accion->modulo }}</td>
                            <td class="d-none d-md-table-cell text-break">{{ $accion->referencia ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center p-4">No hay acciones que coincidan con los filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $acciones->links('acciones.paginacion') }}</div>
    </div>
</div>
@endsection
