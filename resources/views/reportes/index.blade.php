@extends('layouts.app')

@section('title', 'Reportes')

@push('css')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')

<div class="page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <div class="page-header">

        <div class="page-title">

            <h1>Reportes</h1>

            <p>Analiza salidas de inventario y métricas de ventas</p>

        </div>

    </div>


    {{-- =====================================================
         FILTROS
    ====================================================== --}}

    <div class="panel">

        <div class="panel-body">

            <form method="GET" action="{{ route('reportes.index') }}" class="reportes-filtros">

                <div class="campo">
                    <label for="fecha_inicio" class="form-label">Fecha Desde</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                        class="form-control" value="{{ $fechaInicio }}" required>
                </div>

                <div class="campo">
                    <label for="fecha_fin" class="form-label">Fecha Hasta</label>
                    <input type="date" name="fecha_fin" id="fecha_fin"
                        class="form-control" value="{{ $fechaFin }}" required>
                </div>

                <div class="campo campo--amplio">
                    <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                    <select name="tipo_reporte" id="tipo_reporte" class="form-select">
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

                <div class="campo campo--boton">
                    <button type="submit" class="btn-solid">Filtrar</button>
                </div>

            </form>

        </div>

    </div>


    {{-- =====================================================
         RESULTADOS
    ====================================================== --}}

    <div class="panel panel--resultados">

        <div class="panel-head">
            <div>
                <h3>Resultados del Reporte</h3>
                <p>Datos según el rango y tipo seleccionados</p>
            </div>
        </div>

        <div class="panel-body panel-body--flush">

            {{-- Reportes de un solo valor numérico (2, 3, 4, 5) --}}
            @if(in_array($tipoReporte, ['ingresos', 'cantidad_ventas', 'total_articulos', 'ticket_promedio']))

                <div class="reporte-valor">

                    <span class="reporte-valor-label">Total Calculado</span>

                    <strong class="reporte-valor-numero">
                        @if(in_array($tipoReporte, ['ingresos', 'ticket_promedio'])) $ @endif{{ is_numeric($datos) ? number_format($datos, 0, ',', '.') : 0 }}
                    </strong>

                </div>

            {{-- Tablas dinámicas (1, 6, 7, 8, 9, 10) --}}
            @else

                <div class="data-table-wrap">

                    <table class="data-table">

                        <thead>
                            <tr>
                                @switch($tipoReporte)
                                    @case('mas_vendidos')
                                        <th>Producto</th>
                                        <th class="text-center">Unidades Vendidas</th>
                                        <th class="col-acciones">Dinero Generado</th>
                                        @break
                                    @case('ventas_categoria')
                                        <th>Categoría</th>
                                        <th class="text-center">Unidades Vendidas</th>
                                        <th class="col-acciones">Ingresos Totales</th>
                                        @break
                                    @case('ventas_dia')
                                        <th>Día</th>
                                        <th class="col-acciones">Ingresos Totales</th>
                                        @break
                                    @case('hora_pico')
                                        <th>Hora</th>
                                        <th class="col-acciones">Cantidad Transacciones</th>
                                        @break
                                    @case('bajo_stock')
                                        <th>Producto</th>
                                        <th class="col-acciones">Stock Actual</th>
                                        @break
                                    @case('proximos_vencer')
                                        <th>Producto</th>
                                        <th class="text-center">Vencimiento</th>
                                        <th class="col-acciones">Cantidad Disponible</th>
                                        @break
                                    @default
                                        <th>Fecha</th>
                                        <th>Producto</th>
                                        <th class="text-center">Unidades Vendidas</th>
                                @endswitch
                            </tr>
                        </thead>

                        <tbody>
                            @if(is_iterable($datos) && $datos->isEmpty())
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <div class="empty-icon">📊</div>
                                            <strong>No hay registros que coincidan con la búsqueda</strong>
                                            <p>Prueba con otro rango de fechas o tipo de reporte.</p>
                                        </div>
                                    </td>
                                </tr>
                            @elseif(is_iterable($datos))
                                @foreach($datos as $fila)
                                    <tr>
                                        @switch($tipoReporte)
                                            @case('mas_vendidos')
                                                <td><span class="celda-principal">{{ $fila->nombre }}</span></td>
                                                <td class="text-center"><span class="pill pill--primary">{{ $fila->total_unidades }}</span></td>
                                                <td class="col-acciones">${{ number_format($fila->dinero_generado, 0, ',', '.') }}</td>
                                                @break
                                            @case('ventas_categoria')
                                                <td><span class="celda-principal">{{ $fila->categoria }}</span></td>
                                                <td class="text-center"><span class="pill pill--primary">{{ $fila->total_unidades }}</span></td>
                                                <td class="col-acciones">${{ number_format($fila->ingresos, 0, ',', '.') }}</td>
                                                @break
                                            @case('ventas_dia')
                                                <td><span class="celda-principal">{{ $fila->dia }}</span></td>
                                                <td class="col-acciones">${{ number_format($fila->ingresos, 0, ',', '.') }}</td>
                                                @break
                                            @case('hora_pico')
                                                <td><span class="celda-principal">{{ $fila->hora }}:00</span></td>
                                                <td class="col-acciones">{{ $fila->cantidad_transacciones }}</td>
                                                @break
                                            @case('bajo_stock')
                                                <td><span class="celda-principal">{{ $fila->nombre }}</span></td>
                                                <td class="col-acciones"><span class="pill pill--danger">{{ $fila->stock_actual }} u.</span></td>
                                                @break
                                            @case('proximos_vencer')
                                                <td><span class="celda-principal">{{ $fila->nombre }}</span></td>
                                                <td class="text-center">{{ \Carbon\Carbon::parse($fila->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                <td class="col-acciones">{{ $fila->cantidad_disponible }} u.</td>
                                                @break
                                            @default
                                                <td>{{ \Carbon\Carbon::parse($fila->fecha)->format('d/m/Y') }}</td>
                                                <td><span class="celda-principal">{{ $fila->nombre }}</span></td>
                                                <td class="text-center"><span class="pill pill--primary">{{ $fila->total_unidades }}</span></td>
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
