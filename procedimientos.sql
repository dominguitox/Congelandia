CREATE DATABASE IF NOT EXISTS congelandia_db;
USE congelandia_db;
DELIMITER //
DROP PROCEDURE SP_RegistrarSalida_Basica;
DROP PROCEDURE SP_CrearProducto;
DROP PROCEDURE SP_ListarProductos;
DROP PROCEDURE SP_ListarProveedores;
sELECT * FROM Lista_Precio 


DELIMITER //
CREATE PROCEDURE SP_CrearProducto(
    -- 1. Datos del Catálogo (Tabla Producto y Categoria)
    IN p_codigo VARCHAR(50),
    IN p_nombre VARCHAR(150),
    IN p_descripcion TEXT,
    IN p_idCategoria INT,
    -- 2. Dato de Venta (Tabla Lista_Precio)
    IN p_precioVenta INT,
    -- 3. Datos de Abastecimiento Inicial (Tablas Ingreso y Detalle_Ingreso)
    IN p_idProveedor INT,
    IN p_idUsuario INT, -- El ID del trabajador/jefe que está creando el producto
    IN p_stockInicial INT,
    IN p_precioCompra DECIMAL(10,2),
    IN p_fechaVencimiento DATE
)
BEGIN
    -- Variable para guardar el ID del ingreso generado
    DECLARE v_idIngreso INT;
    -- Manejador de errores: Si algo falla, deshace todo (Rollback) para no dejar datos huérfanos
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    -- Iniciar la transacción
    START TRANSACTION;
    -- PASO 1: Registrar el producto base en el catálogo
    INSERT INTO Producto (codigo, nombre, descripcion, idCategoria)
    VALUES (p_codigo, p_nombre, p_descripcion, p_idCategoria);
    -- PASO 2: Registrar el precio de venta actual vigente
    INSERT INTO Lista_Precio (codigoProducto, precioVenta, fechaInicio, fechaFin)
    VALUES (p_codigo, p_precioVenta, NOW(), NULL);
    -- PASO 3: Si se ingresó un stock inicial mayor a 0, registrar automáticamente la compra (Ingreso)
    IF p_stockInicial > 0 THEN
        -- Crear la cabecera del ingreso
        INSERT INTO Ingreso (fecha, idProveedor, idUsuario, totalCompra)
        VALUES (NOW(), p_idProveedor, p_idUsuario, (p_precioCompra * p_stockInicial));
        -- Capturar el ID del ingreso recién creado
        SET v_idIngreso = LAST_INSERT_ID();
        -- Crear el detalle del ingreso con el costo y vencimiento de este lote
        INSERT INTO Detalle_Ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento)
        VALUES (v_idIngreso, p_codigo, p_stockInicial, p_precioCompra, p_fechaVencimiento);
    END IF;
    -- Confirmar todos los cambios
    COMMIT;
END //
DELIMITER //

CREATE PROCEDURE SP_ListarProductos()
BEGIN
    SELECT 
        p.codigo, 
        p.nombre, 
        c.nombre AS categoria,
        -- 1. Obtener el precio de venta actual (el que no tiene fecha de fin)
        (SELECT precioVenta FROM Lista_Precio WHERE codigoProducto = p.codigo AND fechaFin IS NULL LIMIT 1) AS precio,
        -- 2. Obtener el último costo de compra desde el historial de ingresos
        (SELECT di.precioCompra FROM Detalle_Ingreso di 
			JOIN Ingreso i ON di.idIngreso = i.idIngreso 
         WHERE di.codigoProducto = p.codigo 
         ORDER BY i.fecha DESC LIMIT 1) AS costo,
        -- 3. Obtener el proveedor al que se le hizo la última compra de este producto
        (SELECT prov.nombre FROM Detalle_Ingreso di 
			JOIN Ingreso i ON di.idIngreso = i.idIngreso 
			JOIN Proveedor prov ON i.idProveedor = prov.idProveedor
         WHERE di.codigoProducto = p.codigo 
         ORDER BY i.fecha DESC LIMIT 1) AS proveedor,
        -- 4. Calcular el stock real (Suma de todo lo que entró - Suma de todo lo que salió)
        (IFNULL((SELECT SUM(cantidad) FROM Detalle_Ingreso WHERE codigoProducto = p.codigo), 0) 
        - IFNULL((SELECT SUM(cantidad) FROM Detalle_Salida WHERE codigoProducto = p.codigo), 0)) AS stock
    FROM Producto p
    LEFT JOIN Categoria c ON p.idCategoria = c.idCategoria;
END //
DELIMITER //
CREATE PROCEDURE SP_ObtenerProductoPorId(
    IN p_codigoBuscado VARCHAR(50)
)
BEGIN
    SELECT 
        p.codigo, 
        p.nombre, 
        c.nombre AS categoria,
        -- 1. Obtener el precio de venta actual (el que no tiene fecha de fin)
        (SELECT precioVenta FROM Lista_Precio WHERE codigoProducto = p.codigo AND fechaFin IS NULL LIMIT 1) AS precio,
        -- 2. Obtener el último costo de compra desde el historial de ingresos
        (SELECT di.precioCompra FROM Detalle_Ingreso di 
         JOIN Ingreso i ON di.idIngreso = i.idIngreso 
         WHERE di.codigoProducto = p.codigo 
         ORDER BY i.fecha DESC LIMIT 1) AS costo,
        -- 3. Obtener el proveedor al que se le hizo la última compra de este producto
        (SELECT prov.nombre FROM Detalle_Ingreso di 
         JOIN Ingreso i ON di.idIngreso = i.idIngreso 
         JOIN Proveedor prov ON i.idProveedor = prov.idProveedor
         WHERE di.codigoProducto = p.codigo 
         ORDER BY i.fecha DESC LIMIT 1) AS proveedor,
        -- 4. Calcular el stock real (Suma de todo lo que entró - Suma de todo lo que salió)
        (IFNULL((SELECT SUM(cantidad) FROM Detalle_Ingreso WHERE codigoProducto = p.codigo), 0) 
        - IFNULL((SELECT SUM(cantidad) FROM Detalle_Salida WHERE codigoProducto = p.codigo), 0)
        ) AS stock
    FROM Producto p
    LEFT JOIN Categoria c ON p.idCategoria = c.idCategoria
    -- Filtramos específicamente por el código ingresado
    WHERE p.codigo = p_codigoBuscado; 
END //
DELIMITER //


SELECT * FROM PROVEEDOR;
DELIMITER //
CREATE PROCEDURE SP_listarProveedores()
BEGIN
select * from proveedor;
END //

DELIMITER //
CREATE PROCEDURE SP_listarCategorias()
BEGIN
select * from categoria;
END //
DELIMITER //

DELIMITER //
CREATE PROCEDURE SP_listarHistorial()
select * from salida;

DELIMITER //
DROP PROCEDURE SP_listarSalidas;
CREATE PROCEDURE SP_listarSalidas()
BEGIN
SELECT 
	s.idSalida,
    s.fecha,
	(select t.nombre from tipo_salida t where idTIpo = s.idTipo) as tipo,
    (select u.nombre from usuario u where idUsuario = s.idUsuario) as usuario,
    s.rutCliente,
    s.totalSalida
    FROM SALIDA s;
END //
call SP_ListarSalidas;
DELIMITER //

DELIMITER //
select * from salida;
select * from detalle_salida;
select * from tipo_salida;

DELIMITER ;

SELECT * FROM categoria;

call SP_ObtenerProductoPorId('PROD-001');
call SP_ObtenerProductoPorId('2323');
call SP_listarSalidas;


SELECT * FROM PRODUCTO;

SELECT * FROM USUARIOS;


DELIMITER ;
DELIMITER ;
