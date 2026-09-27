USE congelandia_db;

-- 1. Usuarios y Clientes
-- Aquí está el valor de prueba oculto (Usuario ID 2)
INSERT INTO Usuario (nombre, contrasena, rol, activo) VALUES 
('Admin Principal', 'hash_admin123', 'Administrador', TRUE),
('Pingüino Espía', 'secreto_congelado', 'Jefe', FALSE); 

INSERT INTO Cliente (rutCliente, nombre, telefono, saldoDeuda) VALUES 
('11111111-1', 'Cliente General', '+56912345678', 0.00);

-- 2. Catálogo de Productos
INSERT INTO Categoria (nombre) VALUES 
('Carnes');

INSERT INTO Producto (codigo, nombre, descripcion, idCategoria) VALUES 
('780123456789', 'Hamburguesa de Res', 'Pack 4 unidades congeladas', 1);

-- 3. Gestión de Precios y Promociones
INSERT INTO Lista_Precio (codigoProducto, precioVenta, fechaInicio, fechaFin) VALUES 
('780123456789', 4500.00, NOW(), NULL);

INSERT INTO Promocion (codigoProducto, porcentajeDescuento, fechaInicio, fechaFin) VALUES 
('780123456789', 10.00, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY));

-- 4. Proveedores e Ingresos
INSERT INTO Proveedor (nombre, telefono, correo) VALUES 
('Proveedor Frio Sur', '+56987654321', 'ventas@friosur.cl');

INSERT INTO Ingreso (fecha, idProveedor, idUsuario, totalCompra) VALUES 
(NOW(), 1, 1, 39000.00);

INSERT INTO Detalle_Ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES 
(1, '780123456789', 10, 3900.00, '2026-12-31');

-- 5. Salidas (Ventas y Mermas)
INSERT INTO Tipo_Salida (nombre) VALUES 
('Venta');

INSERT INTO Salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES 
(NOW(), 1, 1, '11111111-1', 4500.00);

INSERT INTO Detalle_Salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES 
(1, '780123456789', 1, 4500.00);

-- 6. Pagos Combinados
INSERT INTO Metodo_Pago (nombre) VALUES 
('Efectivo');

INSERT INTO Pago_Salida (idSalida, idMetodo, montoPagado) VALUES 
(1, 1, 4500.00);

-- 7. Devoluciones Normadas
INSERT INTO Tipo_Devolucion (nombre, reintegraStock) VALUES 
('Falla de Origen', FALSE);

INSERT INTO Devolucion (fecha, idSalida, idTipo, codigoProducto, cantidad, comentario) VALUES 
(NOW(), 1, 1, '780123456789', 1, 'Empaque dañado al momento de abrir');