-- Usuario
INSERT INTO Usuario (nombre, contrasena, rol, email, activo) VALUES 
('Admin Principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@congelandia.cl', 'Administrador', TRUE),
('Cajero 1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cajero1@congelandia.cl','Cajero', TRUE),
('Cajero 2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cajero2@congelandia.cl','Cajero', TRUE),
('Supervisor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','supervisor@congelandia.cl', 'Supervisor', TRUE),
('Bodeguero', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','bodeguero@congelandia.cl', 'Bodeguero', TRUE);
INSERT INTO Usuario (nombre, email, contrasena, rol, activo) 
VALUES ('Admin Principal', 'admin@congelandia.com', '12345', 'Administrador', 1);

-- Cliente
INSERT INTO Cliente (rutCliente, nombre, telefono, saldoDeuda) VALUES 
('12345678-9', 'María González', '+56987654321', 15000.00),
('87654321-K', 'Juan Pérez', '+56912345678', 8500.00),
('11111111-1', 'Ana Martínez', '+56911112222', 0.00),
('22222222-2', 'Carlos Soto', '+56933334444', 0.00),
('33333333-3', 'Rosa Fuentes', '+56955556666', 3200.00);

-- Categoria
INSERT INTO Categoria (nombre) VALUES 
('Bebidas'),
('Panadería'),
('Lácteos'),
('Despensa'),
('Verduras');

-- Producto
INSERT INTO Producto (codigo, nombre, descripcion, idCategoria) VALUES 
('PROD-001', 'Coca Cola 2L', 'Bebida desechable 2 Litros', 1),
('PROD-002', 'Pan de Molde', 'Pan de molde blanco 500g', 2),
('PROD-003', 'Leche Entera 1L', 'Leche entera 1 Litro caja', 3),
('PROD-004', 'Arroz 1kg', 'Arroz grado 2 bolsa 1kg', 4),
('PROD-005', 'Tomate 1kg', 'Tomate fresco granel', 5);

-- Lista_Precio
INSERT INTO Lista_Precio (codigoProducto, precioVenta, fechaInicio, fechaFin) VALUES 
('PROD-001', 2000.00, '2026-01-01 00:00:00', NULL),
('PROD-002', 1500.00, '2026-01-01 00:00:00', NULL),
('PROD-003', 1000.00, '2026-01-01 00:00:00', NULL),
('PROD-004', 1200.00, '2026-01-01 00:00:00', NULL),
('PROD-005', 1600.00, '2026-01-01 00:00:00', NULL);

-- Promocion
INSERT INTO Promocion (codigoProducto, porcentajeDescuento, fechaInicio, fechaFin) VALUES 
('PROD-001', 10.00, '2026-09-01 00:00:00', '2026-09-30 23:59:59'),
('PROD-002', 15.00, '2026-09-10 00:00:00', '2026-09-20 23:59:59'),
('PROD-003', 5.00, '2026-09-01 00:00:00', '2026-09-15 23:59:59'),
('PROD-004', 20.00, '2026-09-15 00:00:00', '2026-09-25 23:59:59'),
('PROD-005', 10.00, '2026-09-18 00:00:00', '2026-09-28 23:59:59');

-- Proveedor
INSERT INTO Proveedor (nombre, telefono, correo) VALUES 
('Distribuidora del Norte', '+56998877665', 'contacto@distribuidoranorte.cl'),
('Frío Express Ltda.', '+56944332211', 'ventas@frioexpress.cl'),
('Alimentos del Sur', '+56977889900', 'ventas@alimentosdelsur.cl'),
('Comercializadora Central', '+56911223344', 'info@comercialcentral.cl'),
('Importadora Congelados SA', '+56955667788', 'contacto@congeladossa.cl');

-- Ingreso
INSERT INTO Ingreso (fecha, idProveedor, idUsuario, totalCompra) VALUES 
('2026-09-01 08:30:00', 1, 1, 70000.00),
('2026-09-03 09:15:00', 2, 1, 52000.00),
('2026-09-05 10:00:00', 3, 5, 120000.00),
('2026-09-10 11:30:00', 4, 5, 45000.00),
('2026-09-15 14:00:00', 5, 1, 95000.00);

-- Detalle_Ingreso
INSERT INTO Detalle_Ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES 
(1, 'PROD-001', 50, 1400.00, '2027-09-01'),
(2, 'PROD-002', 40, 900.00, '2026-10-01'),
(3, 'PROD-003', 60, 650.00, '2026-09-25'),
(4, 'PROD-004', 50, 750.00, '2027-12-31'),
(5, 'PROD-005', 30, 1000.00, '2026-09-22');

-- Tipo_Salida
INSERT INTO Tipo_Salida (nombre) VALUES 
('Venta'),
('Merma por Vencimiento'),
('Merma por Daño');

-- Salida
INSERT INTO Salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES 
('2026-09-16 10:30:00', 1, 2, '12345678-9', 7000.00),
('2026-09-16 11:10:00', 1, 2, NULL, 4000.00),
('2026-09-17 11:20:00', 1, 3, '87654321-K', 6000.00),
('2026-09-18 15:45:00', 2, 5, NULL, 0.00),
('2026-09-19 09:00:00', 1, 2, '11111111-1', 1200.00);

-- Detalle_Salida
INSERT INTO Detalle_Salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES 
(1, 'PROD-001', 2, 2000.00),
(1, 'PROD-003', 3, 1000.00),
(2, 'PROD-001', 2, 2000.00),
(3, 'PROD-003', 2, 3000.00),
(4, 'PROD-003', 5, 0.00),
(5, 'PROD-004', 1, 1200.00);

-- Metodo_Pago
INSERT INTO Metodo_Pago (nombre) VALUES 
('Efectivo'),
('Tarjeta de Débito'),
('Tarjeta de Crédito'),
('Transferencia'),
('Fiado / Pendiente');

-- Pago_Salida
INSERT INTO Pago_Salida (idSalida, idMetodo, montoPagado) VALUES 
(1, 1, 7000.00),
(2, 2, 4000.00),
(3, 5, 6000.00),
(4, 1, 0.00),
(5, 1, 1200.00);

-- Tipo_Devolucion
INSERT INTO Tipo_Devolucion (nombre, reintegraStock) VALUES 
('Cambio por Garantía', TRUE),
('Falla de Origen', FALSE),
('Devolución por Error de Caja', TRUE),
('Producto Deteriorado en Local', FALSE),
('Satisfacción Garantizada', TRUE);

-- Devolucion
INSERT INTO Devolucion (fecha, idSalida, idTipo, codigoProducto, cantidad, comentario) VALUES 
('2026-09-16 12:00:00', 1, 1, 'PROD-003', 1, 'El cliente indicó que el envase venía abierto.'),
('2026-09-17 14:30:00', 3, 3, 'PROD-003', 1, 'Cobro duplicado por error en sistema.'),
('2026-09-18 16:00:00', 4, 2, 'PROD-003', 2, 'Devolución directa por vencimiento próximo.'),
('2026-09-19 10:00:00', 5, 5, 'PROD-004', 1, 'Cliente cambió de opinión sobre el producto.'),
('2026-09-19 11:15:00', 2, 1, 'PROD-001', 1, 'Tapa defectuosa en la bebida.');