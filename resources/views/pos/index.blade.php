@extends('layouts.app')

@section('title', 'Punto de Venta')

@push('css')
    @vite(['resources/css/pos.css'])
@endpush

@section('content')

<div class="page" id="rutasPos" data-registrar-venta="{{ route('venta.registrar') }}" data-tipo-venta="{{ $idTipoVenta }}" data-usuario="{{ auth()->id() }}">

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
                            data-stock="{{ $producto->stock }}" role="group" tabindex="0">

                            <button type="button" class="producto-card-fijar" aria-pressed="false"
                                aria-label="Fijar {{ $producto->nombre }} al inicio del listado"
                                title="Fijar producto" data-nombre-producto="{{ $producto->nombre }}">
                                <span aria-hidden="true">★</span>
                            </button>

                            <div class="producto-card-imagen" aria-hidden="true">
                                @if ($producto->imagenProducto)
                                    <img src="{{ asset($producto->imagenProducto) }}"
                                        alt="{{ $producto->nombre }}"
                                        loading="lazy"
                                        onerror="this.hidden=true; this.nextElementSibling.hidden=false">
                                    <span class="producto-card-sin-imagen" hidden>📦</span>
                                @else
                                    <span class="producto-card-sin-imagen">📦</span>
                                @endif
                            </div>

                            <div class="producto-card-top">
                                <span class="badge bg-info" id="badge-carrito-{{ $producto->codigo }}" hidden>En carrito: 0</span>
                                @if ($descuentoOferta > 0)
                                    <span class="badge badge-oferta">-{{ number_format($descuentoOferta, 0, ',', '.') }}%</span>
                                @endif
                            </div>

                            <div class="producto-card-info">
                                <h5 class="producto-card-nombre">{{ $producto->nombre }}</h5>
                                <p class="producto-card-categoria">{{ $producto->categoria }}</p>
                            </div>

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

                <p id="resultado-venta" role="status" aria-live="polite"></p>
                <button type="button" id="btn-cobrar" class="btn-solid btn-solid--success ticket-cobrar">Cobrar</button>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="modalConfirmarVenta" tabindex="-1" aria-labelledby="tituloConfirmarVenta" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="form-confirmar-venta">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="tituloConfirmarVenta">Confirmar venta</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>Revisa los productos, el cliente y los pagos antes de registrar la venta.</p>
                <ul class="cobro-productos" id="resumen-productos"></ul>
                <p class="cobro-total">Total de la venta <strong id="confirmacion-total">$0</strong></p>
                <fieldset id="datos-cobro">
                    <legend class="visually-hidden">Datos del cobro</legend>
                    <label for="cliente_select" class="form-label">Cliente</label>
                    <select id="cliente_select" class="form-select mb-3">
                        <option value="">Sin cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->rutCliente }}">{{ $cliente->nombre }}</option>
                        @endforeach
                    </select>
                    <h3 class="fs-6">Métodos de pago</h3>
                    <p class="text-muted">Asigna cuánto de la venta se paga con cada método.</p>
                    <div id="pagos-cobro"></div>
                    <button type="button" class="btn btn-outline-primary mb-3" id="agregar-pago">Agregar otro método</button>
                    <section id="efectivo-panel" hidden aria-labelledby="tituloEfectivo">
                        <h3 class="fs-6" id="tituloEfectivo">Calcular vuelto</h3>
                        <label for="efectivo-recibido" class="form-label">Efectivo recibido del cliente</label>
                        <input type="number" id="efectivo-recibido" class="form-control" min="0" max="9999999999" step="1" inputmode="numeric" placeholder="Si paga justo, déjalo vacío" aria-describedby="ayudaEfectivo">
                        <p class="form-text" id="ayudaEfectivo">Escribe el total que te entregan. Si paga justo, puedes dejarlo vacío.</p>
                    </section>
                </fieldset>
                <dl class="cobro-resumen" aria-live="polite" aria-atomic="true">
                    <div><dt>Aplicado a la venta</dt><dd id="cobro-asignado">$0</dd></div>
                    <div><dt>Por asignar</dt><dd id="cobro-restante">$0</dd></div>
                    <div><dt>Parte en efectivo</dt><dd id="cobro-efectivo">$0</dd></div>
                    <div class="cobro-vuelto"><dt>Vuelto a entregar</dt><dd id="cobro-vuelto">$0</dd></div>
                </dl>
                <p id="error-cobro" class="text-danger" role="alert"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Volver</button>
                <button type="submit" class="btn btn-success" id="confirmar-cobro">Confirmar y registrar venta</button>
            </div>
        </form>
    </div>
</div>

<template id="plantilla-pago">
    <div class="cobro-pago">
        <div><label class="form-label" data-label-metodo>Método</label>
            <select class="form-select" data-metodo required>
                @foreach($metodosPago as $metodo)
                    <option value="{{ $metodo->idMetodo }}" data-efectivo="{{ mb_strtolower(trim($metodo->nombre)) === 'efectivo' ? '1' : '0' }}">{{ $metodo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="form-label" data-label-monto>Importe aplicado</label>
            <input type="number" class="form-control" data-monto min="0" max="9999999999" step="1" inputmode="numeric" required>
        </div>
        <div class="cobro-pago-acciones">
            <button type="button" class="btn btn-sm btn-outline-primary" data-restante>Asignar restante</button>
            <button type="button" class="btn btn-sm btn-outline-danger" data-quitar>Quitar método</button>
        </div>
    </div>
</template>

@endsection

@push('scripts')
    @vite(['resources/js/venta.js'])
@endpush
