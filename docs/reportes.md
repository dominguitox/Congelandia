# Reportes provisionales

Disponible en `/reportes` para administradores autenticados, de acuerdo con el control de roles actual. La exportación aplica el mismo permiso. Esta versión solo consulta datos; no modifica inventario, ventas, precios ni promociones.

## Vistas

- **Resumen de ventas:** monto vendido, transacciones, unidades, ticket promedio, evolución diaria, categorías y productos más vendidos.
- **Inventario y riesgo:** stock actual, reposición sugerida, vencimientos y valor en riesgo estimado.
- **Hábitos de compra:** transacciones por día y hora, hora punta y comparación de venta media diaria por día de la semana.

Las vistas de ventas y hábitos comparten un período inclusivo de hasta 366 días (30 días por defecto). El inventario siempre refleja los movimientos registrados hasta el momento de la consulta. Cada vista permite descargar CSV UTF-8 con separador `;`.

## Criterios de cálculo

- Se consideran ventas las salidas cuyo tipo se llama `Venta`, sin distinguir mayúsculas ni espacios externos. Se excluyen cabeceras y detalles eliminados. Una transacción se cuenta una vez aunque tenga varios detalles.
- El monto vendido usa `cantidad × precioCobrado`, conservando descuentos históricos. Incluye fiado: no equivale a dinero recaudado ni a utilidad.
- Los productos eliminados conservan su participación en ventas históricas. Los productos sin categoría se agrupan en «Sin categoría».
- El stock suma ingresos y resta todas las salidas, incluidas mermas, excluyendo movimientos eliminados o futuros. Solo se muestran productos activos.
- Umbrales provisionales: bajo stock `< 10`, crítico `<= 3`, reposición hasta `20` unidades. Un stock negativo se señala para revisión.
- **Vencimientos estimados:** el esquema no enlaza las salidas con lotes. Se descuentan las salidas del producto empezando por el ingreso que vence primero (FEFO). Los remanentes son una aproximación para revisar físicamente, no trazabilidad real de lotes. Se incluyen vencidos y vencimientos hasta 15 días.
- El valor en riesgo multiplica los remanentes estimados por el precio de venta vigente sin descuentos. Los lotes sin precio se muestran, pero se excluyen del valor; no es una estimación de ganancia.
- El mapa muestra totales del período en las 24 horas usando la hora registrada en la app. La comparación semanal divide el monto por las ocurrencias de cada día, incluidos días sin ventas. En empates se muestra el primer día (lunes a domingo) o la primera hora.

## Implementación y validación

El [controlador](../app/Http/Controllers/ReporteController.php) valida filtros y entrega HTML o CSV. El [servicio de reportes](../app/Services/Reportes/ReporteService.php) concentra consultas y cálculos. Las [vistas](../resources/views/reportes/) reutilizan componentes y tokens globales, con [CSS limitado a reportes](../resources/css/reportes.css).

No requiere migraciones ni dependencias nuevas. Compilar los assets con `npm run build` antes de desplegar. Se conserva la ruta existente y su restricción `role:Administrador`; no se alteran los permisos de otros módulos. Las pruebas usan los nombres canónicos de tablas en minúsculas para ser compatibles con MySQL Linux.

Pruebas: `php artisan test tests/Feature/ReportesTest.php` ([casos](../tests/Feature/ReportesTest.php)). Usan SQLite en memoria: autenticación, cálculos, filtros, eliminaciones, estimación FEFO, estados vacíos y CSV con protección de fórmulas.

## Actualización del 8 de noviembre de 2026

La rama `feat/reportes-provisorios` se actualizó sobre `d241389` de `fix/integracion-main`, conservando las correcciones de roles, consultas compartidas, clientes y CI. Los cambios de reportes permanecen sin commit para revisión.

- Suite completa con PHP 8.4.23 y SQLite en memoria: **72 pruebas y 359 aserciones correctas**, incluidos los casos de reportes y la denegación de acceso/CSV al cajero.
- Compilación de producción correcta; persiste la advertencia previa de `fontaine` opcional.
- No se ejecutaron migraciones ni el SQL de prueba contra la base de desarrollo durante esta actualización.
- Esta ejecución local no valida MySQL Linux; el workflow existente contempla ese motor al subir los cambios.

El [SQL de prueba](../database/sql/datos_prueba_reportes.sql) conserva los 25 ingresos y 75 ventas solicitados, con detalles y pagos. Se recuperó del respaldo local y no se ejecutó. Utiliza los códigos `PROD-001` a `PROD-005`; revisar que existan antes de importarlo. Cada ejecución agrega nuevos movimientos.
