@extends('layouts.app')

@section('title', 'Punto de Venta')

@push('css')
    @vite(['resources/css/pos.css'])
@endpush

@section('content')

<div class="page">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <div class="page-header">

        <div class="page-title">

            <h1>Punto de Venta</h1>

            <p>Selecciona productos y registra la venta</p>

        </div>

    </div>


    <div class="pos-grid">

        {{-- =================================================
             CATALOGO DE PRODUCTOS
        ================================================== --}}

        <div class="panel">

            <div class="panel-head">
                <div>
                    <h3>Productos</h3>
                    <p>Haz clic en un producto para agregarlo a la boleta</p>
                </div>
                <div class="toolbar">
                    <div class="buscador-producto">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input type="text" id="buscarProducto" placeholder="Buscar por código o nombre..." autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="panel-body">

                <div class="pos-productos" id="grillaProductos">

                    @forelse ($productos as $producto)
                        @php
                            $precioNormal = (float) ($producto->precio ?? 0);
                            $descuentoOferta = (float) ($producto->descuentoOferta ?? 0);
                            $precioOferta = $descuentoOferta > 0
                                ? (float) round($precioNormal * (1 - $descuentoOferta / 100))
                                : $precioNormal;
                        @endphp
                        <div class="producto-card" data-id="{{ $producto->codigo }}"
                            data-nombre="{{ $producto->nombre }}"
                            data-precio-normal="{{ $precioNormal }}"
                            data-precio-oferta="{{ $precioOferta }}"
                            data-descuento="{{ $descuentoOferta }}"
                            data-stock="{{ $producto->stock }}" role="button" tabindex="0">

                            <div class="producto-card-top">
                                <span class="badge bg-info" id="badge-carrito-{{ $producto->codigo }}" hidden>En carrito: 0</span>
                                @if ($descuentoOferta > 0)
                                    <span class="badge badge-oferta">-{{ number_format($descuentoOferta, 0, ',', '.') }}%</span>
                                @endif
                            </div>

                            <h5 class="producto-card-nombre">{{ $producto->nombre }}</h5>
                            <p class="producto-card-categoria">{{ $producto->categoria }}</p>

                            <div class="producto-card-pie">
                                <span class="producto-card-precio-wrap">
                                    @if ($descuentoOferta > 0)
                                        <small class="producto-card-precio-normal">Normal: ${{ number_format($precioNormal, 0, ',', '.') }}</small>
                                        <span class="producto-card-precio producto-card-precio--oferta">${{ number_format($precioOferta, 0, ',', '.') }}</span>
                                    @else
                                        <span class="producto-card-precio">${{ number_format($precioNormal, 0, ',', '.') }}</span>
                                    @endif
                                </span>
                                <span class="badge badge-stock" id="badge-stock-{{ $producto->codigo }}"
                                    data-stock-inicial="{{ $producto->stock }}">Stock: {{ $producto->stock }}</span>
                            </div>

                        </div>
                    @empty
                        <div class="empty-state pos-vacio">
                            <div class="empty-icon">🛒</div>
                            <strong>No hay productos disponibles</strong>
                            <p>Agrega productos al inventario para comenzar a vender.</p>
                        </div>
                    @endforelse

                </div>

            </div>

        </div>


        {{-- =================================================
             BOLETA / CARRITO
        ================================================== --}}

        <div class="panel panel-ticket">

            <div class="panel-head panel-head--accent">
                <div>
                    <h3>Detalle de Venta</h3>
                </div>
            </div>

            <div id="ticket-items" class="ticket-items">
                <p class="text-center text-muted mt-5">No hay productos en la boleta.</p>
            </div>

            <div class="ticket-pie">

                <div class="ticket-total">
                    <div class="ticket-total-normal" id="ticket-total-normal-wrap" hidden>
                        <span>Total sin ofertas</span>
                        <del id="ticket-total-normal">$0</del>
                    </div>
                    <div class="ticket-total-actual">
                        <span id="ticket-total-label">Total</span>
                        <strong id="ticket-total">$0</strong>
                    </div>
                </div>

                <div class="ticket-cliente">
                    <label for="cliente_select">Cliente</label>
                    <select id="cliente_select" class="form-control">
                        <option value="Ninguno">Ninguno</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->rutCliente }}">{{ $cliente->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ticket-cliente">
                    <label for="metodo_pago_select">Método de pago</label>
                    <select id="metodo_pago_select" class="form-control">
                        @foreach($metodosPago as $metodo)
                            <option value="{{ $metodo->idMetodo }}">{{ $metodo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="btn-solid btn-solid--success ticket-cobrar" onclick="registrarVenta()">Cobrar</button>

            </div>

        </div>

    </div>

</div>

@endsection

@vite(['resources/js/venta.js'])
