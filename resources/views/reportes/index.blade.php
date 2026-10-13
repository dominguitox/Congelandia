@extends('layouts.app')

@section('title', 'Reportes')

@push('css')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold text-primary">Reportes de Salidas e Inventario</h2>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reportes.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="fecha_inicio" class="form-label fw-semibold text-secondary">Fecha Desde</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                            class="form-control form-control-lg bg-light border-0" value="{{ $fechaInicio }}" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="fecha_fin" class="form-label fw-semibold text-secondary">Fecha Hasta</label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                            class="form-control form-control-lg bg-light border-0" value="{{ $fechaFin }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="tipo_reporte" class="form-label fw-semibold text-secondary">Tipo de Reporte</label>
                        <select name="tipo_reporte" id="tipo_reporte" class="form-select form-select-lg bg-light border-0">
                            <option value="crudo" {{ $tipoReporte == 'crudo' ? 'selected' : '' }}>Reporte Diario (Crudo)</option>
                            <option value="mas_vendidos" {{ $tipoReporte == 'mas_vendidos' ? 'selected' : '' }}>1. Productos Más Vendidos</option>
                            <option value="ingresos" {{ $tipoReporte == 'ingresos' ? 'selected' : '' }}>2. Total de Ingresos</option>
                            <option value="cantidad_ventas" {{ $tipoReporte == 'cantidad_ventas' ? 'selected' : '' }}>3. Cantidad de Ventas</option>
                            <option value="total_articulos" {{ $tipoReporte == 'total_articulos' ? 'selected' : '' }}>4. Total de Artículos</option>
                            <option value="ticket_promedio" {{ $tipoReporte == 'ticket_promedio' ? 'selected' : '' }}>5. Ticket Promedio</option>
                            <option value="ventas_categoria" {{ $tipoReporte == 'ventas_categoria' ? 'selected' : '' }}>6. Ventas por Categoría</option>
                            <option value="ventas_dia" {{ $tipoReporte == 'ventas_dia' ? 'selected' : '' }}>7. Ventas por Día de la Semana</option>
                            <option value="hora_pico" {{ $tipoReporte == 'hora_pico' ? 'selected' : '' }}>8. Horarios con Más Tráfico</option>
                            <option value="bajo_stock" {{ $tipoReporte == 'bajo_stock' ? 'selected' : '' }}>9. Productos con Bajo Stock</option>
                            <option value="proximos_vencer" {{ $tipoReporte == 'proximos_vencer' ? 'selected' : '' }}>10. Productos Próximos a Vencer</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            Filtrar
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
                
                <!-- Condición para reportes de un solo valor numérico (2, 3, 4, 5) -->
                @if(in_array($tipoReporte, ['ingresos', 'cantidad_ventas', 'total_articulos', 'ticket_promedio']))
                    <div class="p-5 text-center">
                        <h3 class="text-muted mb-3">Total Calculado</h3>
                        <h1 class="display-4 text-primary fw-bold">
                            @if(in_array($tipoReporte, ['ingresos', 'ticket_promedio'])) $ @endif
                            {{ is_numeric($datos) ? number_format($datos, 0, ',', '.') : 0 }}
                        </h1>
                    </div>
                
                <!-- Tablas dinámicas para colecciones de datos (1, 6, 7, 8, 9, 10) -->
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless align-middle mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    @switch($tipoReporte)
                                        @case('mas_vendidos')
                                            <th class="ps-4 py-3">Producto</th>
                                            <th class="text-center py-3">Unidades Vendidas</th>
                                            <th class="text-end py-3 pe-4">Dinero Generado</th>
                                            @break
                                        @case('ventas_categoria')
                                            <th class="ps-4 py-3">Categoría</th>
                                            <th class="text-center py-3">Unidades Vendidas</th>
                                            <th class="text-end py-3 pe-4">Ingresos Totales</th>
                                            @break
                                        @case('ventas_dia')
                                            <th class="ps-4 py-3">Día</th>
                                            <th class="text-end py-3 pe-4">Ingresos Totales</th>
                                            @break
                                        @case('hora_pico')
                                            <th class="ps-4 py-3">Hora</th>
                                            <th class="text-end py-3 pe-4">Cantidad Transacciones</th>
                                            @break
                                        @case('bajo_stock')
                                            <th class="ps-4 py-3">Producto</th>
                                            <th class="text-end py-3 pe-4">Stock Actual</th>
                                            @break
                                        @case('proximos_vencer')
                                            <th class="ps-4 py-3">Producto</th>
                                            <th class="text-center py-3">Vencimiento</th>
                                            <th class="text-end py-3 pe-4">Cantidad Disponible</th>
                                            @break
                                        @default
                                            <th class="ps-4 py-3">Fecha</th>
                                            <th class="py-3">Producto</th>
                                            <th class="text-center py-3 pe-4">Unidades Vendidas</th>
                                    @endswitch
                                </tr>
                            </thead>
                            <tbody>
                                @if(is_iterable($datos) && $datos->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <p class="mb-0 fs-5">No hay registros que coincidan con la búsqueda.</p>
                                        </td>
                                    </tr>
                                @elseif(is_iterable($datos))
                                    @foreach($datos as $fila)
                                        <tr class="border-bottom">
                                            @switch($tipoReporte)
                                                @case('mas_vendidos')
                                                    <td class="ps-4 fw-semibold text-dark">{{ $fila->nombre }}</td>
                                                    <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fs-6">{{ $fila->total_unidades }}</span></td>
                                                    <td class="text-end pe-4 text-muted">${{ number_format($fila->dinero_generado, 0, ',', '.') }}</td>
                                                    @break
                                                @case('ventas_categoria')
                                                    <td class="ps-4 fw-semibold text-dark">{{ $fila->categoria }}</td>
                                                    <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fs-6">{{ $fila->total_unidades }}</span></td>
                                                    <td class="text-end pe-4 text-muted">${{ number_format($fila->ingresos, 0, ',', '.') }}</td>
                                                    @break
                                                @case('ventas_dia')
                                                    <td class="ps-4 fw-semibold text-dark">{{ $fila->dia }}</td>
                                                    <td class="text-end pe-4 text-muted">${{ number_format($fila->ingresos, 0, ',', '.') }}</td>
                                                    @break
                                                @case('hora_pico')
                                                    <td class="ps-4 fw-semibold text-dark">{{ $fila->hora }}:00</td>
                                                    <td class="text-end pe-4 text-muted">{{ $fila->cantidad_transacciones }}</td>
                                                    @break
                                                @case('bajo_stock')
                                                    <td class="ps-4 fw-semibold text-dark">{{ $fila->nombre }}</td>
                                                    <td class="text-end pe-4"><span class="badge bg-danger px-3 py-2 rounded-pill fs-6">{{ $fila->stock_actual }} u.</span></td>
                                                    @break
                                                @case('proximos_vencer')
                                                    <td class="ps-4 fw-semibold text-dark">{{ $fila->nombre }}</td>
                                                    <td class="text-center text-muted">{{ \Carbon\Carbon::parse($fila->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                    <td class="text-end pe-4 text-muted">{{ $fila->cantidad_disponible }} u.</td>
                                                    @break
                                                @default
                                                    <td class="ps-4 text-muted">{{ \Carbon\Carbon::parse($fila->fecha)->format('d/m/Y') }}</td>
                                                    <td class="fw-semibold text-dark">{{ $fila->nombre }}</td>
                                                    <td class="text-center pe-4">
                                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fs-6">
                                                            {{ $fila->total_unidades }}
                                                        </span>
                                                    </td>
                                            @endswitch
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection