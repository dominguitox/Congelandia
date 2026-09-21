@extends('layouts.app')

@section('title', 'Punto de Venta')

@section('content')
    <div class="container-fluid p-0">
        <!-- Fila principal dividida para el POS -->
        <div class="row g-3">

            <!-- Columna Izquierda: Catálogo de Productos (Ocupa 8 de 12 columnas) -->
            <div class="col-lg-8 col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white pb-0">
                        <h5 class="mb-2">Productos</h5>
                        <!-- Buscador básico -->
                        <input type="text" class="form-control mb-3" placeholder="Buscar por código o nombre...">
                    </div>
                    <div class="card-body bg-light">
                        <div class="row">
                            @foreach ($productos as $producto)
                                <div class="col-md-3 mb-3"> <!-- Ajusta las clases de tu grilla -->
                                    <div class="card producto-card" data-id="{{ $producto->codigo }}"> <!-- Usa el ID correcto -->
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $producto->nombre }}</h5>
                                            <p class="card-text text-muted">{{ $producto->categoria }}</p>

                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <span
                                                    class="fs-5 fw-bold">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                                <span class="badge bg-info">Stock: {{ $producto->stock }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Boleta / Carrito (Ocupa 4 de 12 columnas) -->
            <div class="col-lg-4 col-md-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Detalle de Venta</h5>
                    </div>

                    <!-- Área con scroll para los items del carrito -->
                    <div class="card-body overflow-auto" style="min-height: 400px; max-height: 60vh;">
                        <p class="text-center text-muted mt-5">No hay productos en la boleta.</p>
                    </div>

                    <!-- Botonera fija de pago -->
                    <div class="card-footer bg-white p-3">
                        <div class="d-flex justify-content-between mb-3 fs-5 fw-bold">
                            <span>Total:</span>
                            <span>$0</span>
                        </div>
                        <button class="btn btn-success btn-lg w-100 fw-bold">Cobrar</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection