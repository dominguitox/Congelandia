# Reportes

Disponible en `/reportes` para administradores autenticados, de acuerdo con el control de roles actual. La exportación aplica el mismo permiso. Esta versión solo consulta datos; no modifica inventario, ventas, precios ni promociones.

## Vistas

- **Resumen de ventas:** monto vendido, transacciones, unidades, ticket promedio, evolución diaria, categorías y productos más vendidos.
- **Inventario y riesgo:** stock actual, reposición sugerida, vencimientos y valor en riesgo estimado.
- **Hábitos de compra:** transacciones por día y hora, hora punta y comparación de venta media diaria por día de la semana.
- **Calendario:** mes o semana desde el lunes, ofertas, feriados nacionales y estimaciones de agotamiento; selección de fecha con ventas y rankings.
- **Fechas de mayor venta:** fechas exactas de un año y patrones de día/mes en todo el historial, con desglose anual y rankings ponderados.

Las vistas de ventas y hábitos comparten un período inclusivo de hasta 366 días (30 días por defecto). El inventario siempre refleja los movimientos registrados hasta el momento de la consulta. Cada vista permite descargar CSV UTF-8 con separador `;`.

## Criterios de cálculo

- Se consideran ventas las salidas cuyo tipo se llama `Venta`, sin distinguir mayúsculas ni espacios externos. Se excluyen cabeceras y detalles eliminados. Una transacción se cuenta una vez aunque tenga varios detalles.
- El monto vendido usa `cantidad × precioCobrado`, conservando descuentos históricos. Incluye fiado: no equivale a dinero recaudado ni a utilidad.
- Los productos eliminados conservan su participación en ventas históricas. Los productos sin categoría se agrupan en «Sin categoría».
- El stock suma ingresos y resta todas las salidas, incluidas mermas, excluyendo movimientos eliminados o futuros. Solo se muestran productos activos.
- Umbrales provisionales del reporte anterior **Inventario y riesgo**: bajo stock `< 10`, crítico `<= 3`, reposición hasta `20` unidades. Un stock negativo se señala para revisión. El nuevo calendario no usa esos umbrales ni sugiere cantidades a comprar.
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

## Calendario y análisis histórico (11 de noviembre de 2026)

### Esquema y semántica verificados

Antes de implementar se consultaron las columnas de la base MySQL configurada, además de las migraciones, modelos, controladores, servicios, rutas y vistas. Nombres canónicos confirmados:

| Tabla | Columnas utilizadas |
| --- | --- |
| `producto` | `codigo`, `nombre`, `idCategoria`, `deleted_at` |
| `categoria` | `idCategoria`, `nombre`, `deleted_at` |
| `promocion` | `idPromocion`, `codigoProducto`, `porcentajeDescuento`, `fechaInicio`, `fechaFin`, `activa`, `deleted_at` |
| `salida` | `idSalida`, `fecha`, `idTipo`, `deleted_at` |
| `tipo_salida` | `idTipo`, `nombre` |
| `detalle_salida` | `idSalida`, `codigoProducto`, `cantidad`, `precioCobrado`, `deleted_at` |
| `ingreso` | `idIngreso`, `fecha`, `idProveedor` (nullable), `deleted_at` |
| `detalle_ingreso` | `idIngreso`, `codigoProducto`, `cantidad`, `precioCompra`, `fechaVencimiento`, `deleted_at` |
| `proveedor` | `idProveedor`, `nombre`, `deleted_at` |

No existe columna de stock mínimo, reserva, estado de cancelación de venta ni enlace de `detalle_salida` a un lote/`detalle_ingreso`. La exclusión de ventas sigue exactamente el servicio previo: tipo `Venta` y cabecera/detalle sin `deleted_at`. El catálogo de tipos puede conservarse en los datos históricos. Se suma `cantidad × precioCobrado`; no se recalculan descuentos, impuestos, precios actuales, deuda ni pagos. El POS ya guarda el descuento aplicado en `precioCobrado`.

El stock del calendario usa el criterio temporal del reporte de inventario: ingresos menos **todas** las salidas válidas hasta el instante actual, incluidas mermas. El esquema contiene `devolucion`, pero el cálculo de stock existente no la incorpora y no hay flujo operativo de devolución en las rutas revisadas. No se le atribuye un ajuste de inventario nuevo a esa tabla. Los pendientes offline tampoco son stock confirmado del servidor.

### Navegación y selección

Se mantiene `/reportes` y `role:Administrador`. No se agregan rutas ni permisos. Parámetros nuevos validados:

- `seccion=calendario|fechas`, `vista=mes|semana`, `fecha_calendario=YYYY-MM-DD`.
- `dia=YYYY-MM-DD`, o `patron=MM-DD` (incluye 29 de febrero).
- `anio`, y páginas independientes `pagina_fechas`, `pagina_patrones`, `pagina_detalle`.

La navegación permite fechas de 1900 a 2100, aunque la cobertura de feriados es menor y explícita. La vista inicial es mensual, usa lunes a domingo y completa semanas con días adyacentes. Navegar meses/semanas conserva la selección. Elegir un día mueve el calendario a ese día y abre el detalle; elegir una ocurrencia recurrente permite consultar su año por separado. Los totales del rango normal permanecen arriba. «Aplicar esta fecha al filtro» abre el resumen de ventas para ese día; «Limpiar selección» conserva el rango normal. CSV en estas pestañas está rotulado como ventas **del rango**, no como exportación de eventos o del patrón seleccionado.

Fechas y horas usan `app.locale` y `app.timezone` (por defecto `es` y `America/Santiago`), igual que los registros locales de la aplicación. La selección y navegación funcionan con enlaces/formularios sin JavaScript. Tablas con encabezados, foco visible, controles nativos `details/summary` y estados escritos permiten usar teclado y entender eventos sin depender del color. La cuadrícula se desplaza horizontalmente en pantallas pequeñas; rankings y contexto tienen áreas de desplazamiento acotadas.

### Ofertas

Se consulta intersección inclusiva entre inicio/término y todo el período visible, incluidos adyacentes. No se usa `Promocion::vigentes()` porque excluiría ofertas históricas y programadas. Se muestra cada día ocupado, con producto, código, descuento y ambas fechas/horas.

- `activa=true`: programada, vigente o vencida según las fechas actuales.
- `activa=false`: **Pausada (no válida)**, siguiendo `PromocionController::toggle`; no se confunde con vencimiento natural.
- `deleted_at` en la promoción: se excluye por completo.
- Producto retirado: evento informativo **Producto retirado (no válida)**.

El modelo no conserva intervalos históricos de pausa/reactivación ni versiones de promociones editadas. Se informa el estado actualmente registrado; no se afirma reconstruir descuentos que estuvieron vigentes en cada instante pasado. Los precios históricos cobrados provienen siempre de las ventas.

### Feriados sin red y ampliación de años

El [servicio de feriados](../app/Services/Reportes/FeriadosChile.php) calcula feriados fijos, Pascua gregoriana (Viernes y Sábado Santo), traslados de junio/octubre y feriados condicionales de enero/septiembre. El [catálogo local](../config/feriados.php) habilita **2025–2027** mediante las fechas verificadas del solsticio e incluye los extraordinarios nacionales electorales de 2025. Una fecha puede tener dos conmemoraciones (29 de junio de 2025). No se incluyen feriados regionales, bancarios, domingos ordinarios ni fechas comerciales.

Fuentes para revisar las reglas y datos:

- [Calendario nacional 2025, Gobierno de Chile](https://www.gob.cl/noticias/calendario-feriados-2025-festivos-irrenunciables-legales/).
- [Calendario nacional 2026, Gobierno de Chile](https://www.gob.cl/noticias/feriados-2026-revisa-cuantos-habra-y-cuales-son-irrenunciables/).
- [Calendarios 2026 y 2027, Conferencia Episcopal de Chile](https://www.conferenciaepiscopal.cl/tiempos_liturgicos.php).
- [Ley 19.668: traslados del 29 de junio y 12 de octubre](https://www.bcn.cl/leychile/navegar?idNorma=160270).
- [Ley 20.299: traslados del feriado evangélico](https://www.bcn.cl/leychile/navegar?idLey=20299).
- [Feriados adicionales de septiembre, Dirección del Trabajo](https://dt.gob.cl/portal/1628/w3-article-95016.html) y [Ley 20.983: 17 de septiembre/2 de enero condicionales](https://www.bcn.cl/leychile/navegar?i=1098384).

Para ampliar cobertura: verificar primero legislación aplicable al año y fecha local del solsticio, añadir ese año a `solsticios`, registrar los feriados nacionales extraordinarios (elecciones u otras leyes) en `extraordinarios`, y agregar pruebas de fechas y cantidad de eventos del año. Ajustar reglas si cambió su vigencia histórica; añadir un solsticio por sí solo no certifica feriados de años antiguos o futuros. No importar feriados regionales de calendarios consulares. Actualizar el intervalo mostrado en la interfaz/documentación. Si se usa caché de configuración, renovarla por el procedimiento habitual.

Fuera de cobertura aparece un aviso; nunca se presenta un catálogo vacío como afirmación de que no existen feriados. El calendario y las ventas siguen disponibles. No hay llamadas de red en ejecución, nuevas tablas, migraciones ni seeders.

### Estimación de agotamiento y contexto de recepciones

El [pronóstico](../app/Services/Reportes/PronosticoStockService.php) es informativo y se calcula desde **hoy**, aunque se navegue otro mes. No escribe movimientos, pedidos, borradores o solicitudes, no contacta proveedores y no recomienda cantidades a comprar.

1. Observa hasta 56 días completos anteriores a hoy, incluyendo días con cero venta. Si el primer ingreso es más reciente, comienza allí. Exige ingreso registrado, al menos 14 días de observación, 5 unidades y 3 días con ventas; si falta evidencia muestra «Datos insuficientes para estimar», sin fecha.
2. Ritmo base = unidades vendidas / días observados. Usa el mismo criterio de ventas y exposición a ceros de Hábitos de compra, pero agrega por producto en SQL; el mapa horario previo no ofrece estacionalidad anual reutilizable.
3. Busca hasta tres años anteriores: 56 días antes y 28 días desde la fecha equivalente (ajuste sin desbordamiento para 29 de febrero). Un año comparable requiere historia de ingresos desde el inicio de la ventana y al menos 3 días de ventas en **cada** tramo. Con dos años o más, factor = `(unidades de tramos posteriores / 28) / (unidades de tramos anteriores / 56)`. Multiplica el ritmo reciente por ese factor, ponderando las cantidades agregadas entre años. Sin esa evidencia conserva solo el ritmo reciente y lo dice.
4. Días restantes = redondeo hacia arriba de stock / ritmo. Stock cero o negativo produce un aviso urgente hoy; no se inventa una fecha pasada de agotamiento. Próximo significa hasta 7 días; previsión, de 8 a 90 días. Más allá de 90 días se informa la limitación sin publicar una fecha de precisión engañosa. El ajuste estacional es una aproximación del horizonte cercano, no un modelo diario de demanda.
5. Agrupa recepciones de los últimos 365 días por producto, proveedor y fecha: cantidades recibidas, número de fechas de recepción, intervalo medio entre fechas y hasta tres fechas/cantidades recientes por proveedor. Se identifican ingresos sin proveedor. El intervalo entre recepciones **no es un plazo de entrega** y las recepciones no son pedidos abiertos.

No se descuentan mermas futuras ni vencimientos hipotéticos del pronóstico. La disponibilidad física debe comprobarse, particularmente con stock negativo, lotes vencidos o equipos pendientes de sincronizar. La fecha es una estimación de agotamiento, no una garantía de tiempo disponible para reponer.

### Fechas y rankings ponderados

El [análisis histórico](../app/Services/Reportes/AnalisisFechasService.php) agrega en SQL y pagina fechas exactas/recurrentes (20 por página) y líneas de la fecha seleccionada (25). El patrón recurrente suma el día/mes de todo el historial disponible y cuenta años **con ventas**; no añade ocurrencias inexistentes del 29 de febrero ni interpreta ausencia de registro como venta cero certificada. El desglose anual abre cada fecha en calendario o resumen filtrado.

Productos y categorías tienen rankings separados, ordenados por unidades; ingresos conservan el criterio previo. Se usan las categorías actualmente vinculadas a los productos, pues no hay historial de reclasificación. Los productos retirados conservan ventas históricas.

Para cada producto/ocurrencia:

- Precio promedio cobrado = suma de ingresos / suma de unidades.
- Costo unitario de compra estimado = suma de `cantidad × precioCompra` / suma de cantidades de ingresos válidos del producto **hasta el fin del día de esa ocurrencia**, incluyendo todos los del mismo día, independientemente de la hora de venta. Se excluyen ingresos/cabeceras eliminados y cantidades no positivas. No usa compras posteriores ni el costo actual del producto.
- Al combinar ocurrencias, costo total estimado = suma de unidades vendidas en cada ocurrencia × su costo estimado. Se divide por unidades vendidas totales; margen unitario estimado = (ingresos totales − costo total estimado) / unidades totales.
- Categorías se calculan con las filas agregadas de sus productos, usando las mismas ponderaciones; no se promedian promedios.
- Una compra registrada a costo cero es válida. La ausencia de costo anterior es `null`, nunca cero. Si faltan costos para parte de las unidades, se informa cuántas y no se muestra un costo/margen completo. Cada fila indica cuántas ocurrencias contribuyen.

Costo y margen son **estimados**: promedio de compras acumuladas por fecha, sin vínculo venta-lote; no son costo exacto, FIFO, FEFO ni utilidad contable. Las consultas obtienen agregados por producto/ocurrencia y costo mediante subconsulta, sin consultas por cada fila ni descarga del historial de tickets al navegador. Calendario filtra por cuadrícula, pronóstico por ventanas acotadas y análisis exacto por año; solo el ranking recurrente agrega deliberadamente todo el historial.

### Verificación local

Las [pruebas enfocadas](../tests/Feature/CalendarioReportesTest.php) complementan las [pruebas anteriores](../tests/Feature/ReportesTest.php): acceso administrativo, cuadrícula y semana, ofertas históricas/programadas/pausadas/eliminadas, feriados variables y cobertura, selección/filtros, precios/costos/márgenes ponderados, datos faltantes, estados lógicos, estacionalidad, urgencia, stock/mermas/futuros, paginación, bisiestos y ausencia de consultas N+1/escrituras por producto.

Se usan bases de prueba independientes. No se ejecutan migraciones, seeders ni SQL de demostración contra la base de desarrollo para esta funcionalidad. El SQL de usuario presente antes de comenzar y el archivo adjunto de imágenes se conservan sin importar ni modificar.

Resultados finales de esta implementación (Windows, PHP 8.4.23):

- SQLite en memoria: suite completa **198 pruebas, 1.296 aserciones**, todas correctas.
- MySQL 8.0.46 aislado en un puerto/directorio temporal: Reportes + Calendario, **39 pruebas, 161 aserciones**, todas correctas. La instancia y los datos de desarrollo no se utilizan como base de pruebas. No se ha ejecutado CI remoto ni MySQL 8.4 en Linux.
- Laravel Pint en los PHP modificados: correcto; compilación de vistas Blade y `git diff --check`: correctos.
- `npm run build`: correcto. Permanece el aviso previo de `fontaine` opcional; no impide compilar. El primer recorrido de la suite encontró dos fallos por un manifiesto Vite local desactualizado; después de compilar assets, esos casos y la suite completa pasan.
- Navegador: HTML renderizado, cuadrícula mensual de 42 días, contención del desplazamiento a 390 y 1440 px y apertura de un evento con teclado (Enter sobre `summary`).
