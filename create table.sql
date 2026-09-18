CREATE DATABASE IF NOT EXISTS congelandia_db;
USE congelandia_db;
	
-- 1. Usuarios y Clientes
CREATE TABLE Usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    deleted_at DATETIME NULL DEFAULT NULL -- Campo para Soft Deletes en Laravel
);

CREATE TABLE Cliente (
    rutCliente VARCHAR(15) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    saldoDeuda DECIMAL(10, 2) DEFAULT 0.00,
    deleted_at DATETIME NULL DEFAULT NULL -- Campo para Soft Deletes
);

-- 2. Catálogo de Productos
CREATE TABLE Categoria (
    idCategoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    deleted_at DATETIME NULL DEFAULT NULL -- Campo para Soft Deletes
);

CREATE TABLE Producto (
    codigo VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    idCategoria INT,
    deleted_at DATETIME NULL DEFAULT NULL, -- Campo para Soft Deletes
    CONSTRAINT fk_producto_categoria FOREIGN KEY (idCategoria) 
        REFERENCES Categoria(idCategoria) ON DELETE SET NULL
);

-- 3. Gestión de Precios y Promociones Temporales (SIN SOFT DELETE)
CREATE TABLE Lista_Precio (
    idPrecio INT AUTO_INCREMENT PRIMARY KEY,
    codigoProducto VARCHAR(50) NOT NULL,
    precioVenta DECIMAL(10, 2) NOT NULL,
    fechaInicio DATETIME NOT NULL,
    fechaFin DATETIME, -- Null si es el precio actual vigente
    CONSTRAINT fk_precio_producto FOREIGN KEY (codigoProducto) 
        REFERENCES Producto(codigo) ON DELETE CASCADE
);

CREATE TABLE Promocion (
    idPromocion INT AUTO_INCREMENT PRIMARY KEY,
    codigoProducto VARCHAR(50) NOT NULL,
    porcentajeDescuento DECIMAL(5, 2) NOT NULL,
    fechaInicio DATETIME NOT NULL,
    fechaFin DATETIME NOT NULL,
    CONSTRAINT fk_promocion_producto FOREIGN KEY (codigoProducto) 
        REFERENCES Producto(codigo) ON DELETE CASCADE
);

-- 4. Proveedores e Ingresos (Compras al proveedor)
CREATE TABLE Proveedor (
    idProveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    deleted_at DATETIME NULL DEFAULT NULL -- Campo para Soft Deletes
);

-- (LAS SIGUIENTES TABLAS TRANSACCIONALES NO LLEVAN SOFT DELETE)
CREATE TABLE Ingreso (
    idIngreso INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    idProveedor INT NOT NULL,
    idUsuario INT NOT NULL,
    totalCompra DECIMAL(12, 2) NOT NULL,
    CONSTRAINT fk_ingreso_proveedor FOREIGN KEY (idProveedor) REFERENCES Proveedor(idProveedor),
    CONSTRAINT fk_ingreso_usuario FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario)
);

CREATE TABLE Detalle_Ingreso (
    idDetalle INT AUTO_INCREMENT PRIMARY KEY,
    idIngreso INT NOT NULL,
    codigoProducto VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL,
    precioCompra DECIMAL(10, 2) NOT NULL,
    fechaVencimiento DATE NOT NULL,
    CONSTRAINT fk_detingreso_ingreso FOREIGN KEY (idIngreso) REFERENCES Ingreso(idIngreso) ON DELETE CASCADE,
    CONSTRAINT fk_detingreso_producto FOREIGN KEY (codigoProducto) REFERENCES Producto(codigo)
);

-- 5. Salidas (Ventas y Mermas)
CREATE TABLE Tipo_Salida (
    idTipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL -- Ej: 'Venta', 'Merma por Vencimiento', 'Merma por Daño'
);

CREATE TABLE Salida (
    idSalida INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    idTipo INT NOT NULL,
    idUsuario INT NOT NULL,
    rutCliente VARCHAR(15), -- Null si es Merma o venta a cliente anónimo
    totalSalida DECIMAL(12, 2) DEFAULT 0.00,
    CONSTRAINT fk_salida_tipo FOREIGN KEY (idTipo) REFERENCES Tipo_Salida(idTipo),
    CONSTRAINT fk_salida_usuario FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario),
    CONSTRAINT fk_salida_cliente FOREIGN KEY (rutCliente) REFERENCES Cliente(rutCliente)
);

CREATE TABLE Detalle_Salida (
    idDetalle INT AUTO_INCREMENT PRIMARY KEY,
    idSalida INT NOT NULL,
    codigoProducto VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL,
    precioCobrado DECIMAL(10, 2) NOT NULL, -- 0 en caso de ser Merma
    CONSTRAINT fk_detsalida_salida FOREIGN KEY (idSalida) REFERENCES Salida(idSalida) ON DELETE CASCADE,
    CONSTRAINT fk_detsalida_producto FOREIGN KEY (codigoProducto) REFERENCES Producto(codigo)
);

-- 6. Pagos Combinados
CREATE TABLE Metodo_Pago (
    idMetodo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL -- Ej: 'Efectivo', 'Tarjeta de Débito', 'Transferencia'
);

CREATE TABLE Pago_Salida (
    idPago INT AUTO_INCREMENT PRIMARY KEY,
    idSalida INT NOT NULL,
    idMetodo INT NOT NULL,
    montoPagado DECIMAL(12, 2) NOT NULL,
    CONSTRAINT fk_pago_salida FOREIGN KEY (idSalida) REFERENCES Salida(idSalida) ON DELETE CASCADE,
    CONSTRAINT fk_pago_metodo FOREIGN KEY (idMetodo) REFERENCES Metodo_Pago(idMetodo)
);

-- 7. Devoluciones Normadas
CREATE TABLE Tipo_Devolucion (
    idTipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL, -- Ej: 'Cambio por Garantía', 'Falla de Origen'
    reintegraStock BOOLEAN NOT NULL -- True si el producto vuelve a estar disponible para vender
);

CREATE TABLE Devolucion (
    idDevolucion INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    idSalida INT NOT NULL,
    idTipo INT NOT NULL,
    codigoProducto VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL,
    comentario TEXT,
    CONSTRAINT fk_devolucion_salida FOREIGN KEY (idSalida) REFERENCES Salida(idSalida),
    CONSTRAINT fk_devolucion_tipo FOREIGN KEY (idTipo) REFERENCES Tipo_Devolucion(idTipo),
    CONSTRAINT fk_devolucion_producto FOREIGN KEY (codigoProducto) REFERENCES Producto(codigo)
);