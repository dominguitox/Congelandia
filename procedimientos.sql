CREATE DATABASE IF NOT EXISTS congelandia_db;
USE congelandia_db;

DROP PROCEDURE IF EXISTS SP_RegistrarSalida;
DROP PROCEDURE IF EXISTS SP_RegistrarVenta;
DROP PROCEDURE IF EXISTS SP_DetalleSalida;
DROP PROCEDURE IF EXISTS SP_RegistrarDetalleVenta;
DROP PROCEDURE IF EXISTS SP_DetalleVenta;
DROP PROCEDURE IF EXISTS SP_CrearProducto;
DROP PROCEDURE IF EXISTS SP_ListarProductos;
DROP PROCEDURE IF EXISTS SP_ListarCategorias;
DROP PROCEDURE IF EXISTS SP_ObtenerProductoPorId;
DROP PROCEDURE IF EXISTS SP_ListarProveedores;
DROP PROCEDURE IF EXISTS SP_listarSalidas;
DROP PROCEDURE IF EXISTS SP_listarHistorial;
DROP PROCEDURE IF EXISTS SP_DetalleSalida;

DELIMITER //
DELIMITER //

DELIMITER //

CREATE PROCEDURE SP_RegistrarVenta(
	IN p_idTipo INT,
    IN p_idUsuario INT,
    IN p_rutCliente VARCHAR(20),
    IN p_totalVenta DECIMAL(10,2),
    OUT p_idVenta INT,       -- Parámetro de salida para usarlo en el detalle
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
        SET p_resultado = 0;
        SET p_mensaje = 'Error al registrar la venta.';

    -- Inserción en la tabla Venta
	INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida)
	VALUES (NOW(), p_idTipo, p_idUsuario, p_rutCliente, p_totalVenta);
    
    -- Capturar el ID generado para la venta actual
    SET p_idVenta = LAST_INSERT_ID();
    SET p_resultado = 1;
    SET p_mensaje = 'Venta registrada exitosamente.';
END //
DELIMITER //

CREATE PROCEDURE SP_RegistrarDetalleVenta(
    IN p_idVenta INT,
    IN p_codigoProducto VARCHAR(50),
    IN p_cantidad INT,
    IN p_precioCobrado DECIMAL(10,2),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_stock_actual INT;
        SET p_resultado = 0;
        SET p_mensaje = 'Error al registrar el detalle o actualizar el stock.';

    -- Validar que el stock sea suficiente antes de confirmar, tal como lo exige el sistema de Congelandia
    IF v_stock_actual >= p_cantidad THEN
        -- 1. Insertar en la tabla DetalleVenta
        INSERT INTO DetalleVenta (idVenta, producto_codigo, cantidad, precioCobrado)
        VALUES (p_idVenta, p_codigoProducto, p_cantidad, p_precioCobrado);
        -- 2. Descontar el stock automáticamente en la tabla Producto
		INSERT INTO Lista_Precio (codigoProducto, precioVenta, fechaInicio, fechaFin)
		VALUES (p_codigo, p_precioVenta, NOW(), NULL);
        SET p_resultado = 1;
        SET p_mensaje = 'Detalle registrado y stock actualizado con éxito.';
    ELSE
        SET p_resultado = 0;
        SET p_mensaje = 'Stock insuficiente para confirmar la cantidad solicitada.';
    END IF;
END //

CREATE PROCEDURE SP_CrearProducto(
    IN p_codigo VARCHAR(50),
    IN p_nombre VARCHAR(150),
    IN p_descripcion TEXT,
    IN p_idCategoria INT,
    IN p_precioVenta INT,
    IN p_idProveedor INT,
    IN p_idUsuario INT,
    IN p_stockInicial INT,
    IN p_precioCompra DECIMAL(10,2),
    IN p_fechaVencimiento DATE
)
BEGIN
    DECLARE v_idIngreso INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    START TRANSACTION;
    
    INSERT INTO Producto (codigo, nombre, descripcion, idCategoria)
    VALUES (p_codigo, p_nombre, p_descripcion, p_idCategoria);
    
    INSERT INTO Lista_Precio (codigoProducto, precioVenta, fechaInicio, fechaFin)
    VALUES (p_codigo, p_precioVenta, NOW(), NULL);
    
    IF p_stockInicial > 0 THEN
        INSERT INTO Ingreso (fecha, idProveedor, idUsuario, totalCompra)
        VALUES (NOW(), p_idProveedor, p_idUsuario, (p_precioCompra * p_stockInicial));
        
        SET v_idIngreso = LAST_INSERT_ID();
        
        INSERT INTO Detalle_Ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento)
        VALUES (v_idIngreso, p_codigo, p_stockInicial, p_precioCompra, p_fechaVencimiento);
    END IF;
    COMMIT;
END //

CREATE PROCEDURE SP_ListarProductos()
BEGIN
    SELECT 
        p.codigo, 
        p.nombre, 
        c.nombre AS categoria,
        (SELECT precioVenta FROM Lista_Precio WHERE codigoProducto = p.codigo AND fechaFin IS NULL LIMIT 1) AS precio,
        (SELECT di.precioCompra FROM Detalle_Ingreso di 
            JOIN Ingreso i ON di.idIngreso = i.idIngreso 
         WHERE di.codigoProducto = p.codigo 
         ORDER BY i.fecha DESC LIMIT 1) AS costo,
        (SELECT prov.nombre FROM Detalle_Ingreso di 
            JOIN Ingreso i ON di.idIngreso = i.idIngreso 
            JOIN Proveedor prov ON i.idProveedor = prov.idProveedor
         WHERE di.codigoProducto = p.codigo 
         ORDER BY i.fecha DESC LIMIT 1) AS proveedor,
        (IFNULL((SELECT SUM(cantidad) FROM Detalle_Ingreso WHERE codigoProducto = p.codigo), 0) 
        - IFNULL((SELECT SUM(cantidad) FROM Detalle_Salida WHERE codigoProducto = p.codigo), 0)) AS stock
    FROM Producto p
    LEFT JOIN Categoria c ON p.idCategoria = c.idCategoria;
END //

CREATE PROCEDURE SP_ObtenerProductoPorId(
    IN p_codigoBuscado VARCHAR(50)
)
BEGIN
    SELECT 
        p.codigo, 
        p.nombre, 
        c.nombre AS categoria,
        (SELECT precioVenta FROM Lista_Precio WHERE codigoProducto = p.codigo AND fechaFin IS NULL LIMIT 1) AS precio,
        (SELECT di.precioCompra FROM Detalle_Ingreso di 
         JOIN Ingreso i ON di.idIngreso = i.idIngreso 
         WHERE di.codigoProducto = p.codigo 
         ORDER BY i.fecha DESC LIMIT 1) AS costo,
        (SELECT prov.nombre FROM Detalle_Ingreso di 
         JOIN Ingreso i ON di.idIngreso = i.idIngreso 
         JOIN Proveedor prov ON i.idProveedor = prov.idProveedor
         WHERE di.codigoProducto = p.codigo 
         ORDER BY i.fecha DESC LIMIT 1) AS proveedor,
        (IFNULL((SELECT SUM(cantidad) FROM Detalle_Ingreso WHERE codigoProducto = p.codigo), 0) 
        - IFNULL((SELECT SUM(cantidad) FROM Detalle_Salida WHERE codigoProducto = p.codigo), 0)
        ) AS stock
    FROM Producto p
    LEFT JOIN Categoria c ON p.idCategoria = c.idCategoria
    WHERE p.codigo = p_codigoBuscado; 
END //

CREATE PROCEDURE SP_listarProveedores()
BEGIN
    SELECT * FROM proveedor;
END //

CREATE PROCEDURE SP_listarCategorias()
BEGIN
    SELECT * FROM categoria;
END //

CREATE PROCEDURE SP_listarHistorial()
BEGIN
    SELECT * FROM salida;
END //

CREATE PROCEDURE SP_listarSalidas()
BEGIN
    SELECT 
        s.idSalida,
        s.fecha,
        (SELECT t.nombre FROM tipo_salida t WHERE idTIpo = s.idTipo) AS tipo,
        (SELECT u.nombre FROM usuario u WHERE idUsuario = s.idUsuario) AS usuario,
        s.rutCliente,
        s.totalSalida,
       (SELECT sum(CANTIDAD) FROM DETALLE_SALIDA WHERE idSalida = s.idSalida) as cantidad
    FROM SALIDA s;
END //

CREATE PROCEDURE SP_DetalleSalida(
    IN p_idSalida INT
)
BEGIN
    SELECT
        p.nombre AS producto,
        ds.cantidad,
        ds.precioCobrado AS subtotal,
        (ds.precioCobrado / ds.cantidad) AS precioUnitario
    FROM Detalle_Salida ds
    INNER JOIN Producto p
        ON ds.codigoProducto = p.codigo
    WHERE ds.idSalida = p_idSalida;
END //



DELIMITER ;