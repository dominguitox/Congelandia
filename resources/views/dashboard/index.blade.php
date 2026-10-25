@extends('layouts.app')

@section('title', 'Panel General')

@push('css')
    @vite(['resources/css/dashboard.css'])
@endpush

@section('content')

<div class="page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <div class="page-header">

        <div class="page-title">

            <h1>Panel General</h1>

            <p>Resumen de la actividad de hoy</p>

        </div>

        <div class="page-actions">

            <a href="{{ url('/pos') }}" class="btn-solid">Nueva Venta</a>

        </div>

    </div>


    {{-- =====================================================
         TARJETAS DE RESUMEN
    ====================================================== --}}

    <div class="stats-grid">

        <div class="stat-tile">

            <div class="stat-tile-info">
                <span>Ingresos de hoy</span>
                <strong>${{ $totalVentasHoy }}</strong>
            </div>

            <div class="card-icon stat-icon-ingreso">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 1v22"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>

        </div>


        <div class="stat-tile">

            <div class="stat-tile-info">
                <span>Órdenes de hoy</span>
                <strong>{{ $ordenesHoy }}</strong>
            </div>

            <div class="card-icon proveedores-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                    <path d="M3 6h18"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>

        </div>


        <div class="stat-tile">

            <div class="stat-tile-info">
                <span>Productos en alerta</span>
                <strong>{{ $alertasActivas }}</strong>
            </div>

            <div class="card-icon alerta-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3 2.5 20h19L12 3Z"/>
                    <path d="M12 9v5"/>
                    <path d="M12 17h.01"/>
                </svg>
            </div>

        </div>


        <a href="{{ route('inventario.index') }}#ofertas" class="stat-tile stat-tile--link">

            <div class="stat-tile-info">
                <span>Ofertas activas</span>
                <strong>{{ $cantidadPromocionesActivas }}</strong>
            </div>

            <div class="card-icon productos-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z"/>
                    <path d="M7 7h.01"/>
                </svg>
            </div>

        </a>

    </div>


    {{-- =====================================================
         VENTAS + ALERTAS
    ====================================================== --}}

    <div class="dashboard-grid">

        {{-- Ventas recientes --}}
        <div class="panel">

            <div class="panel-head">
                <div>
                    <h3>Ventas Recientes</h3>
                    <p>Últimas órdenes registradas</p>
                </div>
            </div>

            <div class="panel-body panel-body--flush">

                <div class="dashboard-ventas-list">
                     {{-- salidas => ordena salidas desde la mas antigua 
                          salidasRecientes => ordena salidas desde la mas nueva (usar esta!!) 
                          salidasHoy => salidas solo hoy (desde la mas vieja)
                     --}}
                    @forelse($salidasRecientes as $salida)
                    <div class="venta-item">

                            <div>
                                <div class="venta-item-titulo">Orden #{{ $salida->idSalida }}</div>
                                <div class="venta-item-meta">
                                    {{ \Carbon\Carbon::parse($salida->fecha)->format('d M Y, H:i') }}
                                    •
                                    {{ $salida->detalles->sum('cantidad') }} artículos
                                </div>
                            </div>

                            <div class="venta-item-lado">
                                <strong>${{ number_format($salida->totalSalida, 0, ',', '.') }}</strong>
                                <div class="venta-item-meta">
                                    {{ $salida->usuario->nombre ?? 'Sin usuario' }}
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="venta-vacia">No existen ventas registradas.</div>
                    @endforelse

                </div>

            </div>

        </div>


        {{-- Alertas --}}
        <div class="panel">

            <div class="panel-head">
                <div>
                    <h3>Alertas</h3>
                    <p>Stock bajo y ofertas vigentes</p>
                </div>
            </div>

            <div class="panel-body panel-body--flush">

                <div class="dashboard-alerta-bloque">

                    <h4 class="dashboard-alerta-titulo">Productos bajo stock</h4>

                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th class="col-acciones">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $hayBajoStock = false; @endphp
                                @foreach($productos as $producto)
                                    @if($producto->stock < 10)
                                        @php $hayBajoStock = true; @endphp
                                        <tr>
                                            <td><span class="celda-principal">{{ $producto->nombre }}</span></td>
                                            <td>{{ $producto->categoria ?? 'Sin categoría' }}</td>
                                            <td class="col-acciones">
                                                @if($producto->stock == 0)
                                                    <span class="pill pill--danger">Agotado</span>
                                                @else
                                                    <span class="pill pill--warning">{{ number_format($producto->stock, 0, ',', '.') }} restantes</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                @unless($hayBajoStock)
                                    <tr>
                                        <td colspan="3">
                                            <div class="empty-state">
                                                <strong>Sin alertas de stock</strong>
                                                <p>Todos los productos tienen stock suficiente.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endunless
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="dashboard-alerta-bloque">

                    <h4 class="dashboard-alerta-titulo">Ofertas activas ({{ $cantidadPromocionesActivas }})</h4>

                    <div class="data-table-wrap" id="ofertas">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Descuento</th>
                                    <th class="col-acciones">Finaliza</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promocionesActivas as $promocion)
                                    <tr>
                                        <td><span class="celda-principal">{{ $promocion->producto->nombre ?? 'Producto no encontrado' }}</span></td>
                                        <td class="text-center">
                                            <span class="pill pill--primary">-{{ $promocion->porcentajeDescuento }}%</span>
                                        </td>
                                        <td class="col-acciones">
                                            {{ \Carbon\Carbon::parse($promocion->fechaFin)->format('d M') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">
                                            <div class="empty-state">
                                                <strong>No hay ofertas activas</strong>
                                                <p>Las promociones vigentes aparecerán aquí.</p>
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

    </div>

</div>

@endsection
