@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Inventario</h2>
            <!-- Botón que activa el Modal -->
            <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal"
                data-bs-target="#modalCrearProducto">
                + Añadir Producto
            </button>
        </div>

        <!-- Tabla de inventario -->
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Categoria</th>
                    <th scope="col">Proveedor</th>
                    <th scope="col">Costo</th>
                    <th scope="col">Precio</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre }} <br>
                            <small class="text-muted">{{ $producto->codigo }}</small>
                        </td>
                        <td>{{ $producto->categoria ?? 'Sin categoría' }}</td>
                        <td>{{ $producto->proveedor ?? 'Sin proveedor' }}</td>
                        <td>${{ number_format($producto->costo ?? 0, 0, ',', '.') }}</td>
                        <td>${{ number_format($producto->precio ?? 0, 0, ',', '.') }}</td>
                        <td>{{ number_format($producto->stock ?? 0, 0, ',', '.') }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary">Editar</button>
                            <button class="btn btn-sm btn-danger">Borrar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- MODAL DE CREACIÓN DE PRODUCTO -->
    <div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- modal-lg para que sea ancho y quepan 2 columnas -->
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalLabel">Registrar Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <!-- Formulario conectado a la ruta que configuraste previamente -->
                <form action="{{ route('productos.crearProducto') }}" method="POST">
                    @csrf <!-- Token de seguridad obligatorio en Laravel -->
                    <div class="modal-body">
                        <!-- Fila 1: Datos Básicos -->
                        <h6 class="text-primary mb-3">Datos del Catálogo</h6>
                        <div class="row g-3 mb-3">
                            <!-- Código de Barras con Botón Integrado -->
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

                            <!-- Nombre del Producto -->
                            <div class="col-md-7">
                                <label for="nombre" class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" name="nombre" id="nombre"
                                    placeholder="Ej. Hamburguesa de Res" required>
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-6">
                                <label for="idCategoria" class="form-label">Categoría *</label>
                                <select class="form-select" name="idCategoria" id="idCategoria" required>
                                    <option value="">Seleccione una categoría...</option>
                                    <option value="1">Carnes</option>
                                    <option value="2">Bebidas</option>
                                </select>
                            </div>

                            <!-- Descripción -->
                            <div class="col-md-6">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="descripcion" id="descripcion">
                            </div>

                        </div>

                        <hr>

                        <!-- Fila 2: Precios y Abastecimiento Inicial -->
                        <h6 class="text-primary mb-3">Precios y Abastecimiento Inicial</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="precioVenta" class="form-label">Precio de Venta ($) *</label>
                                <input type="number" step="0.01" class="form-control" name="precioVenta" id="precioVenta"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label for="idProveedor" class="form-label">Proveedor *</label>
                                <select class="form-select" name="idProveedor" id="idProveedor" required>
                                    <option value="">Seleccione proveedor...</option>
                                    <!-- Iterar proveedores reales -->
                                    <option value="1">Proveedor de Hielo</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="stockInicial" class="form-label">Stock Inicial *</label>
                                <input type="number" class="form-control" name="stockInicial" id="stockInicial" value="0"
                                    min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="precioCompra" class="form-label">Costo de Compra (Lote) ($) *</label>
                                <input type="number" step="0.01" class="form-control" name="precioCompra" id="precioCompra"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="fechaVencimiento" class="form-label">Fecha de Vencimiento *</label>
                                <input type="date" class="form-control" name="fechaVencimiento" id="fechaVencimiento"
                                    required>
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
@endsection