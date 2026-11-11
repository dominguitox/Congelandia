  -- Datos de demostracion Congelandia: catalogo y ventas del 1 al 30 de noviembre de 2026.
  -- Inserta 10 proveedores, 20 categorias, 100 productos y 10-30 ventas por cada dia (510 en total).
  -- Ejecutar en la base de pruebas, en una sola conexion. Requiere al menos un usuario activo.
  -- No borra datos existentes. Codigos DEMO-NOV26-xxx hacen que categorias/productos/precios se puedan volver a ejecutar.
  -- Cada ejecucion agrega otro lote de ventas; las ventas no son idempotentes.

  START TRANSACTION;

  -- 10 proveedores
  INSERT INTO proveedor (nombre, telefono, correo)
  VALUES
  ('Distribuidora Frio Austral', '+56941000001', 'ventas@frioaustral.cl'),
  ('Alimentos Congelados del Sur', '+56941000002', 'contacto@congeladosur.cl'),
  ('Comercial Patagonia', '+56941000003', 'pedidos@patagoniacomer.cl'),
  ('Pesquera Bahia Azul', '+56941000004', 'ventas@bahiaazul.cl'),
  ('Avicola San Miguel', '+56941000005', 'contacto@avicola-sm.cl'),
  ('Frutas y Verduras Andinas', '+56941000006', 'ventas@andinas.cl'),
  ('Lacteos Cordillera', '+56941000007', 'pedidos@lacteoscordillera.cl'),
  ('Importadora Polar', '+56941000008', 'contacto@polarimport.cl'),
  ('Distribuidora La Estrella', '+56941000009', 'ventas@laestrella.cl'),
  ('Comercial Central de Alimentos', '+56941000010', 'contacto@centralalimentos.cl');

  -- 20 categorias (se omiten las que ya existan por nombre)
  INSERT INTO categoria (nombre)
  SELECT v.nombre FROM (
    SELECT 'Congelados' AS nombre UNION ALL
    SELECT 'Pescados' AS nombre UNION ALL
    SELECT 'Mariscos' AS nombre UNION ALL
    SELECT 'Carnes' AS nombre UNION ALL
    SELECT 'Pollo' AS nombre UNION ALL
    SELECT 'Verduras congeladas' AS nombre UNION ALL
    SELECT 'Frutas congeladas' AS nombre UNION ALL
    SELECT 'Helados' AS nombre UNION ALL
    SELECT 'Papas y snacks' AS nombre UNION ALL
    SELECT 'Masas' AS nombre UNION ALL
    SELECT 'Empanadas' AS nombre UNION ALL
    SELECT 'Pizzas' AS nombre UNION ALL
    SELECT 'Panaderia congelada' AS nombre UNION ALL
    SELECT 'Postres' AS nombre UNION ALL
    SELECT 'Lacteos' AS nombre UNION ALL
    SELECT 'Bebidas' AS nombre UNION ALL
    SELECT 'Abarrotes' AS nombre UNION ALL
    SELECT 'Salsas' AS nombre UNION ALL
    SELECT 'Reposteria' AS nombre UNION ALL
    SELECT 'Otros productos' AS nombre
  ) AS v
  WHERE NOT EXISTS (SELECT 1 FROM categoria c WHERE c.nombre=v.nombre AND c.deleted_at IS NULL);

  -- 100 productos, repartidos entre las 20 categorias
  INSERT INTO producto (codigo, nombre, descripcion, idCategoria)
  SELECT v.codigo, v.nombre, v.descripcion, c.idCategoria
  FROM (
    SELECT 'DEMO-NOV26-001' AS codigo, 'Arvejas congeladas' AS nombre, 'Arvejas congeladas, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Congelados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-002' AS codigo, 'Choclo congelado' AS nombre, 'Choclo congelado, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Congelados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-003' AS codigo, 'Mix primavera' AS nombre, 'Mix primavera, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Congelados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-004' AS codigo, 'Porotos verdes' AS nombre, 'Porotos verdes, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Congelados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-005' AS codigo, 'Espinaca congelada' AS nombre, 'Espinaca congelada, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Congelados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-006' AS codigo, 'Merluza austral' AS nombre, 'Merluza austral, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Pescados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-007' AS codigo, 'Reineta' AS nombre, 'Reineta, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Pescados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-008' AS codigo, 'Salmon porcionado' AS nombre, 'Salmon porcionado, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Pescados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-009' AS codigo, 'Congrio' AS nombre, 'Congrio, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Pescados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-010' AS codigo, 'Filete de tilapia' AS nombre, 'Filete de tilapia, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Pescados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-011' AS codigo, 'Camaron' AS nombre, 'Camaron, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Mariscos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-012' AS codigo, 'Choritos' AS nombre, 'Choritos, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Mariscos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-013' AS codigo, 'Calamares' AS nombre, 'Calamares, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Mariscos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-014' AS codigo, 'Mix de mariscos' AS nombre, 'Mix de mariscos, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Mariscos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-015' AS codigo, 'Ostiones' AS nombre, 'Ostiones, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Mariscos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-016' AS codigo, 'Vacuno molido' AS nombre, 'Vacuno molido, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Carnes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-017' AS codigo, 'Asado carnicero' AS nombre, 'Asado carnicero, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Carnes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-018' AS codigo, 'Posta rosada' AS nombre, 'Posta rosada, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Carnes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-019' AS codigo, 'Costillar de cerdo' AS nombre, 'Costillar de cerdo, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Carnes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-020' AS codigo, 'Plateada' AS nombre, 'Plateada, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Carnes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-021' AS codigo, 'Pechuga de pollo' AS nombre, 'Pechuga de pollo, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Pollo' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-022' AS codigo, 'Trutro corto' AS nombre, 'Trutro corto, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Pollo' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-023' AS codigo, 'Alitas de pollo' AS nombre, 'Alitas de pollo, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Pollo' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-024' AS codigo, 'Pollo deshuesado' AS nombre, 'Pollo deshuesado, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Pollo' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-025' AS codigo, 'Nuggets de pollo' AS nombre, 'Nuggets de pollo, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Pollo' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-026' AS codigo, 'Brocoli' AS nombre, 'Brocoli, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Verduras congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-027' AS codigo, 'Coliflor' AS nombre, 'Coliflor, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Verduras congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-028' AS codigo, 'Zanahoria en cubos' AS nombre, 'Zanahoria en cubos, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Verduras congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-029' AS codigo, 'Pimenton en tiras' AS nombre, 'Pimenton en tiras, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Verduras congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-030' AS codigo, 'Poroto granado' AS nombre, 'Poroto granado, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Verduras congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-031' AS codigo, 'Frutilla' AS nombre, 'Frutilla, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Frutas congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-032' AS codigo, 'Arandano' AS nombre, 'Arandano, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Frutas congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-033' AS codigo, 'Frambuesa' AS nombre, 'Frambuesa, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Frutas congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-034' AS codigo, 'Mango en cubos' AS nombre, 'Mango en cubos, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Frutas congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-035' AS codigo, 'Mix de berries' AS nombre, 'Mix de berries, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Frutas congeladas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-036' AS codigo, 'Helado de vainilla' AS nombre, 'Helado de vainilla, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Helados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-037' AS codigo, 'Helado de chocolate' AS nombre, 'Helado de chocolate, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Helados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-038' AS codigo, 'Helado de frutilla' AS nombre, 'Helado de frutilla, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Helados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-039' AS codigo, 'Helado de lucuma' AS nombre, 'Helado de lucuma, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Helados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-040' AS codigo, 'Paleta surtida' AS nombre, 'Paleta surtida, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Helados' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-041' AS codigo, 'Papas prefritas' AS nombre, 'Papas prefritas, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Papas y snacks' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-042' AS codigo, 'Papas rusticas' AS nombre, 'Papas rusticas, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Papas y snacks' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-043' AS codigo, 'Aros de cebolla' AS nombre, 'Aros de cebolla, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Papas y snacks' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-044' AS codigo, 'Nuggets vegetales' AS nombre, 'Nuggets vegetales, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Papas y snacks' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-045' AS codigo, 'Croquetas de papa' AS nombre, 'Croquetas de papa, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Papas y snacks' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-046' AS codigo, 'Masa de hoja' AS nombre, 'Masa de hoja, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Masas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-047' AS codigo, 'Masa de empanada' AS nombre, 'Masa de empanada, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Masas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-048' AS codigo, 'Masa de pizza' AS nombre, 'Masa de pizza, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Masas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-049' AS codigo, 'Masa de wanton' AS nombre, 'Masa de wanton, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Masas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-050' AS codigo, 'Masa para sopaipillas' AS nombre, 'Masa para sopaipillas, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Masas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-051' AS codigo, 'Empanada de pino' AS nombre, 'Empanada de pino, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Empanadas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-052' AS codigo, 'Empanada de queso' AS nombre, 'Empanada de queso, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Empanadas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-053' AS codigo, 'Empanada napolitana' AS nombre, 'Empanada napolitana, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Empanadas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-054' AS codigo, 'Empanada de pollo' AS nombre, 'Empanada de pollo, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Empanadas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-055' AS codigo, 'Empanada de camaron queso' AS nombre, 'Empanada de camaron queso, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Empanadas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-056' AS codigo, 'Pizza mozzarella' AS nombre, 'Pizza mozzarella, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Pizzas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-057' AS codigo, 'Pizza napolitana' AS nombre, 'Pizza napolitana, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Pizzas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-058' AS codigo, 'Pizza pepperoni' AS nombre, 'Pizza pepperoni, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Pizzas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-059' AS codigo, 'Pizza cuatro quesos' AS nombre, 'Pizza cuatro quesos, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Pizzas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-060' AS codigo, 'Pizza vegetariana' AS nombre, 'Pizza vegetariana, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Pizzas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-061' AS codigo, 'Hallulla precocida' AS nombre, 'Hallulla precocida, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Panaderia congelada' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-062' AS codigo, 'Marraqueta precocida' AS nombre, 'Marraqueta precocida, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Panaderia congelada' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-063' AS codigo, 'Pan amasado' AS nombre, 'Pan amasado, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Panaderia congelada' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-064' AS codigo, 'Baguette' AS nombre, 'Baguette, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Panaderia congelada' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-065' AS codigo, 'Medialuna' AS nombre, 'Medialuna, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Panaderia congelada' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-066' AS codigo, 'Torta tres leches' AS nombre, 'Torta tres leches, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Postres' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-067' AS codigo, 'Kuchen de frambuesa' AS nombre, 'Kuchen de frambuesa, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Postres' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-068' AS codigo, 'Cheesecake' AS nombre, 'Cheesecake, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Postres' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-069' AS codigo, 'Brazo de reina' AS nombre, 'Brazo de reina, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Postres' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-070' AS codigo, 'Torta de chocolate' AS nombre, 'Torta de chocolate, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Postres' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-071' AS codigo, 'Queso mantecoso' AS nombre, 'Queso mantecoso, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Lacteos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-072' AS codigo, 'Queso mozzarella' AS nombre, 'Queso mozzarella, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Lacteos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-073' AS codigo, 'Mantequilla' AS nombre, 'Mantequilla, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Lacteos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-074' AS codigo, 'Crema de leche' AS nombre, 'Crema de leche, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Lacteos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-075' AS codigo, 'Leche entera' AS nombre, 'Leche entera, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Lacteos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-076' AS codigo, 'Jugo de naranja' AS nombre, 'Jugo de naranja, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Bebidas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-077' AS codigo, 'Bebida cola' AS nombre, 'Bebida cola, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Bebidas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-078' AS codigo, 'Agua mineral' AS nombre, 'Agua mineral, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Bebidas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-079' AS codigo, 'Nectar de durazno' AS nombre, 'Nectar de durazno, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Bebidas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-080' AS codigo, 'Bebida limon' AS nombre, 'Bebida limon, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Bebidas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-081' AS codigo, 'Arroz grado 1' AS nombre, 'Arroz grado 1, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Abarrotes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-082' AS codigo, 'Porotos negros' AS nombre, 'Porotos negros, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Abarrotes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-083' AS codigo, 'Lentejas' AS nombre, 'Lentejas, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Abarrotes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-084' AS codigo, 'Harina sin polvos' AS nombre, 'Harina sin polvos, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Abarrotes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-085' AS codigo, 'Azucar granulada' AS nombre, 'Azucar granulada, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Abarrotes' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-086' AS codigo, 'Salsa de tomate' AS nombre, 'Salsa de tomate, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Salsas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-087' AS codigo, 'Mayonesa' AS nombre, 'Mayonesa, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Salsas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-088' AS codigo, 'Ketchup' AS nombre, 'Ketchup, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Salsas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-089' AS codigo, 'Mostaza' AS nombre, 'Mostaza, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Salsas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-090' AS codigo, 'Salsa barbecue' AS nombre, 'Salsa barbecue, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Salsas' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-091' AS codigo, 'Masa de galletas' AS nombre, 'Masa de galletas, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Reposteria' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-092' AS codigo, 'Brownie individual' AS nombre, 'Brownie individual, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Reposteria' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-093' AS codigo, 'Churros' AS nombre, 'Churros, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Reposteria' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-094' AS codigo, 'Berlines' AS nombre, 'Berlines, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Reposteria' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-095' AS codigo, 'Donas glaseadas' AS nombre, 'Donas glaseadas, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Reposteria' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-096' AS codigo, 'Hielo en cubos' AS nombre, 'Hielo en cubos, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Otros productos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-097' AS codigo, 'Hielo frappe' AS nombre, 'Hielo frappe, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Otros productos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-098' AS codigo, 'Bolsa termica' AS nombre, 'Bolsa termica, producto de prueba para reportes; formato 500 g.' AS descripcion, 'Otros productos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-099' AS codigo, 'Contenedor alimento' AS nombre, 'Contenedor alimento, producto de prueba para reportes; formato 750 g.' AS descripcion, 'Otros productos' AS categoria UNION ALL
    SELECT 'DEMO-NOV26-100' AS codigo, 'Pack gel refrigerante' AS nombre, 'Pack gel refrigerante, producto de prueba para reportes; formato 1000 g.' AS descripcion, 'Otros productos' AS categoria
  ) AS v
  JOIN (SELECT nombre, MIN(idCategoria) AS idCategoria FROM categoria WHERE deleted_at IS NULL GROUP BY nombre) c ON c.nombre=v.categoria
  WHERE NOT EXISTS (SELECT 1 FROM producto p WHERE p.codigo=v.codigo);

  -- Precio vigente de referencia para cada producto de demostracion
  INSERT INTO lista_precio (codigoProducto, precioVenta, fechaInicio, fechaFin)
  SELECT v.codigo, v.precio, '2026-01-01 00:00:00', NULL
  FROM (
    SELECT 'DEMO-NOV26-001' AS codigo, 1200.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-002' AS codigo, 1430.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-003' AS codigo, 1660.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-004' AS codigo, 1890.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-005' AS codigo, 2120.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-006' AS codigo, 1520.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-007' AS codigo, 1750.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-008' AS codigo, 1980.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-009' AS codigo, 2200.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-010' AS codigo, 2430.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-011' AS codigo, 1830.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-012' AS codigo, 2060.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-013' AS codigo, 2290.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-014' AS codigo, 2520.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-015' AS codigo, 2750.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-016' AS codigo, 2150.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-017' AS codigo, 2380.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-018' AS codigo, 2610.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-019' AS codigo, 2840.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-020' AS codigo, 3070.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-021' AS codigo, 2470.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-022' AS codigo, 2700.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-023' AS codigo, 2930.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-024' AS codigo, 3160.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-025' AS codigo, 3380.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-026' AS codigo, 2790.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-027' AS codigo, 3010.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-028' AS codigo, 3240.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-029' AS codigo, 3470.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-030' AS codigo, 3700.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-031' AS codigo, 3100.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-032' AS codigo, 3330.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-033' AS codigo, 3560.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-034' AS codigo, 3790.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-035' AS codigo, 4020.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-036' AS codigo, 3420.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-037' AS codigo, 3650.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-038' AS codigo, 3880.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-039' AS codigo, 4110.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-040' AS codigo, 4340.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-041' AS codigo, 3740.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-042' AS codigo, 3970.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-043' AS codigo, 4190.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-044' AS codigo, 4420.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-045' AS codigo, 4650.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-046' AS codigo, 4050.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-047' AS codigo, 4280.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-048' AS codigo, 4510.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-049' AS codigo, 4740.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-050' AS codigo, 4970.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-051' AS codigo, 4370.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-052' AS codigo, 4600.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-053' AS codigo, 4830.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-054' AS codigo, 5060.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-055' AS codigo, 5290.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-056' AS codigo, 4690.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-057' AS codigo, 4920.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-058' AS codigo, 5150.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-059' AS codigo, 5370.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-060' AS codigo, 5600.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-061' AS codigo, 5000.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-062' AS codigo, 5230.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-063' AS codigo, 5460.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-064' AS codigo, 5690.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-065' AS codigo, 5920.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-066' AS codigo, 5320.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-067' AS codigo, 5550.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-068' AS codigo, 5780.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-069' AS codigo, 6010.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-070' AS codigo, 6240.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-071' AS codigo, 5640.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-072' AS codigo, 5870.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-073' AS codigo, 6100.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-074' AS codigo, 6330.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-075' AS codigo, 6550.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-076' AS codigo, 5960.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-077' AS codigo, 6180.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-078' AS codigo, 6410.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-079' AS codigo, 6640.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-080' AS codigo, 6870.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-081' AS codigo, 6270.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-082' AS codigo, 6500.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-083' AS codigo, 6730.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-084' AS codigo, 6960.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-085' AS codigo, 7190.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-086' AS codigo, 6590.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-087' AS codigo, 6820.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-088' AS codigo, 7050.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-089' AS codigo, 7280.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-090' AS codigo, 7510.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-091' AS codigo, 6910.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-092' AS codigo, 7140.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-093' AS codigo, 7360.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-094' AS codigo, 7590.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-095' AS codigo, 7820.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-096' AS codigo, 7220.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-097' AS codigo, 7450.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-098' AS codigo, 7680.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-099' AS codigo, 7910.00 AS precio UNION ALL
    SELECT 'DEMO-NOV26-100' AS codigo, 8140.00 AS precio
  ) AS v
  WHERE NOT EXISTS (SELECT 1 FROM lista_precio lp WHERE lp.codigoProducto=v.codigo AND lp.fechaFin IS NULL);

  -- Asegurar tipo de salida y metodo de pago sin asumir IDs fijos
  INSERT INTO tipo_salida (nombre) SELECT 'Venta' WHERE NOT EXISTS (SELECT 1 FROM tipo_salida WHERE LOWER(TRIM(nombre))='venta');
  INSERT INTO metodo_pago (nombre) SELECT 'Efectivo' WHERE NOT EXISTS (SELECT 1 FROM metodo_pago WHERE LOWER(TRIM(nombre))='efectivo');
  SET @demo_usuario = (SELECT idUsuario FROM usuario WHERE activo=1 AND deleted_at IS NULL ORDER BY idUsuario LIMIT 1);
  SET @demo_tipo = (SELECT idTipo FROM tipo_salida WHERE LOWER(TRIM(nombre))='venta' ORDER BY idTipo LIMIT 1);
  SET @demo_metodo = (SELECT idMetodo FROM metodo_pago WHERE LOWER(TRIM(nombre))='efectivo' ORDER BY idMetodo LIMIT 1);
  SET @demo_ventas_antes = (SELECT COUNT(*) FROM salida WHERE fecha >= '2026-11-01' AND fecha < '2026-12-01' AND idTipo=@demo_tipo);

  -- Ventas detalladas: cada dia tiene 10 + MOD(dia*7, 21) tickets (10 a 30).
  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 10:03:00', @demo_tipo, @demo_usuario, NULL, 42240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-021', 3, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 3, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 3, 6640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42240.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 17:20:11', @demo_tipo, @demo_usuario, NULL, 14750.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-028', 1, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 1, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 1, 6590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14750.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 12:37:22', @demo_tipo, @demo_usuario, NULL, 34140.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-035', 2, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 2, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 2, 7360.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34140.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 19:54:33', @demo_tipo, @demo_usuario, NULL, 53250.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-042', 3, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 3, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 3, 8140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 53250.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 14:11:44', @demo_tipo, @demo_usuario, NULL, 12900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-049', 1, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 1, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 1, 1750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12900.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 09:28:55', @demo_tipo, @demo_usuario, NULL, 28800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-056', 2, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 2, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 2, 2520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28800.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 16:45:06', @demo_tipo, @demo_usuario, NULL, 45210.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-063', 3, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 3, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 3, 2470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45210.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 11:02:17', @demo_tipo, @demo_usuario, NULL, 17390.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-070', 1, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 1, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 1, 3240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17390.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 18:19:28', @demo_tipo, @demo_usuario, NULL, 23440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-077', 2, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 2, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 2, 4020.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23440.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 13:36:39', @demo_tipo, @demo_usuario, NULL, 39660.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-084', 3, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 3, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 3, 3970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 39660.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 20:53:50', @demo_tipo, @demo_usuario, NULL, 14720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-091', 1, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 1, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 1, 4740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14720.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 15:10:01', @demo_tipo, @demo_usuario, NULL, 30760.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-098', 2, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 2, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 2, 4690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30760.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 10:27:12', @demo_tipo, @demo_usuario, NULL, 34110.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-005', 3, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 3, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 3, 5460.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34110.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 17:44:23', @demo_tipo, @demo_usuario, NULL, 12040.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-012', 1, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 1, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 1, 6240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12040.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 12:01:34', @demo_tipo, @demo_usuario, NULL, 27060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-019', 2, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 2, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 2, 6180.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27060.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 19:18:45', @demo_tipo, @demo_usuario, NULL, 45120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-026', 3, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 3, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 3, 6960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45120.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-01 14:35:56', @demo_tipo, @demo_usuario, NULL, 15700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-033', 1, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 1, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 1, 6910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15700.00, '2026-11-01');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 11:06:00', @demo_tipo, @demo_usuario, NULL, 39480.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-053', 3, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 3, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 3, 1830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 39480.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 18:23:11', @demo_tipo, @demo_usuario, NULL, 15490.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-060', 1, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 1, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 1, 2610.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15490.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 13:40:22', @demo_tipo, @demo_usuario, NULL, 32300.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-067', 2, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 2, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 2, 3380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32300.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 20:57:33', @demo_tipo, @demo_usuario, NULL, 33960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-074', 3, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 3, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 3, 3330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33960.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 15:14:44', @demo_tipo, @demo_usuario, NULL, 12810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-081', 1, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 1, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 1, 4110.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12810.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 10:31:55', @demo_tipo, @demo_usuario, NULL, 26960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-088', 2, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 2, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 2, 4050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26960.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 17:48:06', @demo_tipo, @demo_usuario, NULL, 47430.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-095', 3, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 3, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 3, 4830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 47430.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 12:05:17', @demo_tipo, @demo_usuario, NULL, 10130.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-002', 1, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 1, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 1, 5600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10130.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 19:22:28', @demo_tipo, @demo_usuario, NULL, 23260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-009', 2, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 2, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 2, 5550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23260.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 14:39:39', @demo_tipo, @demo_usuario, NULL, 39390.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-016', 3, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 3, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 3, 6330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 39390.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 09:56:50', @demo_tipo, @demo_usuario, NULL, 13800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-023', 1, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 1, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 1, 6270.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13800.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 16:13:01', @demo_tipo, @demo_usuario, NULL, 32240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-030', 2, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 2, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 2, 7050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32240.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 11:30:12', @demo_tipo, @demo_usuario, NULL, 50370.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-037', 3, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 3, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 3, 7820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 50370.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 18:47:23', @demo_tipo, @demo_usuario, NULL, 11950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-044', 1, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 1, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 1, 1430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11950.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 13:04:34', @demo_tipo, @demo_usuario, NULL, 26880.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-051', 2, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 2, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 2, 2200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26880.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 20:21:45', @demo_tipo, @demo_usuario, NULL, 42360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-058', 3, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 3, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 3, 2150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42360.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 15:38:56', @demo_tipo, @demo_usuario, NULL, 16440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-065', 1, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 1, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 1, 2930.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16440.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 10:55:07', @demo_tipo, @demo_usuario, NULL, 21540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-072', 2, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 2, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 2, 3700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 21540.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 17:12:18', @demo_tipo, @demo_usuario, NULL, 36810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-079', 3, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 3, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 3, 3650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36810.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 12:29:29', @demo_tipo, @demo_usuario, NULL, 13760.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-086', 1, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 1, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 1, 4420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13760.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 19:46:40', @demo_tipo, @demo_usuario, NULL, 28860.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-093', 2, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 2, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 2, 4370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28860.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 14:03:51', @demo_tipo, @demo_usuario, NULL, 50280.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-100', 3, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 3, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 3, 5150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 50280.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 09:20:02', @demo_tipo, @demo_usuario, NULL, 11090.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-007', 1, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 1, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 1, 5920.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11090.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-02 16:37:13', @demo_tipo, @demo_usuario, NULL, 25160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-014', 2, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 2, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 2, 5870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25160.00, '2026-11-02');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 12:09:00', @demo_tipo, @demo_usuario, NULL, 16390.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-034', 1, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 1, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 1, 7140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16390.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 19:26:11', @demo_tipo, @demo_usuario, NULL, 35780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-041', 2, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 2, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 2, 7910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35780.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 14:43:22', @demo_tipo, @demo_usuario, NULL, 36630.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-048', 3, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 3, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 3, 1520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36630.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 09:00:33', @demo_tipo, @demo_usuario, NULL, 14540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-055', 1, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 1, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 1, 2290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14540.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 16:17:44', @demo_tipo, @demo_usuario, NULL, 30420.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-062', 2, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 2, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 2, 3070.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30420.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 11:34:55', @demo_tipo, @demo_usuario, NULL, 50100.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-069', 3, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 3, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 3, 3010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 50100.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 18:51:06', @demo_tipo, @demo_usuario, NULL, 11870.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-076', 1, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 1, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 1, 3790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11870.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 13:08:17', @demo_tipo, @demo_usuario, NULL, 25060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-083', 2, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 2, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 2, 3740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25060.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 20:25:28', @demo_tipo, @demo_usuario, NULL, 44580.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-090', 3, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 3, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 3, 4510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 44580.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-03 15:42:39', @demo_tipo, @demo_usuario, NULL, 15530.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-097', 1, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 1, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 1, 5290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15530.00, '2026-11-03');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 13:12:00', @demo_tipo, @demo_usuario, NULL, 38940.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-017', 3, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 3, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 3, 6550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38940.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 20:29:11', @demo_tipo, @demo_usuario, NULL, 14490.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-024', 1, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 1, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 1, 6500.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14490.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 15:46:22', @demo_tipo, @demo_usuario, NULL, 31960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-031', 2, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 2, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 2, 7280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31960.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 10:03:33', @demo_tipo, @demo_usuario, NULL, 49950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-038', 3, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 3, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 3, 7220.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 49950.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 17:20:44', @demo_tipo, @demo_usuario, NULL, 12640.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-045', 1, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 1, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 1, 1660.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12640.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 12:37:55', @demo_tipo, @demo_usuario, NULL, 26600.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-052', 2, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 2, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 2, 2430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26600.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 19:54:06', @demo_tipo, @demo_usuario, NULL, 44400.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-059', 3, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 3, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 3, 2380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 44400.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 14:11:17', @demo_tipo, @demo_usuario, NULL, 16300.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-066', 1, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 1, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 1, 3160.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16300.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 09:28:28', @demo_tipo, @demo_usuario, NULL, 21260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-073', 2, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 2, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 2, 3100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 21260.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 16:45:39', @demo_tipo, @demo_usuario, NULL, 38850.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-080', 3, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 3, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 3, 3880.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38850.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 11:02:50', @demo_tipo, @demo_usuario, NULL, 13620.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-087', 1, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 1, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 1, 4650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13620.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 18:19:01', @demo_tipo, @demo_usuario, NULL, 30240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-094', 2, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 2, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 2, 4600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30240.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 13:36:12', @demo_tipo, @demo_usuario, NULL, 30810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-001', 3, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 3, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 3, 5370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30810.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 20:53:23', @demo_tipo, @demo_usuario, NULL, 10950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-008', 1, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 1, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 1, 5320.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10950.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 15:10:34', @demo_tipo, @demo_usuario, NULL, 26540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-015', 2, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 2, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 2, 6100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26540.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 10:27:45', @demo_tipo, @demo_usuario, NULL, 41820.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-022', 3, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 3, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 3, 6870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41820.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-04 17:44:56', @demo_tipo, @demo_usuario, NULL, 15440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-029', 1, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 1, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 1, 6820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15440.00, '2026-11-04');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 14:15:00', @demo_tipo, @demo_usuario, NULL, 38700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-049', 3, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 3, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 3, 1750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38700.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 09:32:11', @demo_tipo, @demo_usuario, NULL, 14400.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-056', 1, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 1, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 1, 2520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14400.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 16:49:22', @demo_tipo, @demo_usuario, NULL, 30140.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-063', 2, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 2, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 2, 2470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30140.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 11:06:33', @demo_tipo, @demo_usuario, NULL, 52170.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-070', 3, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 3, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 3, 3240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 52170.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 18:23:44', @demo_tipo, @demo_usuario, NULL, 11720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-077', 1, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 1, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 1, 4020.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11720.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 13:40:55', @demo_tipo, @demo_usuario, NULL, 26440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-084', 2, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 2, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 2, 3970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26440.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 20:57:06', @demo_tipo, @demo_usuario, NULL, 44160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-091', 3, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 3, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 3, 4740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 44160.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 15:14:17', @demo_tipo, @demo_usuario, NULL, 15380.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-098', 1, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 1, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 1, 4690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15380.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 10:31:28', @demo_tipo, @demo_usuario, NULL, 22740.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-005', 2, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 2, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 2, 5460.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22740.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 17:48:39', @demo_tipo, @demo_usuario, NULL, 36120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-012', 3, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 3, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 3, 6240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36120.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 12:05:50', @demo_tipo, @demo_usuario, NULL, 13530.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-019', 1, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 1, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 1, 6180.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13530.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 19:22:01', @demo_tipo, @demo_usuario, NULL, 30080.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-026', 2, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 2, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 2, 6960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30080.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 14:39:12', @demo_tipo, @demo_usuario, NULL, 47100.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-033', 3, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 3, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 3, 6910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 47100.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 09:56:23', @demo_tipo, @demo_usuario, NULL, 18030.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-040', 1, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 1, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 1, 7680.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 18030.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 16:13:34', @demo_tipo, @demo_usuario, NULL, 24720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-047', 2, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 2, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 2, 2120.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24720.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 11:30:45', @demo_tipo, @demo_usuario, NULL, 41550.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-054', 3, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 3, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 3, 2060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41550.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 18:47:56', @demo_tipo, @demo_usuario, NULL, 15350.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-061', 1, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 1, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 1, 2840.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15350.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 13:04:07', @demo_tipo, @demo_usuario, NULL, 32040.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-068', 2, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 2, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 2, 2790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32040.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 20:21:18', @demo_tipo, @demo_usuario, NULL, 36000.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-075', 3, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 3, 1890.00),
  (@demo_salida, 'DEMO-NOV26-033', 3, 3560.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36000.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 15:38:29', @demo_tipo, @demo_usuario, NULL, 12670.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-082', 1, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 1, 1830.00),
  (@demo_salida, 'DEMO-NOV26-040', 1, 4340.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12670.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 10:55:40', @demo_tipo, @demo_usuario, NULL, 28340.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-089', 2, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 2, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 2, 4280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28340.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 17:12:51', @demo_tipo, @demo_usuario, NULL, 46980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-096', 3, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 3, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 3, 5060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46980.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 12:29:02', @demo_tipo, @demo_usuario, NULL, 9990.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-003', 1, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 1, 3330.00),
  (@demo_salida, 'DEMO-NOV26-061', 1, 5000.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 9990.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-05 19:46:13', @demo_tipo, @demo_usuario, NULL, 24640.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-010', 2, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 2, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 2, 5780.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24640.00, '2026-11-05');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 15:18:00', @demo_tipo, @demo_usuario, NULL, 16120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-030', 1, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 1, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 1, 7050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16120.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 10:35:11', @demo_tipo, @demo_usuario, NULL, 33580.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-037', 2, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 2, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 2, 7820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33580.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 17:52:22', @demo_tipo, @demo_usuario, NULL, 35850.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-044', 3, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 3, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 3, 1430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35850.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 12:09:33', @demo_tipo, @demo_usuario, NULL, 13440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-051', 1, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 1, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 1, 2200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13440.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 19:26:44', @demo_tipo, @demo_usuario, NULL, 28240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-058', 2, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 2, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 2, 2150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28240.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 14:43:55', @demo_tipo, @demo_usuario, NULL, 49320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-065', 3, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 3, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 3, 2930.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 49320.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 09:00:06', @demo_tipo, @demo_usuario, NULL, 10770.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-072', 1, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 1, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 1, 3700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10770.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 16:17:17', @demo_tipo, @demo_usuario, NULL, 24540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-079', 2, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 2, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 2, 3650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24540.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 11:34:28', @demo_tipo, @demo_usuario, NULL, 41280.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-086', 3, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 3, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 3, 4420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41280.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-06 18:51:39', @demo_tipo, @demo_usuario, NULL, 14430.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-093', 1, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 1, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 1, 4370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14430.00, '2026-11-06');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 16:21:00', @demo_tipo, @demo_usuario, NULL, 35700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-013', 3, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 3, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 3, 5640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35700.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 11:38:11', @demo_tipo, @demo_usuario, NULL, 14220.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-020', 1, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 1, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 1, 6410.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14220.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 18:55:22', @demo_tipo, @demo_usuario, NULL, 29780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-027', 2, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 2, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 2, 7190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29780.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 13:12:33', @demo_tipo, @demo_usuario, NULL, 49170.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-034', 3, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 3, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 3, 7140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 49170.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 20:29:44', @demo_tipo, @demo_usuario, NULL, 17890.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-041', 1, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 1, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 1, 7910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17890.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 15:46:55', @demo_tipo, @demo_usuario, NULL, 24420.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-048', 2, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 2, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 2, 1520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24420.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 10:03:06', @demo_tipo, @demo_usuario, NULL, 43620.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-055', 3, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 3, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 3, 2290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 43620.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 17:20:17', @demo_tipo, @demo_usuario, NULL, 15210.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-062', 1, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 1, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 1, 3070.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15210.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 12:37:28', @demo_tipo, @demo_usuario, NULL, 33400.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-069', 2, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 2, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 2, 3010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33400.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 19:54:39', @demo_tipo, @demo_usuario, NULL, 35610.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-076', 3, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 3, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 3, 3790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35610.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 14:11:50', @demo_tipo, @demo_usuario, NULL, 12530.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-083', 1, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 1, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 1, 3740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12530.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 09:28:01', @demo_tipo, @demo_usuario, NULL, 29720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-090', 2, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 2, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 2, 4510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29720.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 16:45:12', @demo_tipo, @demo_usuario, NULL, 46590.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-097', 3, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 3, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 3, 5290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46590.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 11:02:23', @demo_tipo, @demo_usuario, NULL, 10680.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-004', 1, 1890.00),
  (@demo_salida, 'DEMO-NOV26-033', 1, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 1, 5230.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10680.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 18:19:34', @demo_tipo, @demo_usuario, NULL, 24360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-011', 2, 1830.00),
  (@demo_salida, 'DEMO-NOV26-040', 2, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 2, 6010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24360.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 13:36:45', @demo_tipo, @demo_usuario, NULL, 38550.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-018', 3, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 3, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 3, 5960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38550.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-07 20:53:56', @demo_tipo, @demo_usuario, NULL, 15170.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-025', 1, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 1, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 1, 6730.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15170.00, '2026-11-07');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 17:24:00', @demo_tipo, @demo_usuario, NULL, 37920.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-045', 3, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 3, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 3, 1660.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37920.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 12:41:11', @demo_tipo, @demo_usuario, NULL, 13300.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-052', 1, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 1, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 1, 2430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13300.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 19:58:22', @demo_tipo, @demo_usuario, NULL, 29600.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-059', 2, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 2, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 2, 2380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29600.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 14:15:33', @demo_tipo, @demo_usuario, NULL, 48900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-066', 3, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 3, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 3, 3160.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 48900.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 09:32:44', @demo_tipo, @demo_usuario, NULL, 10630.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-073', 1, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 1, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 1, 3100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10630.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 16:49:55', @demo_tipo, @demo_usuario, NULL, 25900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-080', 2, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 2, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 2, 3880.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25900.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 11:06:06', @demo_tipo, @demo_usuario, NULL, 40860.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-087', 3, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 3, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 3, 4650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 40860.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 18:23:17', @demo_tipo, @demo_usuario, NULL, 15120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-094', 1, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 1, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 1, 4600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15120.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 13:40:28', @demo_tipo, @demo_usuario, NULL, 20540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-001', 2, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 2, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 2, 5370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 20540.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 20:57:39', @demo_tipo, @demo_usuario, NULL, 32850.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-008', 3, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 3, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 3, 5320.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32850.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 15:14:50', @demo_tipo, @demo_usuario, NULL, 13270.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-015', 1, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 1, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 1, 6100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13270.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 10:31:01', @demo_tipo, @demo_usuario, NULL, 27880.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-022', 2, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 2, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 2, 6870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27880.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 17:48:12', @demo_tipo, @demo_usuario, NULL, 46320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-029', 3, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 3, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 3, 6820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46320.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 12:05:23', @demo_tipo, @demo_usuario, NULL, 16930.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-036', 1, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 1, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 1, 7590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16930.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 19:22:34', @demo_tipo, @demo_usuario, NULL, 22520.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-043', 2, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 2, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 2, 1200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22520.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 14:39:45', @demo_tipo, @demo_usuario, NULL, 40770.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-050', 3, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 3, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 3, 1980.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 40770.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 09:56:56', @demo_tipo, @demo_usuario, NULL, 14260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-057', 1, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 1, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 1, 2750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14260.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 16:13:07', @demo_tipo, @demo_usuario, NULL, 31500.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-064', 2, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 2, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 2, 2700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31500.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 11:30:18', @demo_tipo, @demo_usuario, NULL, 51750.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-071', 3, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 3, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 3, 3470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 51750.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 18:47:29', @demo_tipo, @demo_usuario, NULL, 11580.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-078', 1, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 1, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 1, 3420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11580.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 13:04:40', @demo_tipo, @demo_usuario, NULL, 27800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-085', 2, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 2, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 2, 4190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27800.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 20:21:51', @demo_tipo, @demo_usuario, NULL, 43740.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-092', 3, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 3, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 3, 4970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 43740.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 15:38:02', @demo_tipo, @demo_usuario, NULL, 16070.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-099', 1, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 1, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 1, 4920.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16070.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-08 10:55:13', @demo_tipo, @demo_usuario, NULL, 22460.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-006', 2, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 2, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 2, 5690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22460.00, '2026-11-08');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 18:27:00', @demo_tipo, @demo_usuario, NULL, 15040.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-026', 1, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 1, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 1, 6960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15040.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 13:44:11', @demo_tipo, @demo_usuario, NULL, 31400.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-033', 2, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 2, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 2, 6910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31400.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 20:01:22', @demo_tipo, @demo_usuario, NULL, 54090.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-040', 3, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 3, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 3, 7680.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 54090.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 15:18:33', @demo_tipo, @demo_usuario, NULL, 12360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-047', 1, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 1, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 1, 2120.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12360.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 10:35:44', @demo_tipo, @demo_usuario, NULL, 27700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-054', 2, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 2, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 2, 2060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27700.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 17:52:55', @demo_tipo, @demo_usuario, NULL, 46050.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-061', 3, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 3, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 3, 2840.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46050.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 12:09:06', @demo_tipo, @demo_usuario, NULL, 16020.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-068', 1, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 1, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 1, 2790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16020.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 19:26:17', @demo_tipo, @demo_usuario, NULL, 24000.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-075', 2, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 2, 1890.00),
  (@demo_salida, 'DEMO-NOV26-033', 2, 3560.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24000.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 14:43:28', @demo_tipo, @demo_usuario, NULL, 38010.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-082', 3, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 3, 1830.00),
  (@demo_salida, 'DEMO-NOV26-040', 3, 4340.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38010.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-09 09:00:39', @demo_tipo, @demo_usuario, NULL, 14170.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-089', 1, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 1, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 1, 4280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14170.00, '2026-11-09');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 19:30:00', @demo_tipo, @demo_usuario, NULL, 34890.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-009', 3, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 3, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 3, 5550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34890.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 14:47:11', @demo_tipo, @demo_usuario, NULL, 13130.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-016', 1, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 1, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 1, 6330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13130.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 09:04:22', @demo_tipo, @demo_usuario, NULL, 27600.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-023', 2, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 2, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 2, 6270.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27600.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 16:21:33', @demo_tipo, @demo_usuario, NULL, 48360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-030', 3, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 3, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 3, 7050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 48360.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 11:38:44', @demo_tipo, @demo_usuario, NULL, 16790.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-037', 1, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 1, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 1, 7820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16790.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 18:55:55', @demo_tipo, @demo_usuario, NULL, 23900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-044', 2, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 2, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 2, 1430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23900.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 13:12:06', @demo_tipo, @demo_usuario, NULL, 40320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-051', 3, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 3, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 3, 2200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 40320.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 20:29:17', @demo_tipo, @demo_usuario, NULL, 14120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-058', 1, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 1, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 1, 2150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14120.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 15:46:28', @demo_tipo, @demo_usuario, NULL, 32880.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-065', 2, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 2, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 2, 2930.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32880.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 10:03:39', @demo_tipo, @demo_usuario, NULL, 32310.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-072', 3, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 3, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 3, 3700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32310.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 17:20:50', @demo_tipo, @demo_usuario, NULL, 12270.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-079', 1, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 1, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 1, 3650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12270.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 12:37:01', @demo_tipo, @demo_usuario, NULL, 27520.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-086', 2, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 2, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 2, 4420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27520.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 19:54:12', @demo_tipo, @demo_usuario, NULL, 43290.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-093', 3, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 3, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 3, 4370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 43290.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 14:11:23', @demo_tipo, @demo_usuario, NULL, 16760.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-100', 1, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 1, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 1, 5150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16760.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 09:28:34', @demo_tipo, @demo_usuario, NULL, 22180.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-007', 2, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 2, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 2, 5920.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22180.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 16:45:45', @demo_tipo, @demo_usuario, NULL, 37740.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-014', 3, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 3, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 3, 5870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37740.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-10 11:02:56', @demo_tipo, @demo_usuario, NULL, 14080.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-021', 1, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 1, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 1, 6640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14080.00, '2026-11-10');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 20:33:00', @demo_tipo, @demo_usuario, NULL, 53670.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-041', 3, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 3, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 3, 7910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 53670.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 15:50:11', @demo_tipo, @demo_usuario, NULL, 12210.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-048', 1, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 1, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 1, 1520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12210.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 10:07:22', @demo_tipo, @demo_usuario, NULL, 29080.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-055', 2, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 2, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 2, 2290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29080.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 17:24:33', @demo_tipo, @demo_usuario, NULL, 45630.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-062', 3, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 3, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 3, 3070.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45630.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 12:41:44', @demo_tipo, @demo_usuario, NULL, 16700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-069', 1, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 1, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 1, 3010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16700.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 19:58:55', @demo_tipo, @demo_usuario, NULL, 23740.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-076', 2, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 2, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 2, 3790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23740.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 14:15:06', @demo_tipo, @demo_usuario, NULL, 37590.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-083', 3, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 3, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 3, 3740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37590.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 09:32:17', @demo_tipo, @demo_usuario, NULL, 14860.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-090', 1, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 1, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 1, 4510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14860.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 16:49:28', @demo_tipo, @demo_usuario, NULL, 31060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-097', 2, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 2, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 2, 5290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31060.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 11:06:39', @demo_tipo, @demo_usuario, NULL, 32040.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-004', 3, 1890.00),
  (@demo_salida, 'DEMO-NOV26-033', 3, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 3, 5230.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32040.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 18:23:50', @demo_tipo, @demo_usuario, NULL, 12180.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-011', 1, 1830.00),
  (@demo_salida, 'DEMO-NOV26-040', 1, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 1, 6010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12180.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 13:40:01', @demo_tipo, @demo_usuario, NULL, 25700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-018', 2, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 2, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 2, 5960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25700.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 20:57:12', @demo_tipo, @demo_usuario, NULL, 45510.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-025', 3, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 3, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 3, 6730.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45510.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 15:14:23', @demo_tipo, @demo_usuario, NULL, 15840.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-032', 1, 3330.00),
  (@demo_salida, 'DEMO-NOV26-061', 1, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 1, 7510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15840.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 10:31:34', @demo_tipo, @demo_usuario, NULL, 34680.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-039', 2, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 2, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 2, 7450.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34680.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 17:48:45', @demo_tipo, @demo_usuario, NULL, 37470.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-046', 3, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 3, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 3, 1890.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37470.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 12:05:56', @demo_tipo, @demo_usuario, NULL, 13160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-053', 1, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 1, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 1, 1830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13160.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 19:22:07', @demo_tipo, @demo_usuario, NULL, 30980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-060', 2, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 2, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 2, 2610.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30980.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 14:39:18', @demo_tipo, @demo_usuario, NULL, 48450.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-067', 3, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 3, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 3, 3380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 48450.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 09:56:29', @demo_tipo, @demo_usuario, NULL, 11320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-074', 1, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 1, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 1, 3330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11320.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 16:13:40', @demo_tipo, @demo_usuario, NULL, 25620.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-081', 2, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 2, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 2, 4110.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25620.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 11:30:51', @demo_tipo, @demo_usuario, NULL, 40440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-088', 3, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 3, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 3, 4050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 40440.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 18:47:02', @demo_tipo, @demo_usuario, NULL, 15810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-095', 1, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 1, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 1, 4830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15810.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-11 13:04:13', @demo_tipo, @demo_usuario, NULL, 20260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-002', 2, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 2, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 2, 5600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 20260.00, '2026-11-11');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 09:36:00', @demo_tipo, @demo_usuario, NULL, 13940.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-022', 1, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 1, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 1, 6870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13940.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 16:53:11', @demo_tipo, @demo_usuario, NULL, 30880.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-029', 2, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 2, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 2, 6820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30880.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 11:10:22', @demo_tipo, @demo_usuario, NULL, 50790.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-036', 3, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 3, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 3, 7590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 50790.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 18:27:33', @demo_tipo, @demo_usuario, NULL, 11260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-043', 1, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 1, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 1, 1200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11260.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 13:44:44', @demo_tipo, @demo_usuario, NULL, 27180.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-050', 2, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 2, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 2, 1980.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27180.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 20:01:55', @demo_tipo, @demo_usuario, NULL, 42780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-057', 3, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 3, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 3, 2750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42780.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 15:18:06', @demo_tipo, @demo_usuario, NULL, 15750.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-064', 1, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 1, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 1, 2700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15750.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 10:35:17', @demo_tipo, @demo_usuario, NULL, 34500.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-071', 2, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 2, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 2, 3470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34500.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 17:52:28', @demo_tipo, @demo_usuario, NULL, 34740.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-078', 3, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 3, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 3, 3420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34740.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-12 12:09:39', @demo_tipo, @demo_usuario, NULL, 13900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-085', 1, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 1, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 1, 4190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13900.00, '2026-11-12');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 10:39:00', @demo_tipo, @demo_usuario, NULL, 34110.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-005', 3, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 3, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 3, 5460.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34110.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 17:56:11', @demo_tipo, @demo_usuario, NULL, 12040.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-012', 1, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 1, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 1, 6240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12040.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 12:13:22', @demo_tipo, @demo_usuario, NULL, 27060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-019', 2, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 2, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 2, 6180.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27060.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 19:30:33', @demo_tipo, @demo_usuario, NULL, 45120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-026', 3, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 3, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 3, 6960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45120.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 14:47:44', @demo_tipo, @demo_usuario, NULL, 15700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-033', 1, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 1, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 1, 6910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15700.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 09:04:55', @demo_tipo, @demo_usuario, NULL, 36060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-040', 2, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 2, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 2, 7680.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36060.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 16:21:06', @demo_tipo, @demo_usuario, NULL, 37080.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-047', 3, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 3, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 3, 2120.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37080.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 11:38:17', @demo_tipo, @demo_usuario, NULL, 13850.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-054', 1, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 1, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 1, 2060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13850.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 18:55:28', @demo_tipo, @demo_usuario, NULL, 30700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-061', 2, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 2, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 2, 2840.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30700.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 13:12:39', @demo_tipo, @demo_usuario, NULL, 48060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-068', 3, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 3, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 3, 2790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 48060.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 20:29:50', @demo_tipo, @demo_usuario, NULL, 12000.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-075', 1, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 1, 1890.00),
  (@demo_salida, 'DEMO-NOV26-033', 1, 3560.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12000.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 15:46:01', @demo_tipo, @demo_usuario, NULL, 25340.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-082', 2, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 2, 1830.00),
  (@demo_salida, 'DEMO-NOV26-040', 2, 4340.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25340.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 10:03:12', @demo_tipo, @demo_usuario, NULL, 42510.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-089', 3, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 3, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 3, 4280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42510.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 17:20:23', @demo_tipo, @demo_usuario, NULL, 15660.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-096', 1, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 1, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 1, 5060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15660.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 12:37:34', @demo_tipo, @demo_usuario, NULL, 19980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-003', 2, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 2, 3330.00),
  (@demo_salida, 'DEMO-NOV26-061', 2, 5000.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 19980.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 19:54:45', @demo_tipo, @demo_usuario, NULL, 36960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-010', 3, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 3, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 3, 5780.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36960.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-13 14:11:56', @demo_tipo, @demo_usuario, NULL, 12980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-017', 1, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 1, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 1, 6550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12980.00, '2026-11-13');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 11:42:00', @demo_tipo, @demo_usuario, NULL, 50370.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-037', 3, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 3, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 3, 7820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 50370.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 18:59:11', @demo_tipo, @demo_usuario, NULL, 11950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-044', 1, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 1, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 1, 1430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11950.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 13:16:22', @demo_tipo, @demo_usuario, NULL, 26880.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-051', 2, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 2, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 2, 2200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26880.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 20:33:33', @demo_tipo, @demo_usuario, NULL, 42360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-058', 3, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 3, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 3, 2150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42360.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 15:50:44', @demo_tipo, @demo_usuario, NULL, 16440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-065', 1, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 1, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 1, 2930.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16440.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 10:07:55', @demo_tipo, @demo_usuario, NULL, 21540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-072', 2, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 2, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 2, 3700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 21540.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 17:24:06', @demo_tipo, @demo_usuario, NULL, 36810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-079', 3, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 3, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 3, 3650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36810.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 12:41:17', @demo_tipo, @demo_usuario, NULL, 13760.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-086', 1, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 1, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 1, 4420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13760.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 19:58:28', @demo_tipo, @demo_usuario, NULL, 28860.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-093', 2, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 2, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 2, 4370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28860.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 14:15:39', @demo_tipo, @demo_usuario, NULL, 50280.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-100', 3, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 3, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 3, 5150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 50280.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 09:32:50', @demo_tipo, @demo_usuario, NULL, 11090.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-007', 1, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 1, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 1, 5920.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11090.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 16:49:01', @demo_tipo, @demo_usuario, NULL, 25160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-014', 2, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 2, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 2, 5870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25160.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 11:06:12', @demo_tipo, @demo_usuario, NULL, 42240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-021', 3, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 3, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 3, 6640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42240.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 18:23:23', @demo_tipo, @demo_usuario, NULL, 14750.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-028', 1, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 1, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 1, 6590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14750.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 13:40:34', @demo_tipo, @demo_usuario, NULL, 34140.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-035', 2, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 2, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 2, 7360.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34140.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 20:57:45', @demo_tipo, @demo_usuario, NULL, 53250.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-042', 3, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 3, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 3, 8140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 53250.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 15:14:56', @demo_tipo, @demo_usuario, NULL, 12900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-049', 1, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 1, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 1, 1750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12900.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 10:31:07', @demo_tipo, @demo_usuario, NULL, 28800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-056', 2, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 2, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 2, 2520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28800.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 17:48:18', @demo_tipo, @demo_usuario, NULL, 45210.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-063', 3, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 3, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 3, 2470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45210.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 12:05:29', @demo_tipo, @demo_usuario, NULL, 17390.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-070', 1, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 1, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 1, 3240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17390.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 19:22:40', @demo_tipo, @demo_usuario, NULL, 23440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-077', 2, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 2, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 2, 4020.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23440.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 14:39:51', @demo_tipo, @demo_usuario, NULL, 39660.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-084', 3, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 3, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 3, 3970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 39660.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 09:56:02', @demo_tipo, @demo_usuario, NULL, 14720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-091', 1, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 1, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 1, 4740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14720.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-14 16:13:13', @demo_tipo, @demo_usuario, NULL, 30760.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-098', 2, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 2, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 2, 4690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30760.00, '2026-11-14');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 12:45:00', @demo_tipo, @demo_usuario, NULL, 12850.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-018', 1, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 1, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 1, 5960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12850.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 19:02:11', @demo_tipo, @demo_usuario, NULL, 30340.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-025', 2, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 2, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 2, 6730.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30340.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 14:19:22', @demo_tipo, @demo_usuario, NULL, 47520.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-032', 3, 3330.00),
  (@demo_salida, 'DEMO-NOV26-061', 3, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 3, 7510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 47520.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 09:36:33', @demo_tipo, @demo_usuario, NULL, 17340.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-039', 1, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 1, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 1, 7450.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17340.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 16:53:44', @demo_tipo, @demo_usuario, NULL, 24980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-046', 2, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 2, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 2, 1890.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24980.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 11:10:55', @demo_tipo, @demo_usuario, NULL, 39480.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-053', 3, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 3, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 3, 1830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 39480.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 18:27:06', @demo_tipo, @demo_usuario, NULL, 15490.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-060', 1, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 1, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 1, 2610.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15490.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 13:44:17', @demo_tipo, @demo_usuario, NULL, 32300.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-067', 2, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 2, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 2, 3380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32300.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 20:01:28', @demo_tipo, @demo_usuario, NULL, 33960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-074', 3, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 3, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 3, 3330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33960.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-15 15:18:39', @demo_tipo, @demo_usuario, NULL, 12810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-081', 1, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 1, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 1, 4110.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12810.00, '2026-11-15');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 13:48:00', @demo_tipo, @demo_usuario, NULL, 30810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-001', 3, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 3, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 3, 5370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30810.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 20:05:11', @demo_tipo, @demo_usuario, NULL, 10950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-008', 1, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 1, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 1, 5320.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10950.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 15:22:22', @demo_tipo, @demo_usuario, NULL, 26540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-015', 2, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 2, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 2, 6100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26540.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 10:39:33', @demo_tipo, @demo_usuario, NULL, 41820.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-022', 3, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 3, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 3, 6870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41820.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 17:56:44', @demo_tipo, @demo_usuario, NULL, 15440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-029', 1, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 1, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 1, 6820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15440.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 12:13:55', @demo_tipo, @demo_usuario, NULL, 33860.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-036', 2, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 2, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 2, 7590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33860.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 19:30:06', @demo_tipo, @demo_usuario, NULL, 33780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-043', 3, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 3, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 3, 1200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33780.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 14:47:17', @demo_tipo, @demo_usuario, NULL, 13590.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-050', 1, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 1, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 1, 1980.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13590.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 09:04:28', @demo_tipo, @demo_usuario, NULL, 28520.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-057', 2, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 2, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 2, 2750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28520.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 16:21:39', @demo_tipo, @demo_usuario, NULL, 47250.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-064', 3, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 3, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 3, 2700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 47250.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 11:38:50', @demo_tipo, @demo_usuario, NULL, 17250.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-071', 1, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 1, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 1, 3470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17250.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 18:55:01', @demo_tipo, @demo_usuario, NULL, 23160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-078', 2, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 2, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 2, 3420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23160.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 13:12:12', @demo_tipo, @demo_usuario, NULL, 41700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-085', 3, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 3, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 3, 4190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41700.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 20:29:23', @demo_tipo, @demo_usuario, NULL, 14580.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-092', 1, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 1, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 1, 4970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14580.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 15:46:34', @demo_tipo, @demo_usuario, NULL, 32140.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-099', 2, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 2, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 2, 4920.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32140.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 10:03:45', @demo_tipo, @demo_usuario, NULL, 33690.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-006', 3, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 3, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 3, 5690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33690.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-16 17:20:56', @demo_tipo, @demo_usuario, NULL, 11900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-013', 1, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 1, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 1, 5640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11900.00, '2026-11-16');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 14:51:00', @demo_tipo, @demo_usuario, NULL, 47100.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-033', 3, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 3, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 3, 6910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 47100.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 09:08:11', @demo_tipo, @demo_usuario, NULL, 18030.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-040', 1, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 1, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 1, 7680.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 18030.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 16:25:22', @demo_tipo, @demo_usuario, NULL, 24720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-047', 2, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 2, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 2, 2120.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24720.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 11:42:33', @demo_tipo, @demo_usuario, NULL, 41550.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-054', 3, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 3, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 3, 2060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41550.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 18:59:44', @demo_tipo, @demo_usuario, NULL, 15350.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-061', 1, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 1, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 1, 2840.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15350.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 13:16:55', @demo_tipo, @demo_usuario, NULL, 32040.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-068', 2, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 2, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 2, 2790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32040.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 20:33:06', @demo_tipo, @demo_usuario, NULL, 36000.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-075', 3, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 3, 1890.00),
  (@demo_salida, 'DEMO-NOV26-033', 3, 3560.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36000.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 15:50:17', @demo_tipo, @demo_usuario, NULL, 12670.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-082', 1, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 1, 1830.00),
  (@demo_salida, 'DEMO-NOV26-040', 1, 4340.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12670.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 10:07:28', @demo_tipo, @demo_usuario, NULL, 28340.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-089', 2, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 2, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 2, 4280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28340.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 17:24:39', @demo_tipo, @demo_usuario, NULL, 46980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-096', 3, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 3, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 3, 5060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46980.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 12:41:50', @demo_tipo, @demo_usuario, NULL, 9990.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-003', 1, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 1, 3330.00),
  (@demo_salida, 'DEMO-NOV26-061', 1, 5000.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 9990.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 19:58:01', @demo_tipo, @demo_usuario, NULL, 24640.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-010', 2, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 2, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 2, 5780.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24640.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 14:15:12', @demo_tipo, @demo_usuario, NULL, 38940.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-017', 3, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 3, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 3, 6550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38940.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 09:32:23', @demo_tipo, @demo_usuario, NULL, 14490.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-024', 1, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 1, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 1, 6500.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14490.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 16:49:34', @demo_tipo, @demo_usuario, NULL, 31960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-031', 2, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 2, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 2, 7280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31960.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 11:06:45', @demo_tipo, @demo_usuario, NULL, 49950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-038', 3, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 3, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 3, 7220.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 49950.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 18:23:56', @demo_tipo, @demo_usuario, NULL, 12640.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-045', 1, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 1, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 1, 1660.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12640.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 13:40:07', @demo_tipo, @demo_usuario, NULL, 26600.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-052', 2, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 2, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 2, 2430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26600.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 20:57:18', @demo_tipo, @demo_usuario, NULL, 44400.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-059', 3, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 3, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 3, 2380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 44400.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 15:14:29', @demo_tipo, @demo_usuario, NULL, 16300.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-066', 1, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 1, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 1, 3160.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16300.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 10:31:40', @demo_tipo, @demo_usuario, NULL, 21260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-073', 2, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 2, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 2, 3100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 21260.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 17:48:51', @demo_tipo, @demo_usuario, NULL, 38850.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-080', 3, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 3, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 3, 3880.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38850.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 12:05:02', @demo_tipo, @demo_usuario, NULL, 13620.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-087', 1, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 1, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 1, 4650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13620.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-17 19:22:13', @demo_tipo, @demo_usuario, NULL, 30240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-094', 2, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 2, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 2, 4600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30240.00, '2026-11-17');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 15:54:00', @demo_tipo, @demo_usuario, NULL, 12580.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-014', 1, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 1, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 1, 5870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12580.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 10:11:11', @demo_tipo, @demo_usuario, NULL, 28160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-021', 2, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 2, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 2, 6640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28160.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 17:28:22', @demo_tipo, @demo_usuario, NULL, 44250.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-028', 3, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 3, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 3, 6590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 44250.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 12:45:33', @demo_tipo, @demo_usuario, NULL, 17070.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-035', 1, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 1, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 1, 7360.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17070.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 19:02:44', @demo_tipo, @demo_usuario, NULL, 35500.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-042', 2, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 2, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 2, 8140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35500.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 14:19:55', @demo_tipo, @demo_usuario, NULL, 38700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-049', 3, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 3, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 3, 1750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38700.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 09:36:06', @demo_tipo, @demo_usuario, NULL, 14400.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-056', 1, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 1, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 1, 2520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14400.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 16:53:17', @demo_tipo, @demo_usuario, NULL, 30140.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-063', 2, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 2, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 2, 2470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30140.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 11:10:28', @demo_tipo, @demo_usuario, NULL, 52170.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-070', 3, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 3, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 3, 3240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 52170.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-18 18:27:39', @demo_tipo, @demo_usuario, NULL, 11720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-077', 1, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 1, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 1, 4020.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11720.00, '2026-11-18');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 16:57:00', @demo_tipo, @demo_usuario, NULL, 46590.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-097', 3, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 3, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 3, 5290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46590.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 11:14:11', @demo_tipo, @demo_usuario, NULL, 10680.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-004', 1, 1890.00),
  (@demo_salida, 'DEMO-NOV26-033', 1, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 1, 5230.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10680.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 18:31:22', @demo_tipo, @demo_usuario, NULL, 24360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-011', 2, 1830.00),
  (@demo_salida, 'DEMO-NOV26-040', 2, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 2, 6010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24360.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 13:48:33', @demo_tipo, @demo_usuario, NULL, 38550.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-018', 3, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 3, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 3, 5960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38550.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 20:05:44', @demo_tipo, @demo_usuario, NULL, 15170.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-025', 1, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 1, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 1, 6730.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15170.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 15:22:55', @demo_tipo, @demo_usuario, NULL, 31680.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-032', 2, 3330.00),
  (@demo_salida, 'DEMO-NOV26-061', 2, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 2, 7510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31680.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 10:39:06', @demo_tipo, @demo_usuario, NULL, 52020.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-039', 3, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 3, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 3, 7450.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 52020.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 17:56:17', @demo_tipo, @demo_usuario, NULL, 12490.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-046', 1, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 1, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 1, 1890.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12490.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 12:13:28', @demo_tipo, @demo_usuario, NULL, 26320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-053', 2, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 2, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 2, 1830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26320.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 19:30:39', @demo_tipo, @demo_usuario, NULL, 46470.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-060', 3, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 3, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 3, 2610.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46470.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 14:47:50', @demo_tipo, @demo_usuario, NULL, 16150.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-067', 1, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 1, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 1, 3380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16150.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 09:04:01', @demo_tipo, @demo_usuario, NULL, 22640.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-074', 2, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 2, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 2, 3330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22640.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 16:21:12', @demo_tipo, @demo_usuario, NULL, 38430.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-081', 3, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 3, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 3, 4110.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38430.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 11:38:23', @demo_tipo, @demo_usuario, NULL, 13480.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-088', 1, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 1, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 1, 4050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13480.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 18:55:34', @demo_tipo, @demo_usuario, NULL, 31620.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-095', 2, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 2, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 2, 4830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31620.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 13:12:45', @demo_tipo, @demo_usuario, NULL, 30390.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-002', 3, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 3, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 3, 5600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30390.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-19 20:29:56', @demo_tipo, @demo_usuario, NULL, 11630.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-009', 1, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 1, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 1, 5550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11630.00, '2026-11-19');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 17:00:00', @demo_tipo, @demo_usuario, NULL, 46320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-029', 3, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 3, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 3, 6820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46320.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 12:17:11', @demo_tipo, @demo_usuario, NULL, 16930.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-036', 1, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 1, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 1, 7590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16930.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 19:34:22', @demo_tipo, @demo_usuario, NULL, 22520.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-043', 2, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 2, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 2, 1200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22520.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 14:51:33', @demo_tipo, @demo_usuario, NULL, 40770.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-050', 3, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 3, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 3, 1980.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 40770.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 09:08:44', @demo_tipo, @demo_usuario, NULL, 14260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-057', 1, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 1, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 1, 2750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14260.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 16:25:55', @demo_tipo, @demo_usuario, NULL, 31500.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-064', 2, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 2, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 2, 2700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31500.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 11:42:06', @demo_tipo, @demo_usuario, NULL, 51750.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-071', 3, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 3, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 3, 3470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 51750.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 18:59:17', @demo_tipo, @demo_usuario, NULL, 11580.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-078', 1, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 1, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 1, 3420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11580.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 13:16:28', @demo_tipo, @demo_usuario, NULL, 27800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-085', 2, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 2, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 2, 4190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27800.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 20:33:39', @demo_tipo, @demo_usuario, NULL, 43740.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-092', 3, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 3, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 3, 4970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 43740.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 15:50:50', @demo_tipo, @demo_usuario, NULL, 16070.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-099', 1, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 1, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 1, 4920.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16070.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 10:07:01', @demo_tipo, @demo_usuario, NULL, 22460.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-006', 2, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 2, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 2, 5690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22460.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 17:24:12', @demo_tipo, @demo_usuario, NULL, 35700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-013', 3, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 3, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 3, 5640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35700.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 12:41:23', @demo_tipo, @demo_usuario, NULL, 14220.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-020', 1, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 1, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 1, 6410.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14220.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 19:58:34', @demo_tipo, @demo_usuario, NULL, 29780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-027', 2, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 2, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 2, 7190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29780.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 14:15:45', @demo_tipo, @demo_usuario, NULL, 49170.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-034', 3, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 3, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 3, 7140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 49170.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 09:32:56', @demo_tipo, @demo_usuario, NULL, 17890.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-041', 1, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 1, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 1, 7910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17890.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 16:49:07', @demo_tipo, @demo_usuario, NULL, 24420.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-048', 2, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 2, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 2, 1520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24420.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 11:06:18', @demo_tipo, @demo_usuario, NULL, 43620.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-055', 3, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 3, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 3, 2290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 43620.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 18:23:29', @demo_tipo, @demo_usuario, NULL, 15210.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-062', 1, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 1, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 1, 3070.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15210.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 13:40:40', @demo_tipo, @demo_usuario, NULL, 33400.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-069', 2, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 2, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 2, 3010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33400.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 20:57:51', @demo_tipo, @demo_usuario, NULL, 35610.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-076', 3, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 3, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 3, 3790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35610.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 15:14:02', @demo_tipo, @demo_usuario, NULL, 12530.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-083', 1, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 1, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 1, 3740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12530.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-20 10:31:13', @demo_tipo, @demo_usuario, NULL, 29720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-090', 2, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 2, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 2, 4510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29720.00, '2026-11-20');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 18:03:00', @demo_tipo, @demo_usuario, NULL, 12320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-010', 1, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 1, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 1, 5780.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12320.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 13:20:11', @demo_tipo, @demo_usuario, NULL, 25960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-017', 2, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 2, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 2, 6550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25960.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 20:37:22', @demo_tipo, @demo_usuario, NULL, 43470.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-024', 3, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 3, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 3, 6500.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 43470.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 15:54:33', @demo_tipo, @demo_usuario, NULL, 15980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-031', 1, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 1, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 1, 7280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15980.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 10:11:44', @demo_tipo, @demo_usuario, NULL, 33300.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-038', 2, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 2, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 2, 7220.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33300.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 17:28:55', @demo_tipo, @demo_usuario, NULL, 37920.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-045', 3, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 3, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 3, 1660.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37920.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 12:45:06', @demo_tipo, @demo_usuario, NULL, 13300.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-052', 1, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 1, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 1, 2430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13300.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 19:02:17', @demo_tipo, @demo_usuario, NULL, 29600.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-059', 2, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 2, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 2, 2380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29600.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 14:19:28', @demo_tipo, @demo_usuario, NULL, 48900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-066', 3, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 3, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 3, 3160.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 48900.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-21 09:36:39', @demo_tipo, @demo_usuario, NULL, 10630.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-073', 1, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 1, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 1, 3100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10630.00, '2026-11-21');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 19:06:00', @demo_tipo, @demo_usuario, NULL, 43290.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-093', 3, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 3, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 3, 4370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 43290.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 14:23:11', @demo_tipo, @demo_usuario, NULL, 16760.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-100', 1, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 1, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 1, 5150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16760.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 09:40:22', @demo_tipo, @demo_usuario, NULL, 22180.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-007', 2, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 2, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 2, 5920.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22180.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 16:57:33', @demo_tipo, @demo_usuario, NULL, 37740.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-014', 3, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 3, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 3, 5870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37740.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 11:14:44', @demo_tipo, @demo_usuario, NULL, 14080.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-021', 1, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 1, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 1, 6640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14080.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 18:31:55', @demo_tipo, @demo_usuario, NULL, 29500.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-028', 2, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 2, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 2, 6590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29500.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 13:48:06', @demo_tipo, @demo_usuario, NULL, 51210.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-035', 3, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 3, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 3, 7360.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 51210.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 20:05:17', @demo_tipo, @demo_usuario, NULL, 17750.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-042', 1, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 1, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 1, 8140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17750.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 15:22:28', @demo_tipo, @demo_usuario, NULL, 25800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-049', 2, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 2, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 2, 1750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25800.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 10:39:39', @demo_tipo, @demo_usuario, NULL, 43200.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-056', 3, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 3, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 3, 2520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 43200.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 17:56:50', @demo_tipo, @demo_usuario, NULL, 15070.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-063', 1, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 1, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 1, 2470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15070.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 12:13:01', @demo_tipo, @demo_usuario, NULL, 34780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-070', 2, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 2, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 2, 3240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34780.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 19:30:12', @demo_tipo, @demo_usuario, NULL, 35160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-077', 3, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 3, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 3, 4020.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35160.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 14:47:23', @demo_tipo, @demo_usuario, NULL, 13220.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-084', 1, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 1, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 1, 3970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13220.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 09:04:34', @demo_tipo, @demo_usuario, NULL, 29440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-091', 2, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 2, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 2, 4740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29440.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 16:21:45', @demo_tipo, @demo_usuario, NULL, 46140.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-098', 3, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 3, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 3, 4690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 46140.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-22 11:38:56', @demo_tipo, @demo_usuario, NULL, 11370.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-005', 1, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 1, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 1, 5460.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11370.00, '2026-11-22');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 20:09:00', @demo_tipo, @demo_usuario, NULL, 45510.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-025', 3, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 3, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 3, 6730.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45510.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 15:26:11', @demo_tipo, @demo_usuario, NULL, 15840.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-032', 1, 3330.00),
  (@demo_salida, 'DEMO-NOV26-061', 1, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 1, 7510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15840.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 10:43:22', @demo_tipo, @demo_usuario, NULL, 34680.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-039', 2, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 2, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 2, 7450.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34680.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 17:00:33', @demo_tipo, @demo_usuario, NULL, 37470.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-046', 3, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 3, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 3, 1890.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37470.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 12:17:44', @demo_tipo, @demo_usuario, NULL, 13160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-053', 1, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 1, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 1, 1830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13160.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 19:34:55', @demo_tipo, @demo_usuario, NULL, 30980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-060', 2, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 2, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 2, 2610.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30980.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 14:51:06', @demo_tipo, @demo_usuario, NULL, 48450.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-067', 3, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 3, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 3, 3380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 48450.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 09:08:17', @demo_tipo, @demo_usuario, NULL, 11320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-074', 1, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 1, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 1, 3330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11320.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 16:25:28', @demo_tipo, @demo_usuario, NULL, 25620.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-081', 2, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 2, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 2, 4110.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25620.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 11:42:39', @demo_tipo, @demo_usuario, NULL, 40440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-088', 3, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 3, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 3, 4050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 40440.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 18:59:50', @demo_tipo, @demo_usuario, NULL, 15810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-095', 1, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 1, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 1, 4830.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15810.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 13:16:01', @demo_tipo, @demo_usuario, NULL, 20260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-002', 2, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 2, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 2, 5600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 20260.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 20:33:12', @demo_tipo, @demo_usuario, NULL, 34890.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-009', 3, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 3, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 3, 5550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34890.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 15:50:23', @demo_tipo, @demo_usuario, NULL, 13130.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-016', 1, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 1, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 1, 6330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13130.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 10:07:34', @demo_tipo, @demo_usuario, NULL, 27600.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-023', 2, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 2, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 2, 6270.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27600.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 17:24:45', @demo_tipo, @demo_usuario, NULL, 48360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-030', 3, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 3, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 3, 7050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 48360.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 12:41:56', @demo_tipo, @demo_usuario, NULL, 16790.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-037', 1, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 1, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 1, 7820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16790.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 19:58:07', @demo_tipo, @demo_usuario, NULL, 23900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-044', 2, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 2, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 2, 1430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23900.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 14:15:18', @demo_tipo, @demo_usuario, NULL, 40320.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-051', 3, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 3, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 3, 2200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 40320.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 09:32:29', @demo_tipo, @demo_usuario, NULL, 14120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-058', 1, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 1, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 1, 2150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14120.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 16:49:40', @demo_tipo, @demo_usuario, NULL, 32880.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-065', 2, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 2, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 2, 2930.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32880.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 11:06:51', @demo_tipo, @demo_usuario, NULL, 32310.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-072', 3, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 3, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 3, 3700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32310.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 18:23:02', @demo_tipo, @demo_usuario, NULL, 12270.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-079', 1, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 1, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 1, 3650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12270.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-23 13:40:13', @demo_tipo, @demo_usuario, NULL, 27520.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-086', 2, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 2, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 2, 4420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27520.00, '2026-11-23');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 09:12:00', @demo_tipo, @demo_usuario, NULL, 11230.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-006', 1, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 1, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 1, 5690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11230.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 16:29:11', @demo_tipo, @demo_usuario, NULL, 23800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-013', 2, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 2, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 2, 5640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23800.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 11:46:22', @demo_tipo, @demo_usuario, NULL, 42660.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-020', 3, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 3, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 3, 6410.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42660.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 18:03:33', @demo_tipo, @demo_usuario, NULL, 14890.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-027', 1, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 1, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 1, 7190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14890.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 13:20:44', @demo_tipo, @demo_usuario, NULL, 32780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-034', 2, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 2, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 2, 7140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32780.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 20:37:55', @demo_tipo, @demo_usuario, NULL, 53670.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-041', 3, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 3, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 3, 7910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 53670.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 15:54:06', @demo_tipo, @demo_usuario, NULL, 12210.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-048', 1, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 1, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 1, 1520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12210.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 10:11:17', @demo_tipo, @demo_usuario, NULL, 29080.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-055', 2, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 2, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 2, 2290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 29080.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 17:28:28', @demo_tipo, @demo_usuario, NULL, 45630.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-062', 3, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 3, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 3, 3070.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45630.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-24 12:45:39', @demo_tipo, @demo_usuario, NULL, 16700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-069', 1, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 1, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 1, 3010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16700.00, '2026-11-24');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 10:15:00', @demo_tipo, @demo_usuario, NULL, 42510.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-089', 3, 7280.00),
  (@demo_salida, 'DEMO-NOV26-018', 3, 2610.00),
  (@demo_salida, 'DEMO-NOV26-047', 3, 4280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42510.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 17:32:11', @demo_tipo, @demo_usuario, NULL, 15660.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-096', 1, 7220.00),
  (@demo_salida, 'DEMO-NOV26-025', 1, 3380.00),
  (@demo_salida, 'DEMO-NOV26-054', 1, 5060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15660.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 12:49:22', @demo_tipo, @demo_usuario, NULL, 19980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-003', 2, 1660.00),
  (@demo_salida, 'DEMO-NOV26-032', 2, 3330.00),
  (@demo_salida, 'DEMO-NOV26-061', 2, 5000.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 19980.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 19:06:33', @demo_tipo, @demo_usuario, NULL, 36960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-010', 3, 2430.00),
  (@demo_salida, 'DEMO-NOV26-039', 3, 4110.00),
  (@demo_salida, 'DEMO-NOV26-068', 3, 5780.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36960.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 14:23:44', @demo_tipo, @demo_usuario, NULL, 12980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-017', 1, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 1, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 1, 6550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12980.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 09:40:55', @demo_tipo, @demo_usuario, NULL, 28980.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-024', 2, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 2, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 2, 6500.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28980.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 16:57:06', @demo_tipo, @demo_usuario, NULL, 47940.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-031', 3, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 3, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 3, 7280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 47940.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 11:14:17', @demo_tipo, @demo_usuario, NULL, 16650.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-038', 1, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 1, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 1, 7220.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16650.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 18:31:28', @demo_tipo, @demo_usuario, NULL, 25280.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-045', 2, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 2, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 2, 1660.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25280.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 13:48:39', @demo_tipo, @demo_usuario, NULL, 39900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-052', 3, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 3, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 3, 2430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 39900.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 20:05:50', @demo_tipo, @demo_usuario, NULL, 14800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-059', 1, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 1, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 1, 2380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14800.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 15:22:01', @demo_tipo, @demo_usuario, NULL, 32600.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-066', 2, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 2, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 2, 3160.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32600.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 10:39:12', @demo_tipo, @demo_usuario, NULL, 31890.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-073', 3, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 3, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 3, 3100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31890.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 17:56:23', @demo_tipo, @demo_usuario, NULL, 12950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-080', 1, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 1, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 1, 3880.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12950.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 12:13:34', @demo_tipo, @demo_usuario, NULL, 27240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-087', 2, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 2, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 2, 4650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27240.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 19:30:45', @demo_tipo, @demo_usuario, NULL, 45360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-094', 3, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 3, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 3, 4600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45360.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-25 14:47:56', @demo_tipo, @demo_usuario, NULL, 10270.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-001', 1, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 1, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 1, 5370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10270.00, '2026-11-25');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 11:18:00', @demo_tipo, @demo_usuario, NULL, 42240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-021', 3, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 3, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 3, 6640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42240.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 18:35:11', @demo_tipo, @demo_usuario, NULL, 14750.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-028', 1, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 1, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 1, 6590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14750.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 13:52:22', @demo_tipo, @demo_usuario, NULL, 34140.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-035', 2, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 2, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 2, 7360.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34140.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 20:09:33', @demo_tipo, @demo_usuario, NULL, 53250.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-042', 3, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 3, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 3, 8140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 53250.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 15:26:44', @demo_tipo, @demo_usuario, NULL, 12900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-049', 1, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 1, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 1, 1750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12900.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 10:43:55', @demo_tipo, @demo_usuario, NULL, 28800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-056', 2, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 2, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 2, 2520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28800.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 17:00:06', @demo_tipo, @demo_usuario, NULL, 45210.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-063', 3, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 3, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 3, 2470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45210.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 12:17:17', @demo_tipo, @demo_usuario, NULL, 17390.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-070', 1, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 1, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 1, 3240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17390.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 19:34:28', @demo_tipo, @demo_usuario, NULL, 23440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-077', 2, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 2, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 2, 4020.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23440.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 14:51:39', @demo_tipo, @demo_usuario, NULL, 39660.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-084', 3, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 3, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 3, 3970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 39660.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 09:08:50', @demo_tipo, @demo_usuario, NULL, 14720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-091', 1, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 1, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 1, 4740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14720.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 16:25:01', @demo_tipo, @demo_usuario, NULL, 30760.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-098', 2, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 2, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 2, 4690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30760.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 11:42:12', @demo_tipo, @demo_usuario, NULL, 34110.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-005', 3, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 3, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 3, 5460.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 34110.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 18:59:23', @demo_tipo, @demo_usuario, NULL, 12040.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-012', 1, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 1, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 1, 6240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12040.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 13:16:34', @demo_tipo, @demo_usuario, NULL, 27060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-019', 2, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 2, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 2, 6180.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 27060.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 20:33:45', @demo_tipo, @demo_usuario, NULL, 45120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-026', 3, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 3, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 3, 6960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 45120.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 15:50:56', @demo_tipo, @demo_usuario, NULL, 15700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-033', 1, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 1, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 1, 6910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15700.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 10:07:07', @demo_tipo, @demo_usuario, NULL, 36060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-040', 2, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 2, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 2, 7680.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36060.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 17:24:18', @demo_tipo, @demo_usuario, NULL, 37080.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-047', 3, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 3, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 3, 2120.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 37080.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 12:41:29', @demo_tipo, @demo_usuario, NULL, 13850.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-054', 1, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 1, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 1, 2060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13850.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 19:58:40', @demo_tipo, @demo_usuario, NULL, 30700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-061', 2, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 2, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 2, 2840.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30700.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 14:15:51', @demo_tipo, @demo_usuario, NULL, 48060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-068', 3, 5780.00),
  (@demo_salida, 'DEMO-NOV26-097', 3, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 3, 2790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 48060.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 09:32:02', @demo_tipo, @demo_usuario, NULL, 12000.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-075', 1, 6550.00),
  (@demo_salida, 'DEMO-NOV26-004', 1, 1890.00),
  (@demo_salida, 'DEMO-NOV26-033', 1, 3560.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12000.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-26 16:49:13', @demo_tipo, @demo_usuario, NULL, 25340.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-082', 2, 6500.00),
  (@demo_salida, 'DEMO-NOV26-011', 2, 1830.00),
  (@demo_salida, 'DEMO-NOV26-040', 2, 4340.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25340.00, '2026-11-26');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 12:21:00', @demo_tipo, @demo_usuario, NULL, 10130.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-002', 1, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 1, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 1, 5600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10130.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 19:38:11', @demo_tipo, @demo_usuario, NULL, 23260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-009', 2, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 2, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 2, 5550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23260.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 14:55:22', @demo_tipo, @demo_usuario, NULL, 39390.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-016', 3, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 3, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 3, 6330.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 39390.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 09:12:33', @demo_tipo, @demo_usuario, NULL, 13800.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-023', 1, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 1, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 1, 6270.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13800.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 16:29:44', @demo_tipo, @demo_usuario, NULL, 32240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-030', 2, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 2, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 2, 7050.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32240.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 11:46:55', @demo_tipo, @demo_usuario, NULL, 50370.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-037', 3, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 3, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 3, 7820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 50370.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 18:03:06', @demo_tipo, @demo_usuario, NULL, 11950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-044', 1, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 1, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 1, 1430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11950.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 13:20:17', @demo_tipo, @demo_usuario, NULL, 26880.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-051', 2, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 2, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 2, 2200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26880.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 20:37:28', @demo_tipo, @demo_usuario, NULL, 42360.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-058', 3, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 3, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 3, 2150.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 42360.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-27 15:54:39', @demo_tipo, @demo_usuario, NULL, 16440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-065', 1, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 1, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 1, 2930.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16440.00, '2026-11-27');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 13:24:00', @demo_tipo, @demo_usuario, NULL, 41700.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-085', 3, 7190.00),
  (@demo_salida, 'DEMO-NOV26-014', 3, 2520.00),
  (@demo_salida, 'DEMO-NOV26-043', 3, 4190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41700.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 20:41:11', @demo_tipo, @demo_usuario, NULL, 14580.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-092', 1, 7140.00),
  (@demo_salida, 'DEMO-NOV26-021', 1, 2470.00),
  (@demo_salida, 'DEMO-NOV26-050', 1, 4970.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14580.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 15:58:22', @demo_tipo, @demo_usuario, NULL, 32140.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-099', 2, 7910.00),
  (@demo_salida, 'DEMO-NOV26-028', 2, 3240.00),
  (@demo_salida, 'DEMO-NOV26-057', 2, 4920.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 32140.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 10:15:33', @demo_tipo, @demo_usuario, NULL, 33690.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-006', 3, 1520.00),
  (@demo_salida, 'DEMO-NOV26-035', 3, 4020.00),
  (@demo_salida, 'DEMO-NOV26-064', 3, 5690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33690.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 17:32:44', @demo_tipo, @demo_usuario, NULL, 11900.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-013', 1, 2290.00),
  (@demo_salida, 'DEMO-NOV26-042', 1, 3970.00),
  (@demo_salida, 'DEMO-NOV26-071', 1, 5640.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11900.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 12:49:55', @demo_tipo, @demo_usuario, NULL, 28440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-020', 2, 3070.00),
  (@demo_salida, 'DEMO-NOV26-049', 2, 4740.00),
  (@demo_salida, 'DEMO-NOV26-078', 2, 6410.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28440.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 19:06:06', @demo_tipo, @demo_usuario, NULL, 44670.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-027', 3, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 3, 4690.00),
  (@demo_salida, 'DEMO-NOV26-085', 3, 7190.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 44670.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 14:23:17', @demo_tipo, @demo_usuario, NULL, 16390.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-034', 1, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 1, 5460.00),
  (@demo_salida, 'DEMO-NOV26-092', 1, 7140.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16390.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 09:40:28', @demo_tipo, @demo_usuario, NULL, 35780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-041', 2, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 2, 6240.00),
  (@demo_salida, 'DEMO-NOV26-099', 2, 7910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 35780.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 16:57:39', @demo_tipo, @demo_usuario, NULL, 36630.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-048', 3, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 3, 6180.00),
  (@demo_salida, 'DEMO-NOV26-006', 3, 1520.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36630.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 11:14:50', @demo_tipo, @demo_usuario, NULL, 14540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-055', 1, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 1, 6960.00),
  (@demo_salida, 'DEMO-NOV26-013', 1, 2290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14540.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 18:31:01', @demo_tipo, @demo_usuario, NULL, 30420.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-062', 2, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 2, 6910.00),
  (@demo_salida, 'DEMO-NOV26-020', 2, 3070.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30420.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 13:48:12', @demo_tipo, @demo_usuario, NULL, 50100.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-069', 3, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 3, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 3, 3010.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 50100.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 20:05:23', @demo_tipo, @demo_usuario, NULL, 11870.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-076', 1, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 1, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 1, 3790.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 11870.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 15:22:34', @demo_tipo, @demo_usuario, NULL, 25060.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-083', 2, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 2, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 2, 3740.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 25060.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 10:39:45', @demo_tipo, @demo_usuario, NULL, 44580.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-090', 3, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 3, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 3, 4510.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 44580.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-28 17:56:56', @demo_tipo, @demo_usuario, NULL, 15530.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-097', 1, 7450.00),
  (@demo_salida, 'DEMO-NOV26-026', 1, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 1, 5290.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15530.00, '2026-11-28');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 14:27:00', @demo_tipo, @demo_usuario, NULL, 38940.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-017', 3, 2380.00),
  (@demo_salida, 'DEMO-NOV26-046', 3, 4050.00),
  (@demo_salida, 'DEMO-NOV26-075', 3, 6550.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38940.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 09:44:11', @demo_tipo, @demo_usuario, NULL, 14490.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-024', 1, 3160.00),
  (@demo_salida, 'DEMO-NOV26-053', 1, 4830.00),
  (@demo_salida, 'DEMO-NOV26-082', 1, 6500.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 14490.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 16:01:22', @demo_tipo, @demo_usuario, NULL, 31960.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-031', 2, 3100.00),
  (@demo_salida, 'DEMO-NOV26-060', 2, 5600.00),
  (@demo_salida, 'DEMO-NOV26-089', 2, 7280.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 31960.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 11:18:33', @demo_tipo, @demo_usuario, NULL, 49950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-038', 3, 3880.00),
  (@demo_salida, 'DEMO-NOV26-067', 3, 5550.00),
  (@demo_salida, 'DEMO-NOV26-096', 3, 7220.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 49950.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 18:35:44', @demo_tipo, @demo_usuario, NULL, 12640.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-045', 1, 4650.00),
  (@demo_salida, 'DEMO-NOV26-074', 1, 6330.00),
  (@demo_salida, 'DEMO-NOV26-003', 1, 1660.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 12640.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 13:52:55', @demo_tipo, @demo_usuario, NULL, 26600.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-052', 2, 4600.00),
  (@demo_salida, 'DEMO-NOV26-081', 2, 6270.00),
  (@demo_salida, 'DEMO-NOV26-010', 2, 2430.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26600.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 20:09:06', @demo_tipo, @demo_usuario, NULL, 44400.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-059', 3, 5370.00),
  (@demo_salida, 'DEMO-NOV26-088', 3, 7050.00),
  (@demo_salida, 'DEMO-NOV26-017', 3, 2380.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 44400.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 15:26:17', @demo_tipo, @demo_usuario, NULL, 16300.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-066', 1, 5320.00),
  (@demo_salida, 'DEMO-NOV26-095', 1, 7820.00),
  (@demo_salida, 'DEMO-NOV26-024', 1, 3160.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 16300.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 10:43:28', @demo_tipo, @demo_usuario, NULL, 21260.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-073', 2, 6100.00),
  (@demo_salida, 'DEMO-NOV26-002', 2, 1430.00),
  (@demo_salida, 'DEMO-NOV26-031', 2, 3100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 21260.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 17:00:39', @demo_tipo, @demo_usuario, NULL, 38850.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-080', 3, 6870.00),
  (@demo_salida, 'DEMO-NOV26-009', 3, 2200.00),
  (@demo_salida, 'DEMO-NOV26-038', 3, 3880.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 38850.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 12:17:50', @demo_tipo, @demo_usuario, NULL, 13620.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-087', 1, 6820.00),
  (@demo_salida, 'DEMO-NOV26-016', 1, 2150.00),
  (@demo_salida, 'DEMO-NOV26-045', 1, 4650.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13620.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 19:34:01', @demo_tipo, @demo_usuario, NULL, 30240.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-094', 2, 7590.00),
  (@demo_salida, 'DEMO-NOV26-023', 2, 2930.00),
  (@demo_salida, 'DEMO-NOV26-052', 2, 4600.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30240.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 14:51:12', @demo_tipo, @demo_usuario, NULL, 30810.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-001', 3, 1200.00),
  (@demo_salida, 'DEMO-NOV26-030', 3, 3700.00),
  (@demo_salida, 'DEMO-NOV26-059', 3, 5370.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30810.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 09:08:23', @demo_tipo, @demo_usuario, NULL, 10950.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-008', 1, 1980.00),
  (@demo_salida, 'DEMO-NOV26-037', 1, 3650.00),
  (@demo_salida, 'DEMO-NOV26-066', 1, 5320.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 10950.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 16:25:34', @demo_tipo, @demo_usuario, NULL, 26540.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-015', 2, 2750.00),
  (@demo_salida, 'DEMO-NOV26-044', 2, 4420.00),
  (@demo_salida, 'DEMO-NOV26-073', 2, 6100.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 26540.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 11:42:45', @demo_tipo, @demo_usuario, NULL, 41820.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-022', 3, 2700.00),
  (@demo_salida, 'DEMO-NOV26-051', 3, 4370.00),
  (@demo_salida, 'DEMO-NOV26-080', 3, 6870.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41820.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 18:59:56', @demo_tipo, @demo_usuario, NULL, 15440.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-029', 1, 3470.00),
  (@demo_salida, 'DEMO-NOV26-058', 1, 5150.00),
  (@demo_salida, 'DEMO-NOV26-087', 1, 6820.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15440.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 13:16:07', @demo_tipo, @demo_usuario, NULL, 33860.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-036', 2, 3420.00),
  (@demo_salida, 'DEMO-NOV26-065', 2, 5920.00),
  (@demo_salida, 'DEMO-NOV26-094', 2, 7590.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33860.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 20:33:18', @demo_tipo, @demo_usuario, NULL, 33780.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-043', 3, 4190.00),
  (@demo_salida, 'DEMO-NOV26-072', 3, 5870.00),
  (@demo_salida, 'DEMO-NOV26-001', 3, 1200.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 33780.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 15:50:29', @demo_tipo, @demo_usuario, NULL, 13590.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-050', 1, 4970.00),
  (@demo_salida, 'DEMO-NOV26-079', 1, 6640.00),
  (@demo_salida, 'DEMO-NOV26-008', 1, 1980.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13590.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 10:07:40', @demo_tipo, @demo_usuario, NULL, 28520.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-057', 2, 4920.00),
  (@demo_salida, 'DEMO-NOV26-086', 2, 6590.00),
  (@demo_salida, 'DEMO-NOV26-015', 2, 2750.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 28520.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 17:24:51', @demo_tipo, @demo_usuario, NULL, 47250.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-064', 3, 5690.00),
  (@demo_salida, 'DEMO-NOV26-093', 3, 7360.00),
  (@demo_salida, 'DEMO-NOV26-022', 3, 2700.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 47250.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 12:41:02', @demo_tipo, @demo_usuario, NULL, 17250.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-071', 1, 5640.00),
  (@demo_salida, 'DEMO-NOV26-100', 1, 8140.00),
  (@demo_salida, 'DEMO-NOV26-029', 1, 3470.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 17250.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-29 19:58:13', @demo_tipo, @demo_usuario, NULL, 23160.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-078', 2, 6410.00),
  (@demo_salida, 'DEMO-NOV26-007', 2, 1750.00),
  (@demo_salida, 'DEMO-NOV26-036', 2, 3420.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 23160.00, '2026-11-29');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 15:30:00', @demo_tipo, @demo_usuario, NULL, 15380.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-098', 1, 7680.00),
  (@demo_salida, 'DEMO-NOV26-027', 1, 3010.00),
  (@demo_salida, 'DEMO-NOV26-056', 1, 4690.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15380.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 10:47:11', @demo_tipo, @demo_usuario, NULL, 22740.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-005', 2, 2120.00),
  (@demo_salida, 'DEMO-NOV26-034', 2, 3790.00),
  (@demo_salida, 'DEMO-NOV26-063', 2, 5460.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 22740.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 17:04:22', @demo_tipo, @demo_usuario, NULL, 36120.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-012', 3, 2060.00),
  (@demo_salida, 'DEMO-NOV26-041', 3, 3740.00),
  (@demo_salida, 'DEMO-NOV26-070', 3, 6240.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 36120.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 12:21:33', @demo_tipo, @demo_usuario, NULL, 13530.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-019', 1, 2840.00),
  (@demo_salida, 'DEMO-NOV26-048', 1, 4510.00),
  (@demo_salida, 'DEMO-NOV26-077', 1, 6180.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 13530.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 19:38:44', @demo_tipo, @demo_usuario, NULL, 30080.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-026', 2, 2790.00),
  (@demo_salida, 'DEMO-NOV26-055', 2, 5290.00),
  (@demo_salida, 'DEMO-NOV26-084', 2, 6960.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 30080.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 14:55:55', @demo_tipo, @demo_usuario, NULL, 47100.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-033', 3, 3560.00),
  (@demo_salida, 'DEMO-NOV26-062', 3, 5230.00),
  (@demo_salida, 'DEMO-NOV26-091', 3, 6910.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 47100.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 09:12:06', @demo_tipo, @demo_usuario, NULL, 18030.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-040', 1, 4340.00),
  (@demo_salida, 'DEMO-NOV26-069', 1, 6010.00),
  (@demo_salida, 'DEMO-NOV26-098', 1, 7680.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 18030.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 16:29:17', @demo_tipo, @demo_usuario, NULL, 24720.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-047', 2, 4280.00),
  (@demo_salida, 'DEMO-NOV26-076', 2, 5960.00),
  (@demo_salida, 'DEMO-NOV26-005', 2, 2120.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 24720.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 11:46:28', @demo_tipo, @demo_usuario, NULL, 41550.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-054', 3, 5060.00),
  (@demo_salida, 'DEMO-NOV26-083', 3, 6730.00),
  (@demo_salida, 'DEMO-NOV26-012', 3, 2060.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 41550.00, '2026-11-30');

  INSERT INTO salida (fecha, idTipo, idUsuario, rutCliente, totalSalida) VALUES ('2026-11-30 18:03:39', @demo_tipo, @demo_usuario, NULL, 15350.00);
  SET @demo_salida = LAST_INSERT_ID();
  INSERT INTO detalle_salida (idSalida, codigoProducto, cantidad, precioCobrado) VALUES
  (@demo_salida, 'DEMO-NOV26-061', 1, 5000.00),
  (@demo_salida, 'DEMO-NOV26-090', 1, 7510.00),
  (@demo_salida, 'DEMO-NOV26-019', 1, 2840.00);
  INSERT INTO pago_salida (idSalida, idMetodo, montoPagado, fechaPago) VALUES (@demo_salida, @demo_metodo, 15350.00, '2026-11-30');

  COMMIT;

  -- Control: total esperado de tickets en este lote
  SELECT COUNT(*) - @demo_ventas_antes AS ventas_insertadas FROM salida WHERE fecha >= '2026-11-01' AND fecha < '2026-12-01' AND idTipo=@demo_tipo;
