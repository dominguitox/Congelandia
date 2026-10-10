@extends('layouts.app')

@section('title', 'Reportes')

@push('css')
    @vite(['resources/css/reportes.css'])
@endpush
@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold text-primary">Reportes de Salidas</h2>
                <p class="text-muted">Consulta la información de los productos más vendidos en un rango de fechas[cite: 3].
                </p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reportes.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="fecha_inicio" class="form-label fw-semibold text-secondary">Fecha Desde</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                            class="form-control form-control-lg bg-light border-0" value="{{ $fechaInicio }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="fecha_fin" class="form-label fw-semibold text-secondary">Fecha Hasta</label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                            class="form-control form-control-lg bg-light border-0" value="{{ $fechaFin }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            Filtrar Datos
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-semibold text-secondary">Resultados del Reporte</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th scope="col" class="ps-4 py-3">Fecha</th>
                                <th scope="col" class="py-3">Producto</th>
                                <th scope="col" class="text-center py-3 pe-4">Unidades Vendidas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($datos->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <p class="mb-0 fs-5">No hay salidas registradas en este periodo.</p>
                                    </td>
                                </tr>
                            @else
                                @foreach($datos as $fila)
                                    <tr class="border-bottom">
                                        <td class="ps-4 text-muted">{{ \Carbon\Carbon::parse($fila->fecha)->format('d/m/Y') }}</td>
                                        <td class="fw-semibold text-dark">{{ $fila->nombre }}</td>
                                        <td class="text-center pe-4">
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fs-6">
                                                {{ $fila->total_unidades }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection