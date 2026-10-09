@extends('layouts.app')

@section('title', 'Panel General')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Resumen de actividad de hoy</h2>

        <div class="row">
            <!-- Tarjeta de Bootstrap para métricas -->

            <!-- Tarjeta  Ingresos Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Ingresos de hoy</h5>
                        <p class="card-text display-6 fw-bold text-success">${{$totalVentasHoy }}</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Órdenes Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Órdenes</h5>
                        <p class="card-text display-6 fw-bold">0</p>
                    </div>
                </div>
            </div>
<<<<<<< Updated upstream
            <!-- Tarjeta Productos vendidos Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Productos vendidos</h5>
                        <p class="card-text display-6 fw-bold">0</p>
                    </div>
                </div>
            </div>
=======

>>>>>>> Stashed changes
            <!-- Tarjeta Alertas -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Alertas</h5>
                        <p class="card-text display-6 fw-bold">0</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta botón mostrar ofertas -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Ofertas</h5>
                        <p class="card-text display-6 fw-bold">{{ $cantidadPromocionesActivas }}</p>
                    </div>
                </div>
            </div>

        </div>
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
        <div class="row">
            <!-- Log Ventas recientes -->
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Ventas</h5>
                        <div class="historial-card">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">Fecha y Hora</th>
                                        <th scope="col">Artículos</th>
                                        <th scope="col">Cajero</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($salidas as $salida)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($salida->fecha)->format('d M Y, H:i') }}</td>

                                            {{-- Suma la cantidad total desde los detalles --}}
                                            <td>{{ $salida->detalles->sum('cantidad') }}</td>

                                            <td>{{ $salida->idUsuario ?? 'Sin usuario' }}</td>
                                            <td class="total">${{ number_format($salida->totalSalida, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="empty-message">No existen ventas registradas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Alertas -->
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Alertas</h5>

<<<<<<< Updated upstream
                        <table class="table">
=======
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
                        <!-- Tabla: Productos con ofertas activas -->
                        <table class="table align-middle">
>>>>>>> Stashed changes
                            <thead>
                                <h6> Productos bajo stock </h6>
                                <tr>
<<<<<<< Updated upstream
                                    <th scope="col">Producto</th>
                                    <th scope="col">Categoria</th>
                                    <th scope="col">Costo</th>
                                    <th scope="col">Precio Venta</th>
                                    <th scope="col">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productos as $producto)

                                    @if($producto->stock == 0)
                                        <tr class="table-danger">
                                    @elseif($producto->stock < 10)
                                            <tr class="table-warning">
                                        @else
                                            <tr class="table-default" style="display: none;">
                                        @endif
                                        <td>{{ $producto->nombre }} <br>
                                        </td>
                                        <td>{{ $producto->categoria ?? 'Sin categoría' }}</td>
                                        <td>${{ number_format($producto->costo ?? 0, 0, ',', '.') }}</td>
                                        <td>${{ number_format($producto->precio ?? 0, 0, ',', '.') }}</td>

                                        @if($producto->stock == 0)
                                        <td><b>Agotado</b></td>
                                        @else
                                            <td>{{ number_format($producto->stock ?? 0, 0, ',', '.') }}</td>
                                        @endif

                                    </tr>
                                @endforeach
                            </tbody>

                        </table>

                        <table class="table">
                            <thead>
                                <h6> Productos prontos a expirar </h6>
                                <tr>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Categoria</th>
                                    <th scope="col">Costo</th>
                                    <th scope="col">Precio Venta</th>
                                    <th scope="col">Fecha de vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($productos as $producto)
                                    @php
                                        // 1. Parseamos la fecha de MySQL y calculamos los días restantes hasta hoy
                                        $fechaVence = \Carbon\Carbon::parse($producto->fechaVencimiento)->startOfDay();
                                        $hoy = \Carbon\Carbon::now()->startOfDay();
                                        $diasRestantes = $hoy->diffInDays($fechaVence, false); 
                                    @endphp

                                    @if($diasRestantes <= 0)
                                        <tr class="table-danger">
                                    @elseif($diasRestantes < 10)
                                            <tr class="table-warning">
                                        @else
                                            <tr class="table-default" style="display: none;">
                                        @endif
                                        <td>{{ $producto->nombre }}</td>
                                        <td>{{ $producto->categoria ?? 'Sin categoría' }}</td>
                                        <td>${{ number_format($producto->costo ?? 0, 0, ',', '.') }}</td>
                                        <td>${{ number_format($producto->precio ?? 0, 0, ',', '.') }}</td>
                                        <td>
                                            {{ $fechaVence->format('d M') }}
                                            <small class="text-muted">
                                                ({{ $diasRestantes <= 0 ? 'Vencido' : "Faltan $diasRestantes días" }})
                                            </small>
=======
                                    <th colspan="3" class="text-center border-0 pb-3">
                                        <h6 class="text-primary fw-bold mb-0">Ofertas Activas
                                            ({{ $cantidadPromocionesActivas }})</h6>
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="col" class="text-start text-muted">Producto</th>
                                    <th scope="col" class="text-center text-muted">Descuento</th>
                                    <th scope="col" class="text-end text-muted">Finaliza</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($promocionesActivas as $promocion)
                                    @php
                                        // descomentar para debug
                                        // dd($promocion);
                                    @endphp
                                    <tr>
                                        <td class="text-start fw-semibold">
                                            {{ $promocion->producto->nombre ?? 'Producto no encontrado' }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-primary-subtle text-primary-emphasis px-2 py-1 border border-primary-subtle">
                                                -{{ $promocion->porcentajeDescuento }}%
                                            </span>
                                        </td>
                                        <td class="text-end text-muted">
                                            {{ \Carbon\Carbon::parse($promocion->fechaFin)->format('d M') }}
>>>>>>> Stashed changes
                                        </td>
                                    </tr>
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