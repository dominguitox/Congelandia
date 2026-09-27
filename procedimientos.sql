CREATE DATABASE IF NOT EXISTS congelandia_db;
USE congelandia_db;


DROP PROCEDURE IF EXISTS SP_RegistrarSalida_Basica;
DROP PROCEDURE IF EXISTS SP_CrearProducto;
DROP PROCEDURE IF EXISTS SP_ListarProductos;
DROP PROCEDURE IF EXISTS SP_ListarCategorias;
DROP PROCEDURE IF EXISTS SP_ObtenerProductoPorId;
DROP PROCEDURE IF EXISTS SP_ListarProveedores;
DROP PROCEDURE IF EXISTS SP_listarSalidas;
DROP PROCEDURE IF EXISTS SP_listarHistorial;
DROP PROCEDURE IF EXISTS SP_DetalleSalida;

DELIMITER //

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
        s.totalSalida
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