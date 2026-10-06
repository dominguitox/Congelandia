@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Inventario</h2>
            <div>

                <!-- Botón que activa el Modal -->
                <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal"
                    data-bs-target="#modalCrearProducto">
                    + Añadir Producto
                </button>
                <button type="button" class="btn btn-secondary fw-bold" data-bs-toggle="modal"
                    data-bs-target="#modalRegistrarIngreso">
                    + Registrar ingreso
                </button>
                <button type="button" class="btn btn-secondary fw-bold" data-bs-toggle="modal"
                    data-bs-target="#modalRegistrarSalida">
                    + Registrar salida o merma
                </button>
            </div>
        </div>

        <!-- Tabla de inventario -->
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Alerta</th>
                    <th scope="col">Producto</th>
                    <th scope="col">Categoria</th>
                    <th scope="col">Proveedor</th>
                    <th scope="col">Costo</th>
                    <th scope="col">Precio Venta</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                    <tr>
                        <td>
                            @if($producto->stock == 0)
                                <span class="badge bg-danger">Sin Stock</span>
                            @elseif($producto->stock < 10)
                                <span class="badge bg-warning">Bajo Stock</span>
                            @else
                                <span class="badge bg-success">Stock OK</span>
                            @endif
                        </td>
                        <td>{{ $producto->nombre }} <br>
                            <small class="text-muted">{{ $producto->codigo }}</small>
                        </td>
                        <td>{{ $producto->categoria ?? 'Sin categoría' }}</td>
                        <td>{{ $producto->proveedor ?? 'Sin proveedor' }}</td>
                        <td>${{ number_format($producto->costo ?? 0, 0, ',', '.') }}</td>
                        <td>${{ number_format($producto->precio ?? 0, 0, ',', '.') }}</td>
                        <td>{{ number_format($producto->stock ?? 0, 0, ',', '.') }}</td>
                        <td>
                            <button class="btn btn-sm btn-success" type="button" data-bs-toggle="modal"
                                data-bs-target="#modalRegistrarIngreso"
                                onclick="registrarIngreso($producto->codigo)">Ingreso</button>
                            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal"
                                data-bs-target="#modalEditarProducto" data-codigo="{{ $producto->codigo }}"
                                data-nombre="{{ $producto->nombre }}" data-categoria="{{ $producto->idCategoria }}"
                                data-precio="{{ $producto->precioVenta }}" data-descripcion="{{ $producto->descripcion }}"
                                onclick="editarProducto(this)">
                                Editar
                            </button>

                            <button class="btn btn-sm btn-danger">Borrar</button>

                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
    <!-- MODAL DE CREACIÓN DE PRODUCTO -->
    <div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalLabel">Registrar Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                @if($errors->any()) {{ dd($errors) }} @endif

                <form action="{{ route('productos.crearProducto') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Fila 1: Datos Básicos y Precio (Obligatorios) -->
                        <h6 class="text-primary mb-3">Datos del Catálogo</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="codigo" class="form-label">Código de Barras *</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="codigo" id="codigo"
                                        placeholder="Haz clic aquí para escanear" required>
                                    <button class="btn btn-primary fw-bold" type="button" id="btnEscanear">
                                        Escanear
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <label for="nombre" class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" name="nombre" id="nombre"
                                    placeholder="Ej. Hamburguesa de Res" required>
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

                            <!-- PRECIO MOVIDO AQUÍ (OBLIGATORIO) -->
                            <div class="col-md-4">
                                <label for="precioVenta" class="form-label">Precio de Venta ($) *</label>
                                <input type="number" step="0.01" class="form-control" name="precioVenta" id="precioVenta"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="descripcion" id="descripcion">
                            </div>
                        </div>

                        <hr>

                        <!-- Fila 2: Abastecimiento Inicial (Desplegable y Opcional) -->
                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-outline-secondary text-start" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseAbastecimiento" aria-expanded="false"
                                aria-controls="collapseAbastecimiento">
                                <i class="bi bi-chevron-down"></i> + Añadir Abastecimiento Inicial (Opcional)
                            </button>
                        </div>

                        <div class="collapse" id="collapseAbastecimiento">
                            <div class="card card-body border-secondary mb-3">
                                <h6 class="text-primary mb-3">Datos de Abastecimiento</h6>
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
                                        <input type="number" class="form-control" name="stockInicial" id="stockInicial"
                                            value="0" min="0">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="precioCompra" class="form-label">Costo de Compra (Lote) ($)</label>
                                        <input type="number" step="0.01" class="form-control" name="precioCompra"
                                            id="precioCompra">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fechaVencimiento" class="form-label">Fecha de Vencimiento</label>
                                        <input type="date" class="form-control" name="fechaVencimiento"
                                            id="fechaVencimiento">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- MODAL EDITAR PRODUCTO -->
    <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalLabelEditar" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalLabelEditar">Editar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form action="" method="POST" id="formEditarProducto">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <h6 class="text-primary mb-3">Datos del Catálogo</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="edit_codigo" class="form-label">Código de Barras *</label>
                                <input type="text" class="form-control bg-light" name="codigo" id="edit_codigo" readonly
                                    required>
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
                                <input type="number" step="0.01" class="form-control" name="precioVenta"
                                    id="edit_precioVenta" required>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="descripcion" id="edit_descripcion">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- MODAL REGISTRAR INGRESO -->
    <div class="modal fade" id="modalRegistrarIngreso" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalLabel">Registrar Ingreso de Productos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                @if($errors->any()) {{ dd($errors) }} @endif

                <form action="{{ route('productos.crearProducto') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Fila 1: Abastecimiento Inicial -->
                        <h6 class="text-primary mb-3">Datos de Abastecimiento</h6>
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
                                <input type="number" class="form-control" name="stockInicial" id="stockInicial" value="0"
                                    min="0">
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

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary fw-bold">Agregar Producto</button>
                        <button type="submit" class="btn btn-success fw-bold">Registrar Ingreso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- MODAL REGISTRAR MERMA -->
    <div class="modal fade" id="modalRegistrarSalida" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalLabel">Registrar Salida de Productos por Merma u Otro</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                @if($errors->any()) {{ dd($errors) }} @endif

                <form action="{{ route('productos.crearProducto') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Fila 1: Abastecimiento Inicial (Desplegable y Opcional) -->

                        <h6 class="text-primary mb-3">Datos de Abastecimiento</h6>
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
                                <input type="number" class="form-control" name="stockInicial" id="stockInicial" value="0"
                                    min="0">
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

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @vite(['resources/js/producto.js'])


@endsection