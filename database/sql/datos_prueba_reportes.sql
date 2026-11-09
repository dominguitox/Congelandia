-- DATOS DE PRUEBA PARA REPORTES DE CONGELANDIA (MySQL / MariaDB)
-- 25 ingresos + 75 ventas = 100 movimientos.
-- 75 detalles de ingreso, 225 detalles de venta y 75 pagos completos.
-- Preparado para los productos existentes PROD-001 a PROD-005.
-- Precios de venta basados en la base local al 06/11/2026.
-- Usar en una base de pruebas con las migraciones aplicadas.
-- Seleccionar primero la base de datos de Congelandia en el cliente SQL.
-- Ejecutar el archivo completo en una sola conexion y detenerse al primer error.
-- Si ocurre un error antes del COMMIT, ejecutar ROLLBACK en esa conexion.
-- No usar la opcion de continuar ante errores del cliente SQL.
-- Cada ejecucion agrega otros 100 movimientos; no es idempotente.
-- No borra datos ni altera productos, precios o promociones existentes.

START TRANSACTION;

-- Cambiar esta fecha si se necesita reproducir un periodo fijo.
SET @demo_fecha = CURRENT_DATE();
SET @demo_usuario = (SELECT idUsuario FROM usuario WHERE deleted_at IS NULL AND activo = 1 ORDER BY idUsuario LIMIT 1);
-- Proveedores activos disponibles; se reutiliza el primero si hay menos de cuatro.
SET @demo_proveedor_1 = (SELECT idProveedor FROM proveedor WHERE deleted_at IS NULL ORDER BY idProveedor LIMIT 1);
SET @demo_proveedor_2 = COALESCE((SELECT idProveedor FROM proveedor WHERE deleted_at IS NULL ORDER BY idProveedor LIMIT 1 OFFSET 1), @demo_proveedor_1);
SET @demo_proveedor_3 = COALESCE((SELECT idProveedor FROM proveedor WHERE deleted_at IS NULL ORDER BY idProveedor LIMIT 1 OFFSET 2), @demo_proveedor_1);
SET @demo_proveedor_4 = COALESCE((SELECT idProveedor FROM proveedor WHERE deleted_at IS NULL ORDER BY idProveedor LIMIT 1 OFFSET 3), @demo_proveedor_1);

-- Completar los catalogos necesarios sin depender de IDs fijos.
INSERT INTO tipo_salida (nombre) SELECT 'Venta' WHERE NOT EXISTS (SELECT 1 FROM tipo_salida WHERE LOWER(TRIM(nombre)) = 'venta' AND deleted_at IS NULL);
SET @demo_tipo = (SELECT idTipo FROM tipo_salida WHERE LOWER(TRIM(nombre)) = 'venta' AND deleted_at IS NULL ORDER BY idTipo LIMIT 1);
INSERT INTO metodo_pago (nombre) SELECT 'Efectivo' WHERE NOT EXISTS (SELECT 1 FROM metodo_pago WHERE LOWER(TRIM(nombre)) = 'efectivo' AND deleted_at IS NULL);
SET @demo_metodo = (SELECT idMetodo FROM metodo_pago WHERE LOWER(TRIM(nombre)) = 'efectivo' AND deleted_at IS NULL ORDER BY idMetodo LIMIT 1);

-- Verificar antes de continuar: usuario/proveedor no nulos y 5 productos.
SELECT @demo_usuario AS usuario, @demo_proveedor_1 AS proveedor, (SELECT COUNT(*) FROM producto WHERE codigo IN ('PROD-001','PROD-002','PROD-003','PROD-004','PROD-005') AND deleted_at IS NULL) AS productos_esperados_5;

-- ============================================================
-- INGRESOS: 25 cabeceras y 75 detalles. Desde hace 65 a 17 dias.
-- ============================================================

-- Ingreso 01: hace 65 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 65 DAY), '08:30:00'), @demo_proveedor_1, @demo_usuario, 69025.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-001', 24, 1100.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 65 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 29, 825.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 65 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 34, 550.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 65 DAY), INTERVAL 45 DAY));

-- Ingreso 02: hace 63 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 63 DAY), '09:30:00'), @demo_proveedor_2, @demo_usuario, 80385.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-003', 31, 575.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 63 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 36, 690.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 63 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 41, 920.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 63 DAY), INTERVAL 20 DAY));

-- Ingreso 03: hace 61 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 61 DAY), '10:30:00'), @demo_proveedor_3, @demo_usuario, 131280.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-005', 38, 960.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 61 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 43, 1200.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 61 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 48, 900.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 61 DAY), INTERVAL 30 DAY));

-- Ingreso 04: hace 59 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 59 DAY), '11:30:00'), @demo_proveedor_4, @demo_usuario, 80335.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-002', 45, 938.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 59 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 25, 625.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 59 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 30, 750.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 59 DAY), INTERVAL 365 DAY));

-- Ingreso 05: hace 57 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 57 DAY), '08:30:00'), @demo_proveedor_1, @demo_usuario, 86680.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-004', 27, 660.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 57 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 32, 880.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 57 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 37, 1100.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 57 DAY), INTERVAL 180 DAY));

-- Ingreso 06: hace 55 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 55 DAY), '09:30:00'), @demo_proveedor_2, @demo_usuario, 98057.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-001', 34, 1150.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 55 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 39, 863.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 55 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 44, 575.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 55 DAY), INTERVAL 45 DAY));

-- Ingreso 07: hace 53 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 53 DAY), '10:30:00'), @demo_proveedor_3, @demo_usuario, 82680.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-003', 41, 600.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 53 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 46, 720.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 53 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 26, 960.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 53 DAY), INTERVAL 20 DAY));

-- Ingreso 08: hace 51 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 51 DAY), '11:30:00'), @demo_proveedor_4, @demo_usuario, 113954.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-005', 48, 1000.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 51 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 28, 1250.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 51 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 33, 938.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 51 DAY), INTERVAL 30 DAY));

-- Ingreso 09: hace 49 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 49 DAY), '08:30:00'), @demo_proveedor_1, @demo_usuario, 70400.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-002', 30, 825.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 49 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 35, 550.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 49 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 40, 660.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 49 DAY), INTERVAL 365 DAY));

-- Ingreso 10: hace 47 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 47 DAY), '09:30:00'), @demo_proveedor_2, @demo_usuario, 118220.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-004', 37, 690.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 47 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 42, 920.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 47 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 47, 1150.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 47 DAY), INTERVAL 180 DAY));

-- Ingreso 11: hace 45 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 45 DAY), '10:30:00'), @demo_proveedor_3, @demo_usuario, 91800.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-001', 44, 1200.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 45 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 24, 900.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 45 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 29, 600.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 45 DAY), INTERVAL 45 DAY));

-- Ingreso 12: hace 43 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 43 DAY), '11:30:00'), @demo_proveedor_4, @demo_usuario, 75500.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-003', 26, 625.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 43 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 31, 750.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 43 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 36, 1000.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 43 DAY), INTERVAL 20 DAY));

-- Ingreso 13: hace 41 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 41 DAY), '08:30:00'), @demo_proveedor_1, @demo_usuario, 106315.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-005', 33, 880.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 41 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 38, 1100.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 41 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 43, 825.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 41 DAY), INTERVAL 30 DAY));

-- Ingreso 14: hace 39 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 39 DAY), '09:30:00'), @demo_proveedor_2, @demo_usuario, 77645.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-002', 40, 863.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 39 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 45, 575.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 39 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 25, 690.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 39 DAY), INTERVAL 365 DAY));

-- Ingreso 15: hace 37 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 37 DAY), '10:30:00'), @demo_proveedor_3, @demo_usuario, 98160.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-004', 47, 720.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 37 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 27, 960.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 37 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 32, 1200.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 37 DAY), INTERVAL 180 DAY));

-- Ingreso 16: hace 35 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 35 DAY), '11:30:00'), @demo_proveedor_4, @demo_usuario, 92517.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-001', 29, 1250.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 35 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 34, 938.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 35 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 39, 625.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 35 DAY), INTERVAL 45 DAY));

-- Ingreso 17: hace 33 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 33 DAY), '08:30:00'), @demo_proveedor_1, @demo_usuario, 87340.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-003', 36, 550.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 33 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 41, 660.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 33 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 46, 880.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 33 DAY), INTERVAL 20 DAY));

-- Ingreso 18: hace 31 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 31 DAY), '09:30:00'), @demo_proveedor_2, @demo_usuario, 118924.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-005', 43, 920.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 31 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 48, 1150.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 31 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 28, 863.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 31 DAY), INTERVAL 30 DAY));

-- Ingreso 19: hace 29 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 29 DAY), '10:30:00'), @demo_proveedor_3, @demo_usuario, 65700.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-002', 25, 900.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 29 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 30, 600.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 29 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 35, 720.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 29 DAY), INTERVAL 365 DAY));

-- Ingreso 20: hace 27 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 27 DAY), '11:30:00'), @demo_proveedor_4, @demo_usuario, 113500.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-004', 32, 750.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 27 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 37, 1000.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 27 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 42, 1250.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 27 DAY), INTERVAL 180 DAY));

-- Ingreso 21: hace 25 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 25 DAY), '08:30:00'), @demo_proveedor_1, @demo_usuario, 92400.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-001', 39, 1100.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 25 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 44, 825.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 25 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 24, 550.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 25 DAY), INTERVAL 45 DAY));

-- Ingreso 22: hace 23 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 23 DAY), '09:30:00'), @demo_proveedor_2, @demo_usuario, 72910.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-003', 46, 575.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 23 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 26, 690.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 23 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 31, 920.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 23 DAY), INTERVAL 20 DAY));

-- Ingreso 23: hace 21 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 21 DAY), '10:30:00'), @demo_proveedor_3, @demo_usuario, 100680.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-005', 28, 960.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 21 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 33, 1200.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 21 DAY), INTERVAL 180 DAY)),
(@demo_ingreso, 'PROD-002', 38, 900.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 21 DAY), INTERVAL 30 DAY));

-- Ingreso 24: hace 19 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 19 DAY), '11:30:00'), @demo_proveedor_4, @demo_usuario, 91580.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-002', 35, 938.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 19 DAY), INTERVAL 30 DAY)),
(@demo_ingreso, 'PROD-003', 40, 625.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 19 DAY), INTERVAL 45 DAY)),
(@demo_ingreso, 'PROD-004', 45, 750.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 19 DAY), INTERVAL 365 DAY));

-- Ingreso 25: hace 17 dias.
INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 17 DAY), '08:30:00'), @demo_proveedor_1, @demo_usuario, 98780.00);
SET @demo_ingreso = LAST_INSERT_ID();
INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) VALUES
(@demo_ingreso, 'PROD-004', 42, 660.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 17 DAY), INTERVAL 365 DAY)),
(@demo_ingreso, 'PROD-005', 47, 880.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 17 DAY), INTERVAL 20 DAY)),
(@demo_ingreso, 'PROD-001', 27, 1100.00, DATE_ADD(DATE_SUB(@demo_fecha, INTERVAL 17 DAY), INTERVAL 180 DAY));

-- ============================================================
-- VENTAS: 75 tickets, 225 detalles y 75 pagos. Ultimos 60 dias.
-- Los tickets incluyen 3 productos diferentes y cantidades variables.
-- Se concentran mas operaciones entre las 17 y las 20 horas.
-- Algunos precios simulan descuentos historicos del 10% o 20%.
-- ============================================================

-- Venta 01: hace 60 dias, 10:00.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 60 DAY), '10:00:00'), @demo_tipo, @demo_usuario, NULL, 7040.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 1, 1600.00),
(@demo_salida, 'PROD-003', 2, 800.00),
(@demo_salida, 'PROD-005', 3, 1280.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 7040.00, DATE_SUB(@demo_fecha, INTERVAL 60 DAY));

-- Venta 02: hace 60 dias, 12:13.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 60 DAY), '12:13:00'), @demo_tipo, @demo_usuario, NULL, 11200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 4, 1500.00),
(@demo_salida, 'PROD-004', 1, 1200.00),
(@demo_salida, 'PROD-001', 2, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11200.00, DATE_SUB(@demo_fecha, INTERVAL 60 DAY));

-- Venta 03: hace 59 dias, 17:26.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 59 DAY), '17:26:00'), @demo_tipo, @demo_usuario, NULL, 10900.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 3, 1000.00),
(@demo_salida, 'PROD-005', 4, 1600.00),
(@demo_salida, 'PROD-002', 1, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10900.00, DATE_SUB(@demo_fecha, INTERVAL 59 DAY));

-- Venta 04: hace 58 dias, 18:39.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 58 DAY), '18:39:00'), @demo_tipo, @demo_usuario, NULL, 12400.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 2, 1200.00),
(@demo_salida, 'PROD-001', 3, 2000.00),
(@demo_salida, 'PROD-003', 4, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12400.00, DATE_SUB(@demo_fecha, INTERVAL 58 DAY));

-- Venta 05: hace 57 dias, 18:52.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 57 DAY), '18:52:00'), @demo_tipo, @demo_usuario, NULL, 8200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 1, 1600.00),
(@demo_salida, 'PROD-002', 2, 1500.00),
(@demo_salida, 'PROD-004', 3, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8200.00, DATE_SUB(@demo_fecha, INTERVAL 57 DAY));

-- Venta 06: hace 57 dias, 19:05.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 57 DAY), '19:05:00'), @demo_tipo, @demo_usuario, NULL, 12200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 4, 2000.00),
(@demo_salida, 'PROD-003', 1, 1000.00),
(@demo_salida, 'PROD-005', 2, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12200.00, DATE_SUB(@demo_fecha, INTERVAL 57 DAY));

-- Venta 07: hace 56 dias, 19:18.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 56 DAY), '19:18:00'), @demo_tipo, @demo_usuario, NULL, 11300.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 3, 1500.00),
(@demo_salida, 'PROD-004', 4, 1200.00),
(@demo_salida, 'PROD-001', 1, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11300.00, DATE_SUB(@demo_fecha, INTERVAL 56 DAY));

-- Venta 08: hace 55 dias, 20:31.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 55 DAY), '20:31:00'), @demo_tipo, @demo_usuario, NULL, 11520.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 2, 900.00),
(@demo_salida, 'PROD-005', 3, 1440.00),
(@demo_salida, 'PROD-002', 4, 1350.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11520.00, DATE_SUB(@demo_fecha, INTERVAL 55 DAY));

-- Venta 09: hace 54 dias, 15:44.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 54 DAY), '15:44:00'), @demo_tipo, @demo_usuario, NULL, 8200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 1, 1200.00),
(@demo_salida, 'PROD-001', 2, 2000.00),
(@demo_salida, 'PROD-003', 3, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8200.00, DATE_SUB(@demo_fecha, INTERVAL 54 DAY));

-- Venta 10: hace 53 dias, 18:57.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 53 DAY), '18:57:00'), @demo_tipo, @demo_usuario, NULL, 10300.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 4, 1600.00),
(@demo_salida, 'PROD-002', 1, 1500.00),
(@demo_salida, 'PROD-004', 2, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10300.00, DATE_SUB(@demo_fecha, INTERVAL 53 DAY));

-- Venta 11: hace 53 dias, 10:10.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 53 DAY), '10:10:00'), @demo_tipo, @demo_usuario, NULL, 11600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 3, 2000.00),
(@demo_salida, 'PROD-003', 4, 1000.00),
(@demo_salida, 'PROD-005', 1, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11600.00, DATE_SUB(@demo_fecha, INTERVAL 53 DAY));

-- Venta 12: hace 52 dias, 12:23.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 52 DAY), '12:23:00'), @demo_tipo, @demo_usuario, NULL, 11680.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 2, 1200.00),
(@demo_salida, 'PROD-004', 3, 960.00),
(@demo_salida, 'PROD-001', 4, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11680.00, DATE_SUB(@demo_fecha, INTERVAL 52 DAY));

-- Venta 13: hace 51 dias, 17:36.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 51 DAY), '17:36:00'), @demo_tipo, @demo_usuario, NULL, 8700.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 1, 1000.00),
(@demo_salida, 'PROD-005', 2, 1600.00),
(@demo_salida, 'PROD-002', 3, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8700.00, DATE_SUB(@demo_fecha, INTERVAL 51 DAY));

-- Venta 14: hace 50 dias, 18:49.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 50 DAY), '18:49:00'), @demo_tipo, @demo_usuario, NULL, 8800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 4, 1200.00),
(@demo_salida, 'PROD-001', 1, 2000.00),
(@demo_salida, 'PROD-003', 2, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8800.00, DATE_SUB(@demo_fecha, INTERVAL 50 DAY));

-- Venta 15: hace 49 dias, 18:02.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 49 DAY), '18:02:00'), @demo_tipo, @demo_usuario, NULL, 10800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 3, 1440.00),
(@demo_salida, 'PROD-002', 4, 1350.00),
(@demo_salida, 'PROD-004', 1, 1080.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10800.00, DATE_SUB(@demo_fecha, INTERVAL 49 DAY));

-- Venta 16: hace 49 dias, 19:15.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 49 DAY), '19:15:00'), @demo_tipo, @demo_usuario, NULL, 13400.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 2, 2000.00),
(@demo_salida, 'PROD-003', 3, 1000.00),
(@demo_salida, 'PROD-005', 4, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 13400.00, DATE_SUB(@demo_fecha, INTERVAL 49 DAY));

-- Venta 17: hace 48 dias, 19:28.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 48 DAY), '19:28:00'), @demo_tipo, @demo_usuario, NULL, 9900.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 1, 1500.00),
(@demo_salida, 'PROD-004', 2, 1200.00),
(@demo_salida, 'PROD-001', 3, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 9900.00, DATE_SUB(@demo_fecha, INTERVAL 48 DAY));

-- Venta 18: hace 47 dias, 20:41.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 47 DAY), '20:41:00'), @demo_tipo, @demo_usuario, NULL, 8600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 4, 1000.00),
(@demo_salida, 'PROD-005', 1, 1600.00),
(@demo_salida, 'PROD-002', 2, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8600.00, DATE_SUB(@demo_fecha, INTERVAL 47 DAY));

-- Venta 19: hace 46 dias, 15:54.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 46 DAY), '15:54:00'), @demo_tipo, @demo_usuario, NULL, 12600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 3, 1200.00),
(@demo_salida, 'PROD-001', 4, 2000.00),
(@demo_salida, 'PROD-003', 1, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12600.00, DATE_SUB(@demo_fecha, INTERVAL 46 DAY));

-- Venta 20: hace 45 dias, 18:07.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 45 DAY), '18:07:00'), @demo_tipo, @demo_usuario, NULL, 12500.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 2, 1600.00),
(@demo_salida, 'PROD-002', 3, 1500.00),
(@demo_salida, 'PROD-004', 4, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12500.00, DATE_SUB(@demo_fecha, INTERVAL 45 DAY));

-- Venta 21: hace 45 dias, 10:20.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 45 DAY), '10:20:00'), @demo_tipo, @demo_usuario, NULL, 8800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 1, 2000.00),
(@demo_salida, 'PROD-003', 2, 1000.00),
(@demo_salida, 'PROD-005', 3, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8800.00, DATE_SUB(@demo_fecha, INTERVAL 45 DAY));

-- Venta 22: hace 44 dias, 12:33.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 44 DAY), '12:33:00'), @demo_tipo, @demo_usuario, NULL, 10080.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 4, 1350.00),
(@demo_salida, 'PROD-004', 1, 1080.00),
(@demo_salida, 'PROD-001', 2, 1800.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10080.00, DATE_SUB(@demo_fecha, INTERVAL 44 DAY));

-- Venta 23: hace 43 dias, 17:46.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 43 DAY), '17:46:00'), @demo_tipo, @demo_usuario, NULL, 8720.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 3, 800.00),
(@demo_salida, 'PROD-005', 4, 1280.00),
(@demo_salida, 'PROD-002', 1, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8720.00, DATE_SUB(@demo_fecha, INTERVAL 43 DAY));

-- Venta 24: hace 42 dias, 18:59.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 42 DAY), '18:59:00'), @demo_tipo, @demo_usuario, NULL, 12400.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 2, 1200.00),
(@demo_salida, 'PROD-001', 3, 2000.00),
(@demo_salida, 'PROD-003', 4, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12400.00, DATE_SUB(@demo_fecha, INTERVAL 42 DAY));

-- Venta 25: hace 41 dias, 18:12.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 41 DAY), '18:12:00'), @demo_tipo, @demo_usuario, NULL, 8200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 1, 1600.00),
(@demo_salida, 'PROD-002', 2, 1500.00),
(@demo_salida, 'PROD-004', 3, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8200.00, DATE_SUB(@demo_fecha, INTERVAL 41 DAY));

-- Venta 26: hace 41 dias, 19:25.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 41 DAY), '19:25:00'), @demo_tipo, @demo_usuario, NULL, 12200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 4, 2000.00),
(@demo_salida, 'PROD-003', 1, 1000.00),
(@demo_salida, 'PROD-005', 2, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12200.00, DATE_SUB(@demo_fecha, INTERVAL 41 DAY));

-- Venta 27: hace 40 dias, 19:38.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 40 DAY), '19:38:00'), @demo_tipo, @demo_usuario, NULL, 11300.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 3, 1500.00),
(@demo_salida, 'PROD-004', 4, 1200.00),
(@demo_salida, 'PROD-001', 1, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11300.00, DATE_SUB(@demo_fecha, INTERVAL 40 DAY));

-- Venta 28: hace 39 dias, 20:51.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 39 DAY), '20:51:00'), @demo_tipo, @demo_usuario, NULL, 12800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 2, 1000.00),
(@demo_salida, 'PROD-005', 3, 1600.00),
(@demo_salida, 'PROD-002', 4, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12800.00, DATE_SUB(@demo_fecha, INTERVAL 39 DAY));

-- Venta 29: hace 38 dias, 15:04.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 38 DAY), '15:04:00'), @demo_tipo, @demo_usuario, NULL, 7380.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 1, 1080.00),
(@demo_salida, 'PROD-001', 2, 1800.00),
(@demo_salida, 'PROD-003', 3, 900.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 7380.00, DATE_SUB(@demo_fecha, INTERVAL 38 DAY));

-- Venta 30: hace 37 dias, 18:17.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 37 DAY), '18:17:00'), @demo_tipo, @demo_usuario, NULL, 10300.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 4, 1600.00),
(@demo_salida, 'PROD-002', 1, 1500.00),
(@demo_salida, 'PROD-004', 2, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10300.00, DATE_SUB(@demo_fecha, INTERVAL 37 DAY));

-- Venta 31: hace 37 dias, 10:30.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 37 DAY), '10:30:00'), @demo_tipo, @demo_usuario, NULL, 11600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 3, 2000.00),
(@demo_salida, 'PROD-003', 4, 1000.00),
(@demo_salida, 'PROD-005', 1, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11600.00, DATE_SUB(@demo_fecha, INTERVAL 37 DAY));

-- Venta 32: hace 36 dias, 12:43.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 36 DAY), '12:43:00'), @demo_tipo, @demo_usuario, NULL, 14600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 2, 1500.00),
(@demo_salida, 'PROD-004', 3, 1200.00),
(@demo_salida, 'PROD-001', 4, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 14600.00, DATE_SUB(@demo_fecha, INTERVAL 36 DAY));

-- Venta 33: hace 35 dias, 17:56.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 35 DAY), '17:56:00'), @demo_tipo, @demo_usuario, NULL, 8700.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 1, 1000.00),
(@demo_salida, 'PROD-005', 2, 1600.00),
(@demo_salida, 'PROD-002', 3, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8700.00, DATE_SUB(@demo_fecha, INTERVAL 35 DAY));

-- Venta 34: hace 34 dias, 18:09.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 34 DAY), '18:09:00'), @demo_tipo, @demo_usuario, NULL, 7040.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 4, 960.00),
(@demo_salida, 'PROD-001', 1, 1600.00),
(@demo_salida, 'PROD-003', 2, 800.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 7040.00, DATE_SUB(@demo_fecha, INTERVAL 34 DAY));

-- Venta 35: hace 33 dias, 18:22.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 33 DAY), '18:22:00'), @demo_tipo, @demo_usuario, NULL, 12000.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 3, 1600.00),
(@demo_salida, 'PROD-002', 4, 1500.00),
(@demo_salida, 'PROD-004', 1, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12000.00, DATE_SUB(@demo_fecha, INTERVAL 33 DAY));

-- Venta 36: hace 33 dias, 19:35.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 33 DAY), '19:35:00'), @demo_tipo, @demo_usuario, NULL, 12060.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 2, 1800.00),
(@demo_salida, 'PROD-003', 3, 900.00),
(@demo_salida, 'PROD-005', 4, 1440.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12060.00, DATE_SUB(@demo_fecha, INTERVAL 33 DAY));

-- Venta 37: hace 32 dias, 19:48.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 32 DAY), '19:48:00'), @demo_tipo, @demo_usuario, NULL, 9900.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 1, 1500.00),
(@demo_salida, 'PROD-004', 2, 1200.00),
(@demo_salida, 'PROD-001', 3, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 9900.00, DATE_SUB(@demo_fecha, INTERVAL 32 DAY));

-- Venta 38: hace 31 dias, 20:01.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 31 DAY), '20:01:00'), @demo_tipo, @demo_usuario, NULL, 8600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 4, 1000.00),
(@demo_salida, 'PROD-005', 1, 1600.00),
(@demo_salida, 'PROD-002', 2, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8600.00, DATE_SUB(@demo_fecha, INTERVAL 31 DAY));

-- Venta 39: hace 30 dias, 15:14.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 30 DAY), '15:14:00'), @demo_tipo, @demo_usuario, NULL, 12600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 3, 1200.00),
(@demo_salida, 'PROD-001', 4, 2000.00),
(@demo_salida, 'PROD-003', 1, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12600.00, DATE_SUB(@demo_fecha, INTERVAL 30 DAY));

-- Venta 40: hace 29 dias, 18:27.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 29 DAY), '18:27:00'), @demo_tipo, @demo_usuario, NULL, 12500.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 2, 1600.00),
(@demo_salida, 'PROD-002', 3, 1500.00),
(@demo_salida, 'PROD-004', 4, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12500.00, DATE_SUB(@demo_fecha, INTERVAL 29 DAY));

-- Venta 41: hace 29 dias, 10:40.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 29 DAY), '10:40:00'), @demo_tipo, @demo_usuario, NULL, 8800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 1, 2000.00),
(@demo_salida, 'PROD-003', 2, 1000.00),
(@demo_salida, 'PROD-005', 3, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8800.00, DATE_SUB(@demo_fecha, INTERVAL 29 DAY));

-- Venta 42: hace 28 dias, 12:53.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 28 DAY), '12:53:00'), @demo_tipo, @demo_usuario, NULL, 11200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 4, 1500.00),
(@demo_salida, 'PROD-004', 1, 1200.00),
(@demo_salida, 'PROD-001', 2, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11200.00, DATE_SUB(@demo_fecha, INTERVAL 28 DAY));

-- Venta 43: hace 27 dias, 17:06.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 27 DAY), '17:06:00'), @demo_tipo, @demo_usuario, NULL, 9810.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 3, 900.00),
(@demo_salida, 'PROD-005', 4, 1440.00),
(@demo_salida, 'PROD-002', 1, 1350.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 9810.00, DATE_SUB(@demo_fecha, INTERVAL 27 DAY));

-- Venta 44: hace 26 dias, 18:19.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 26 DAY), '18:19:00'), @demo_tipo, @demo_usuario, NULL, 12400.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 2, 1200.00),
(@demo_salida, 'PROD-001', 3, 2000.00),
(@demo_salida, 'PROD-003', 4, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12400.00, DATE_SUB(@demo_fecha, INTERVAL 26 DAY));

-- Venta 45: hace 25 dias, 18:32.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 25 DAY), '18:32:00'), @demo_tipo, @demo_usuario, NULL, 6560.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 1, 1280.00),
(@demo_salida, 'PROD-002', 2, 1200.00),
(@demo_salida, 'PROD-004', 3, 960.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 6560.00, DATE_SUB(@demo_fecha, INTERVAL 25 DAY));

-- Venta 46: hace 25 dias, 19:45.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 25 DAY), '19:45:00'), @demo_tipo, @demo_usuario, NULL, 12200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 4, 2000.00),
(@demo_salida, 'PROD-003', 1, 1000.00),
(@demo_salida, 'PROD-005', 2, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12200.00, DATE_SUB(@demo_fecha, INTERVAL 25 DAY));

-- Venta 47: hace 24 dias, 19:58.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 24 DAY), '19:58:00'), @demo_tipo, @demo_usuario, NULL, 11300.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 3, 1500.00),
(@demo_salida, 'PROD-004', 4, 1200.00),
(@demo_salida, 'PROD-001', 1, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11300.00, DATE_SUB(@demo_fecha, INTERVAL 24 DAY));

-- Venta 48: hace 23 dias, 20:11.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 23 DAY), '20:11:00'), @demo_tipo, @demo_usuario, NULL, 12800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 2, 1000.00),
(@demo_salida, 'PROD-005', 3, 1600.00),
(@demo_salida, 'PROD-002', 4, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12800.00, DATE_SUB(@demo_fecha, INTERVAL 23 DAY));

-- Venta 49: hace 22 dias, 15:24.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 22 DAY), '15:24:00'), @demo_tipo, @demo_usuario, NULL, 8200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 1, 1200.00),
(@demo_salida, 'PROD-001', 2, 2000.00),
(@demo_salida, 'PROD-003', 3, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8200.00, DATE_SUB(@demo_fecha, INTERVAL 22 DAY));

-- Venta 50: hace 21 dias, 18:37.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 21 DAY), '18:37:00'), @demo_tipo, @demo_usuario, NULL, 9270.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 4, 1440.00),
(@demo_salida, 'PROD-002', 1, 1350.00),
(@demo_salida, 'PROD-004', 2, 1080.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 9270.00, DATE_SUB(@demo_fecha, INTERVAL 21 DAY));

-- Venta 51: hace 21 dias, 10:50.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 21 DAY), '10:50:00'), @demo_tipo, @demo_usuario, NULL, 11600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 3, 2000.00),
(@demo_salida, 'PROD-003', 4, 1000.00),
(@demo_salida, 'PROD-005', 1, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11600.00, DATE_SUB(@demo_fecha, INTERVAL 21 DAY));

-- Venta 52: hace 20 dias, 12:03.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 20 DAY), '12:03:00'), @demo_tipo, @demo_usuario, NULL, 14600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 2, 1500.00),
(@demo_salida, 'PROD-004', 3, 1200.00),
(@demo_salida, 'PROD-001', 4, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 14600.00, DATE_SUB(@demo_fecha, INTERVAL 20 DAY));

-- Venta 53: hace 19 dias, 17:16.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 19 DAY), '17:16:00'), @demo_tipo, @demo_usuario, NULL, 8700.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 1, 1000.00),
(@demo_salida, 'PROD-005', 2, 1600.00),
(@demo_salida, 'PROD-002', 3, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8700.00, DATE_SUB(@demo_fecha, INTERVAL 19 DAY));

-- Venta 54: hace 18 dias, 18:29.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 18 DAY), '18:29:00'), @demo_tipo, @demo_usuario, NULL, 8800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 4, 1200.00),
(@demo_salida, 'PROD-001', 1, 2000.00),
(@demo_salida, 'PROD-003', 2, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8800.00, DATE_SUB(@demo_fecha, INTERVAL 18 DAY));

-- Venta 55: hace 17 dias, 18:42.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 17 DAY), '18:42:00'), @demo_tipo, @demo_usuario, NULL, 12000.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 3, 1600.00),
(@demo_salida, 'PROD-002', 4, 1500.00),
(@demo_salida, 'PROD-004', 1, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12000.00, DATE_SUB(@demo_fecha, INTERVAL 17 DAY));

-- Venta 56: hace 17 dias, 19:55.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 17 DAY), '19:55:00'), @demo_tipo, @demo_usuario, NULL, 10720.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 2, 1600.00),
(@demo_salida, 'PROD-003', 3, 800.00),
(@demo_salida, 'PROD-005', 4, 1280.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10720.00, DATE_SUB(@demo_fecha, INTERVAL 17 DAY));

-- Venta 57: hace 16 dias, 19:08.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 16 DAY), '19:08:00'), @demo_tipo, @demo_usuario, NULL, 8910.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 1, 1350.00),
(@demo_salida, 'PROD-004', 2, 1080.00),
(@demo_salida, 'PROD-001', 3, 1800.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8910.00, DATE_SUB(@demo_fecha, INTERVAL 16 DAY));

-- Venta 58: hace 15 dias, 20:21.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 15 DAY), '20:21:00'), @demo_tipo, @demo_usuario, NULL, 8600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 4, 1000.00),
(@demo_salida, 'PROD-005', 1, 1600.00),
(@demo_salida, 'PROD-002', 2, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8600.00, DATE_SUB(@demo_fecha, INTERVAL 15 DAY));

-- Venta 59: hace 14 dias, 15:34.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 14 DAY), '15:34:00'), @demo_tipo, @demo_usuario, NULL, 12600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 3, 1200.00),
(@demo_salida, 'PROD-001', 4, 2000.00),
(@demo_salida, 'PROD-003', 1, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12600.00, DATE_SUB(@demo_fecha, INTERVAL 14 DAY));

-- Venta 60: hace 13 dias, 18:47.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 13 DAY), '18:47:00'), @demo_tipo, @demo_usuario, NULL, 12500.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 2, 1600.00),
(@demo_salida, 'PROD-002', 3, 1500.00),
(@demo_salida, 'PROD-004', 4, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12500.00, DATE_SUB(@demo_fecha, INTERVAL 13 DAY));

-- Venta 61: hace 13 dias, 10:00.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 13 DAY), '10:00:00'), @demo_tipo, @demo_usuario, NULL, 8800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 1, 2000.00),
(@demo_salida, 'PROD-003', 2, 1000.00),
(@demo_salida, 'PROD-005', 3, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8800.00, DATE_SUB(@demo_fecha, INTERVAL 13 DAY));

-- Venta 62: hace 12 dias, 12:13.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 12 DAY), '12:13:00'), @demo_tipo, @demo_usuario, NULL, 11200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 4, 1500.00),
(@demo_salida, 'PROD-004', 1, 1200.00),
(@demo_salida, 'PROD-001', 2, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11200.00, DATE_SUB(@demo_fecha, INTERVAL 12 DAY));

-- Venta 63: hace 11 dias, 17:26.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 11 DAY), '17:26:00'), @demo_tipo, @demo_usuario, NULL, 10900.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 3, 1000.00),
(@demo_salida, 'PROD-005', 4, 1600.00),
(@demo_salida, 'PROD-002', 1, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10900.00, DATE_SUB(@demo_fecha, INTERVAL 11 DAY));

-- Venta 64: hace 10 dias, 18:39.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 10 DAY), '18:39:00'), @demo_tipo, @demo_usuario, NULL, 11160.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 2, 1080.00),
(@demo_salida, 'PROD-001', 3, 1800.00),
(@demo_salida, 'PROD-003', 4, 900.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 11160.00, DATE_SUB(@demo_fecha, INTERVAL 10 DAY));

-- Venta 65: hace 9 dias, 18:52.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 9 DAY), '18:52:00'), @demo_tipo, @demo_usuario, NULL, 8200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 1, 1600.00),
(@demo_salida, 'PROD-002', 2, 1500.00),
(@demo_salida, 'PROD-004', 3, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8200.00, DATE_SUB(@demo_fecha, INTERVAL 9 DAY));

-- Venta 66: hace 9 dias, 19:05.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 9 DAY), '19:05:00'), @demo_tipo, @demo_usuario, NULL, 12200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 4, 2000.00),
(@demo_salida, 'PROD-003', 1, 1000.00),
(@demo_salida, 'PROD-005', 2, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12200.00, DATE_SUB(@demo_fecha, INTERVAL 9 DAY));

-- Venta 67: hace 8 dias, 19:18.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 8 DAY), '19:18:00'), @demo_tipo, @demo_usuario, NULL, 9040.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 3, 1200.00),
(@demo_salida, 'PROD-004', 4, 960.00),
(@demo_salida, 'PROD-001', 1, 1600.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 9040.00, DATE_SUB(@demo_fecha, INTERVAL 8 DAY));

-- Venta 68: hace 7 dias, 20:31.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 7 DAY), '20:31:00'), @demo_tipo, @demo_usuario, NULL, 12800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 2, 1000.00),
(@demo_salida, 'PROD-005', 3, 1600.00),
(@demo_salida, 'PROD-002', 4, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12800.00, DATE_SUB(@demo_fecha, INTERVAL 7 DAY));

-- Venta 69: hace 6 dias, 15:44.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 6 DAY), '15:44:00'), @demo_tipo, @demo_usuario, NULL, 8200.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 1, 1200.00),
(@demo_salida, 'PROD-001', 2, 2000.00),
(@demo_salida, 'PROD-003', 3, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8200.00, DATE_SUB(@demo_fecha, INTERVAL 6 DAY));

-- Venta 70: hace 5 dias, 18:57.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 5 DAY), '18:57:00'), @demo_tipo, @demo_usuario, NULL, 10300.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 4, 1600.00),
(@demo_salida, 'PROD-002', 1, 1500.00),
(@demo_salida, 'PROD-004', 2, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10300.00, DATE_SUB(@demo_fecha, INTERVAL 5 DAY));

-- Venta 71: hace 5 dias, 10:10.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 5 DAY), '10:10:00'), @demo_tipo, @demo_usuario, NULL, 10440.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-001', 3, 1800.00),
(@demo_salida, 'PROD-003', 4, 900.00),
(@demo_salida, 'PROD-005', 1, 1440.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 10440.00, DATE_SUB(@demo_fecha, INTERVAL 5 DAY));

-- Venta 72: hace 4 dias, 12:23.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 4 DAY), '12:23:00'), @demo_tipo, @demo_usuario, NULL, 14600.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-002', 2, 1500.00),
(@demo_salida, 'PROD-004', 3, 1200.00),
(@demo_salida, 'PROD-001', 4, 2000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 14600.00, DATE_SUB(@demo_fecha, INTERVAL 4 DAY));

-- Venta 73: hace 3 dias, 17:36.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 3 DAY), '17:36:00'), @demo_tipo, @demo_usuario, NULL, 8700.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-003', 1, 1000.00),
(@demo_salida, 'PROD-005', 2, 1600.00),
(@demo_salida, 'PROD-002', 3, 1500.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8700.00, DATE_SUB(@demo_fecha, INTERVAL 3 DAY));

-- Venta 74: hace 2 dias, 18:49.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 2 DAY), '18:49:00'), @demo_tipo, @demo_usuario, NULL, 8800.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-004', 4, 1200.00),
(@demo_salida, 'PROD-001', 1, 2000.00),
(@demo_salida, 'PROD-003', 2, 1000.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 8800.00, DATE_SUB(@demo_fecha, INTERVAL 2 DAY));

-- Venta 75: hace 1 dias, 18:02.
INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
VALUES (TIMESTAMP(DATE_SUB(@demo_fecha, INTERVAL 1 DAY), '18:02:00'), @demo_tipo, @demo_usuario, NULL, 12000.00);
SET @demo_salida = LAST_INSERT_ID();
INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
(@demo_salida, 'PROD-005', 3, 1600.00),
(@demo_salida, 'PROD-002', 4, 1500.00),
(@demo_salida, 'PROD-004', 1, 1200.00);
INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago)
VALUES (@demo_salida, @demo_metodo, 12000.00, DATE_SUB(@demo_fecha, INTERVAL 1 DAY));

COMMIT;

-- Resumen esperado de lo agregado por UNA ejecucion completa:
-- Compras: $2314767 CLP. Ventas/pagos: $792530 CLP.
-- Para ver todas las ventas nuevas en reportes, seleccionar desde hace 60 dias hasta hoy.
-- El stock previo se conserva; los movimientos de este archivo se suman al existente.
-- Variacion neta de stock por producto:
-- PROD-001 (Coca Cola 2L): +545 ingresos -111 ventas = +434 unidades.
-- PROD-002 (Pan de Molde): +535 ingresos -114 ventas = +421 unidades.
-- PROD-003 (Leche Entera 1L): +525 ingresos -112 ventas = +413 unidades.
-- PROD-004 (Arroz 1kg): +540 ingresos -111 ventas = +429 unidades.
-- PROD-005 (Tomate 1kg): +555 ingresos -113 ventas = +442 unidades.

SELECT DATE_SUB(@demo_fecha, INTERVAL 60 DAY) AS fecha_desde_reportes, @demo_fecha AS fecha_hasta_reportes, 25 AS ingresos_agregados, 75 AS ventas_agregadas, 75 AS detalles_ingreso, 225 AS detalles_venta, 75 AS pagos;
