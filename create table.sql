CREATE DATABASE IF NOT EXISTS congelandia_db;
USE congelandia_db;
select * from lista_precio;
select * from ingreso;
select * from detalle_ingreso;
select * from imagen_producto;

-- 1. Desactivar revisión de llaves foráneas para borrar sin errores de dependencia
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS detalle_ingreso;
DROP TABLE IF EXISTS ingreso;
DROP TABLE IF EXISTS detalle_salida;
DROP TABLE IF EXISTS devolucion;
DROP TABLE IF EXISTS pago_salida;
DROP TABLE IF EXISTS salida;
DROP TABLE IF EXISTS usuario;
DROP TABLE IF EXISTS cliente;
DROP TABLE IF EXISTS lista_precio;
DROP TABLE IF EXISTS promocion;
DROP TABLE IF EXISTS producto;
DROP TABLE IF EXISTS categoria;
DROP TABLE IF EXISTS proveedor;
DROP TABLE IF EXISTS tipo_salida;
DROP TABLE IF EXISTS tipo_devolucion;
DROP TABLE IF EXISTS metodo_pago;
DROP TABLE IF EXISTS imagen_producto;

-- TABLAS QUE NO SE DEBERIAN BORRAR
-- drop table log_errors;
CREATE TABLE IF NOT EXISTS Log_Errors (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mensaje TEXT NULL,
  archivo VARCHAR(500) NULL,
  linea INT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
-- drop table versiones;
CREATE TABLE IF NOT EXISTS versiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL,
    comentarios TEXT,
    activa BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 2. Reactivar revisión de llaves foráneas para proteger las nuevas tablas
SET FOREIGN_KEY_CHECKS = 1;

-- 3. Usuarios y Clientes
CREATE TABLE usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL,
    email varchar(100) not null unique,
    activo BOOLEAN DEFAULT TRUE,
    deleted_at DATETIME NULL DEFAULT NULL
);

CREATE TABLE cliente (
    rutCliente VARCHAR(15) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    saldoDeuda DECIMAL(10, 2) DEFAULT 0.00,
    deleted_at DATETIME NULL DEFAULT NULL
);

-- 4. Catálogo de Productos
CREATE TABLE categoria (
    idCategoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    deleted_at DATETIME NULL DEFAULT NULL
);

CREATE TABLE producto (
    codigo VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    idCategoria INT,
    deleted_at DATETIME NULL DEFAULT NULL,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (idCategoria)
        REFERENCES categoria(idCategoria) ON DELETE SET NULL
);

-- 5. Gestión de Precios y Promociones Temporales
CREATE TABLE lista_precio (
    idPrecio INT AUTO_INCREMENT PRIMARY KEY,
    codigoProducto VARCHAR(50) NOT NULL,
    precioVenta DECIMAL(10, 2) NOT NULL,
    fechaInicio DATETIME NOT NULL,
    fechaFin DATETIME,
    CONSTRAINT fk_precio_producto FOREIGN KEY (codigoProducto)
        REFERENCES producto(codigo) ON DELETE CASCADE
);

CREATE TABLE promocion (
    idPromocion INT AUTO_INCREMENT PRIMARY KEY,
    codigoProducto VARCHAR(50) NOT NULL,
    porcentajeDescuento DECIMAL(5, 2) NOT NULL,
    fechaInicio DATETIME NOT NULL,
    fechaFin DATETIME NOT NULL,
    CONSTRAINT fk_promocion_producto FOREIGN KEY (codigoProducto)
        REFERENCES producto(codigo) ON DELETE CASCADE
);

-- 6. Proveedores e Ingresos
CREATE TABLE proveedor (
    idProveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    deleted_at DATETIME NULL DEFAULT NULL
);

CREATE TABLE ingreso (
    idIngreso INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    idProveedor INT NOT NULL,
    idUsuario INT NOT NULL,
    totalCompra DECIMAL(12, 2) NOT NULL,
    CONSTRAINT fk_ingreso_proveedor FOREIGN KEY (idProveedor) REFERENCES proveedor(idProveedor),
    CONSTRAINT fk_ingreso_usuario FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario)
);

CREATE TABLE detalle_ingreso (
    idDetalle INT AUTO_INCREMENT PRIMARY KEY,
    idIngreso INT NOT NULL,
    codigoProducto VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL,
    precioCompra DECIMAL(10, 2) NOT NULL,
    fechaVencimiento DATE NOT NULL,
    CONSTRAINT fk_detingreso_ingreso FOREIGN KEY (idIngreso) REFERENCES ingreso(idIngreso) ON DELETE CASCADE,
    CONSTRAINT fk_detingreso_producto FOREIGN KEY (codigoProducto) REFERENCES producto(codigo)
);

-- 7. Salidas (Ventas y Mermas)
CREATE TABLE tipo_salida (
    idTipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE salida (
    idSalida INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    idTipo INT NOT NULL,
    idUsuario INT NOT NULL,
    rutCliente VARCHAR(15),
    totalSalida DECIMAL(12, 2) DEFAULT 0.00,
    CONSTRAINT fk_salida_tipo FOREIGN KEY (idTipo) REFERENCES tipo_salida(idTipo),
    CONSTRAINT fk_salida_usuario FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario),
    CONSTRAINT fk_salida_cliente FOREIGN KEY (rutCliente) REFERENCES cliente(rutCliente),
	deleted_at DATETIME NULL DEFAULT NULL
);

CREATE TABLE detalle_salida (
    idDetalle INT AUTO_INCREMENT PRIMARY KEY,
    idSalida INT NOT NULL,
    codigoProducto VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL,
    precioCobrado DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_detsalida_salida FOREIGN KEY (idSalida) REFERENCES salida(idSalida) ON DELETE CASCADE,
    CONSTRAINT fk_detsalida_producto FOREIGN KEY (codigoProducto) REFERENCES producto(codigo)
);

-- 8. Pagos Combinados
CREATE TABLE metodo_pago (
    idMetodo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE pago_salida (
    idPago INT AUTO_INCREMENT PRIMARY KEY,
    idSalida INT NOT NULL,
    idMetodo INT NOT NULL,
    montoPagado DECIMAL(12, 2) NOT NULL,
    fechaPago date not null,
    CONSTRAINT fk_pago_salida FOREIGN KEY (idSalida) REFERENCES salida(idSalida) ON DELETE CASCADE,
    CONSTRAINT fk_pago_metodo FOREIGN KEY (idMetodo) REFERENCES metodo_pago(idMetodo)
);

-- 9. Devoluciones Normadas
CREATE TABLE tipo_devolucion (
    idTipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    reintegraStock BOOLEAN NOT NULL
);

CREATE TABLE devolucion (
    idDevolucion INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    idSalida INT NOT NULL,
    idTipo INT NOT NULL,
    codigoProducto VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL,
    comentario TEXT,
    CONSTRAINT fk_devolucion_salida FOREIGN KEY (idSalida) REFERENCES salida(idSalida),
    CONSTRAINT fk_devolucion_tipo FOREIGN KEY (idTipo) REFERENCES tipo_devolucion(idTipo),
    CONSTRAINT fk_devolucion_producto FOREIGN KEY (codigoProducto) REFERENCES producto(codigo)
);

CREATE TABLE imagen_producto(
	id INT AUTO_INCREMENT PRIMARY KEY,
    rutaImagen TEXT,
	codigoProducto VARCHAR(50) NOT NULL,
	alt TEXT,
    descripcion TEXT
);
