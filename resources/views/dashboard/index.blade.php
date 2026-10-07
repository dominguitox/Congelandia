@extends('layouts.app')

@section('title', 'Panel General')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Resumen de actividad de hoy</h2>

        <div class="row">
            <!-- Tarjeta Ingresos Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Ingresos de hoy</h5>
                        <p class="card-text display-6 fw-bold text-success">
                            ${{ number_format($totalVentasHoy, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Órdenes Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Órdenes</h5>
                        <p class="card-text display-6 fw-bold">{{ $ordenesHoy }}</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Productos vendidos Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Productos vendidos</h5>
                        <p class="card-text display-6 fw-bold">{{ $productosVendidosHoy }}</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Alertas -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Alertas</h5>
                        <p class="card-text display-6 fw-bold">{{ $alertasActivas }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Log Ventas recientes -->
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h3>Ventas Recientes</h3>

                        <!-- Se agregó text-align: left para contrarrestar el text-center superior -->
                        <div class="historial-card" style="text-align: left;">

                            <div>
                                @forelse($salidas as $salida)
                                    @php
                                        // dd($salida);
                                    @endphp

                                    <!-- Tarjeta de Orden Individual (Flexbox mínimo para alineación) -->
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: start; border: 1px solid #ccc; border-radius: 8px; padding: 10px; margin-bottom: 10px;">
                                        <div>
                                            <div>Orden #s{{ $salida->id }}</div>
                                            <div style="color: #666;">
                                                <!-- Fecha y Hora -->
                                                {{ \Carbon\Carbon::parse($salida->fecha)->format('d M Y, H:i') }}
                                                <!-- Separador • -->
                                                •
                                                <!-- Cantidad de Artículos con Texto -->
                                                {{ $salida->detalles->sum('cantidad') }} artículos
                                            </div>
                                        </div>

                                        <!-- Lado Derecho: Total y Usuario -->
                                        <div style="text-align: right;">
                                            <!-- Total en Negrita -->
                                            <div>
                                                <strong>${{ number_format($salida->totalSalida, 0, ',', '.') }}</strong>
                                            </div>
                                            <!-- Usuario en Texto Suave -->
                                            <div style="color: #666;">
                                                {{ $salida->usuario->nombre ?? 'Sin usuario' }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <!-- Mensaje de Lista Vacía -->
                                    <div style="text-align: center;">No existen ventas registradas.</div>
                                @endforelse
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <!-- Alertas -->
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Alertas</h5>

                        <!-- Tabla: Productos bajo stock -->
                        <table class="table mb-4">
                            <thead>
                                <tr>
                                    <th colspan="3" class="text-center">
                                        <h6>Productos bajo stock</h6>
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="col" class="text-start">Producto</th>
                                    <th scope="col" class="text-start">Categoría</th>
                                    <th scope="col" class="text-end">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productos as $producto)
                                    @if($producto->stock < 10)
                                        <tr class="{{ $producto->stock == 0 ? 'table-danger' : 'table-warning' }}">
                                            <td class="text-start">{{ $producto->nombre }}</td>
                                            <td class="text-start">{{ $producto->categoria ?? 'Sin categoría' }}</td>
                                            <td class="text-end">
                                                @if($producto->stock == 0)
                                                    <b>Agotado</b>
                                                @else
                                                    <b>{{ number_format($producto->stock ?? 0, 0, ',', '.') }} restantes</b>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Tabla: Productos prontos a expirar -->
                        <table class="table">
                            <thead>
                                <tr>
                                    <th colspan="3" class="text-center">
                                        <h6>Productos prontos a expirar</h6>
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="col" class="text-start">Producto</th>
                                    <th scope="col" class="text-start">Categoría</th>
                                    <th scope="col" class="text-end">Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productos as $producto)
                                    @php
                                        $fechaVence = \Carbon\Carbon::parse($producto->fechaVencimiento)->startOfDay();
                                        $hoy = \Carbon\Carbon::now()->startOfDay();
                                        $diasRestantes = $hoy->diffInDays($fechaVence, false); 
                                    @endphp

                                    @if($diasRestantes < 10)
                                        <tr class="{{ $diasRestantes <= 0 ? 'table-danger' : 'table-warning' }}">
                                            <td class="text-start">{{ $producto->nombre }}</td>
                                            <td class="text-start">{{ $producto->categoria ?? 'Sin categoría' }}</td>
                                            <td class="text-end">
                                                {{ $fechaVence->format('d M') }} <br>
                                                <small class="{{ $diasRestantes <= 0 ? 'text-danger' : 'text-dark' }}">
                                                    <b>({{ $diasRestantes <= 0 ? 'Vencido' : "Faltan $diasRestantes días" }})</b>
                                                </small>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
@endsection