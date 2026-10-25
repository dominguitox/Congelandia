@extends('layouts.app')

@section('title', 'Inventario')

@push('css')
    @vite(['resources/css/inventario.css'])
@endpush

@section('content')

@php
    $listaProductos = collect($productos);
    $totalProductos = $listaProductos->count();
    $productosSinStock = $listaProductos->where('stock', 0)->count();
    $productosBajoStock = $listaProductos->where('stock', '>', 0)->where('stock', '<', 10)->count();
    $valorInventario = $listaProductos->sum(fn ($p) => ($p->stock ?? 0) * ($p->precio ?? 0));
@endphp

<div class="page" data-modal-error="{{ old('_modal') }}">

    {{-- =====================================================
         ENCABEZADO
    ====================================================== --}}

    <div class="page-header">

        <div class="page-title">

            <h1>Inventario</h1>

            <p>Controla productos, stock, ingresos y mermas</p>

        </div>

        <div class="page-actions">

            <button type="button" class="btn-solid" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">
                <span>+</span> Añadir Producto
            </button>

            <button type="button" class="btn-outline" data-bs-toggle="modal" data-bs-target="#modalRegistrarIngreso">
                Registrar ingreso
            </button>

            <button type="button" class="btn-outline" data-bs-toggle="modal" data-bs-target="#modalRegistrarSalida">
                Registrar salida o merma
            </button>

        </div>

    </div>


    {{-- =====================================================
         TARJETAS DE RESUMEN
    ====================================================== --}}

    <div class="stats-grid">

        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Total de Productos</span>
                <strong>{{ $totalProductos }}</strong>
            </div>
            <div class="card-icon proveedores-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m21 8-9-5-9 5 9 5 9-5Z"/>
                    <path d="M3 8v8l9 5 9-5V8"/>
                    <path d="M12 13v8"/>
                </svg>
            </div>
        </div>

        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Sin Stock</span>
                <strong>{{ $productosSinStock }}</strong>
            </div>
            <div class="card-icon alerta-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3 2.5 20h19L12 3Z"/>
                    <path d="M12 9v5"/>
                    <path d="M12 17h.01"/>
                </svg>
            </div>
        </div>

        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Bajo Stock</span>
                <strong>{{ $productosBajoStock }}</strong>
            </div>
            <div class="card-icon warning">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3 2.5 20h19L12 3Z"/>
                    <path d="M12 9v5"/>
                    <path d="M12 17h.01"/>
                </svg>
            </div>
        </div>

        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Valor de Inventario</span>
                <strong>${{ number_format($valorInventario, 0, ',', '.') }}</strong>
            </div>
            <div class="card-icon stat-icon-ingreso">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 1v22"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
        </div>

    </div>


    {{-- =====================================================
         SECCION INVENTARIO
    ====================================================== --}}

    <div class="panel">

        <div class="panel-head">
            <div>
                <h3>Productos registrados</h3>
                <p>Lista completa del catálogo y su disponibilidad</p>
            </div>
        </div>

        <div class="panel-body panel-body--flush">

            <div class="data-table-wrap">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Alerta</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Proveedor</th>
                            <th class="col-acciones">Costo</th>
                            <th class="col-acciones">Precio Venta</th>
                            <th class="text-center">Stock</th>
                            <th class="col-acciones">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($productos as $producto)

                            <tr>
                                <td>
                                    @if($producto->stock == 0)
                                        <span class="pill pill--danger">Sin Stock</span>
                                    @elseif($producto->stock < 10)
                                        <span class="pill pill--warning">Bajo Stock</span>
                                    @else
                                        <span class="pill pill--success">Stock OK</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="celda-principal">{{ $producto->nombre }}</span>
                                    <span class="celda-sub">{{ $producto->codigo }}</span>
                                </td>

                                <td>{{ $producto->categoria ?? 'Sin categoría' }}</td>

                                <td>{{ $producto->proveedor ?? 'Sin proveedor' }}</td>

                                <td class="col-acciones">${{ number_format($producto->costo ?? 0, 0, ',', '.') }}</td>

                                <td class="col-acciones">${{ number_format($producto->precio ?? 0, 0, ',', '.') }}</td>

                                <td class="text-center">{{ number_format($producto->stock ?? 0, 0, ',', '.') }}</td>

                                <td class="col-acciones">
                                    <div class="acciones">

                                        <button type="button" class="icon-btn icon-btn--success" title="Registrar ingreso"
                                            data-bs-toggle="modal" data-bs-target="#modalRegistrarIngreso"
                                            data-codigo="{{ $producto->codigo }}">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 5v14"/>
                                                <path d="M5 12h14"/>
                                            </svg>
                                        </button>

                                        <button type="button" class="icon-btn icon-btn--info" title="Editar producto"
                                            data-bs-toggle="modal" data-bs-target="#modalEditarProducto"
                                            data-codigo="{{ $producto->codigo }}"
                                            data-nombre="{{ $producto->nombre }}"
                                            data-categoria="{{ $producto->idCategoria }}"
                                            data-precio="{{ $producto->precioVenta }}"
                                            data-descripcion="{{ $producto->descripcion }}"
                                            onclick="editarProducto(this)">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                            </svg>
                                        </button>

                                        <button type="button" class="icon-btn icon-btn--danger" title="Borrar producto">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4h8v2"/>
                                                <path d="M19 6l-1 14H6L5 6"/>
                                                <path d="M10 11v6"/>
                                                <path d="M14 11v6"/>
                                            </svg>
                                        </button>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-icon">📦</div>
                                        <strong>No hay productos registrados</strong>
                                        <p>Añade el primer producto al inventario.</p>
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


{{-- =====================================================
     MODAL CREAR PRODUCTO
====================================================== --}}

<div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalLabel">Registrar Nuevo Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('productos.crearProducto') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <h6 class="text-primary fw-bold mb-3">Datos del Catálogo</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label for="codigo" class="form-label">Código de Barras *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="codigo" id="codigo" placeholder="Haz clic aquí para escanear" required>
                                <button class="btn btn-primary fw-bold" type="button" id="btnEscanear">Escanear</button>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <label for="nombre" class="form-label">Nombre del Producto *</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej. Hamburguesa de Res" required>
                        </div>

                        <div class="col-md-4">
                            <label for="idCategoria" class="form-label">Categoría *</label>
                            <select class="form-select" name="idCategoria" id="idCategoria" required>
                                <option value="">Seleccione una categoría...</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->idCategoria }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="precioVenta" class="form-label">Precio de Venta ($) *</label>
                            <input type="number" step="0.01" class="form-control" name="precioVenta" id="precioVenta" required>
                        </div>

                        <div class="col-md-4">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" name="descripcion" id="descripcion">
                        </div>

                        <div class="col-12">
                            <label for="imagen" class="form-label">Imagen del Producto</label>
                            <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*">
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-outline-secondary text-start" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseAbastecimiento" aria-expanded="false" aria-controls="collapseAbastecimiento">
                            + Añadir Abastecimiento Inicial (Opcional)
                        </button>
                    </div>

                    <div class="collapse" id="collapseAbastecimiento">
                        <div class="card card-body border-secondary mb-3">
                            <h6 class="text-primary fw-bold mb-3">Datos de Abastecimiento</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="idProveedor" class="form-label">Proveedor</label>
                                    <select class="form-select" name="idProveedor" id="idProveedor">
                                        <option value="">Seleccione proveedor...</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->idProveedor }}">{{ $proveedor->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="stockInicial" class="form-label">Stock Inicial</label>
                                    <input type="number" class="form-control" name="stockInicial" id="stockInicial" value="0" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="precioCompra" class="form-label">Costo de Compra (Lote) ($)</label>
                                    <input type="number" step="0.01" class="form-control" name="precioCompra" id="precioCompra">
                                </div>
                                <div class="col-md-6">
                                    <label for="fechaVencimiento" class="form-label">Fecha de Vencimiento</label>
                                    <input type="date" class="form-control" name="fechaVencimiento" id="fechaVencimiento">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-solid">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- =====================================================
     MODAL EDITAR PRODUCTO
====================================================== --}}

<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalLabelEditar" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalLabelEditar">Editar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="" method="POST" id="formEditarProducto" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <h6 class="text-primary fw-bold mb-3">Datos del Catálogo</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label for="edit_codigo" class="form-label">Código de Barras *</label>
                            <input type="text" class="form-control bg-light" name="codigo" id="edit_codigo" readonly required>
                        </div>

                        <div class="col-md-7">
                            <label for="edit_nombre" class="form-label">Nombre del Producto *</label>
                            <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                        </div>

                        <div class="col-md-4">
                            <label for="edit_idCategoria" class="form-label">Categoría *</label>
                            <select class="form-select" name="idCategoria" id="edit_idCategoria" required>
                                <option value="">Seleccione categoría...</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->idCategoria }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="edit_precioVenta" class="form-label">Precio de Venta ($) *</label>
                            <input type="number" step="0.01" class="form-control" name="precioVenta" id="edit_precioVenta" required>
                        </div>

                        <div class="col-md-4">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" name="descripcion" id="edit_descripcion">
                        </div>

                        <div class="col-md-12">
                            <label for="edit_imagen" class="form-label">Actualizar Imagen (Opcional)</label>
                            <input type="file" class="form-control" name="imagen" id="edit_imagen" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-solid">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- =====================================================
     MODAL REGISTRAR INGRESO
====================================================== --}}

<div class="modal fade" id="modalRegistrarIngreso" tabindex="-1" aria-labelledby="modalLabelIngreso" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalLabelIngreso">Registrar Ingreso de Productos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('inventario.ingresos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_modal" value="modalRegistrarIngreso">
                <div class="modal-body">
                    <h6 class="text-primary fw-bold mb-3">Datos de abastecimiento</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ingreso_codigoProducto" class="form-label">Producto *</label>
                            <select class="form-select @error('codigoProducto') is-invalid @enderror" name="codigoProducto" id="ingreso_codigoProducto" required>
                                <option value="">Seleccione producto...</option>
                                @foreach ($productos as $producto)
                                    <option value="{{ $producto->codigo }}" @selected(old('codigoProducto') === $producto->codigo)>{{ $producto->nombre }} ({{ $producto->codigo }})</option>
                                @endforeach
                            </select>
                            @error('codigoProducto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ingreso_idProveedor" class="form-label">Proveedor *</label>
                            <select class="form-select @error('idProveedor') is-invalid @enderror" name="idProveedor" id="ingreso_idProveedor" required>
                                <option value="">Seleccione proveedor...</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->idProveedor }}" @selected((string) old('idProveedor') === (string) $proveedor->idProveedor)>{{ $proveedor->nombre }}</option>
                                @endforeach
                            </select>
                            @error('idProveedor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ingreso_cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control @error('cantidad') is-invalid @enderror" name="cantidad" id="ingreso_cantidad" value="{{ old('cantidad') }}" min="1" step="1" required>
                            @error('cantidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ingreso_precioCompra" class="form-label">Costo unitario ($) *</label>
                            <input type="number" step="0.01" min="0" class="form-control @error('precioCompra') is-invalid @enderror" name="precioCompra" id="ingreso_precioCompra" value="{{ old('precioCompra') }}" required>
                            @error('precioCompra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ingreso_fechaVencimiento" class="form-label">Fecha de vencimiento *</label>
                            <input type="date" class="form-control @error('fechaVencimiento') is-invalid @enderror" name="fechaVencimiento" id="ingreso_fechaVencimiento" value="{{ old('fechaVencimiento') }}" min="{{ now()->toDateString() }}" required>
                            @error('fechaVencimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-solid">Registrar ingreso</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- =====================================================
     MODAL REGISTRAR SALIDA / MERMA
====================================================== --}}

<div class="modal fade" id="modalRegistrarSalida" tabindex="-1" aria-labelledby="modalLabelSalida" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalLabelSalida">Registrar salida por merma</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('inventario.salidas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_modal" value="modalRegistrarSalida">
                <div class="modal-body">
                    <h6 class="text-primary fw-bold mb-3">Datos de la salida por merma</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="salida_codigoProducto" class="form-label">Producto *</label>
                            <select class="form-select @error('codigoProducto') is-invalid @enderror" name="codigoProducto" id="salida_codigoProducto" required>
                                <option value="">Seleccione producto...</option>
                                @foreach ($productos as $producto)
                                    <option value="{{ $producto->codigo }}" data-stock="{{ $producto->stock }}" @selected(old('codigoProducto') === $producto->codigo)>{{ $producto->nombre }} ({{ $producto->codigo }}) — stock {{ $producto->stock }}</option>
                                @endforeach
                            </select>
                            @error('codigoProducto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="salida_motivo" class="form-label">Motivo *</label>
                            <select class="form-select @error('motivo') is-invalid @enderror" name="motivo" id="salida_motivo" required>
                                <option value="">Seleccione motivo...</option>
                                <option value="Merma por Vencimiento" @selected(old('motivo') === 'Merma por Vencimiento')>Merma por vencimiento</option>
                                <option value="Merma por Daño" @selected(old('motivo') === 'Merma por Daño')>Merma por daño</option>
                            </select>
                            @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="salida_cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control @error('cantidad') is-invalid @enderror" name="cantidad" id="salida_cantidad" value="{{ old('cantidad') }}" min="1" step="1" required>
                            @error('cantidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-solid">Registrar salida</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@vite(['resources/js/producto.js'])
