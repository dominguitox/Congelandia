# Migración única del PDV a Congelandia

## Variante con historial en las tablas actuales

La variante nueva convierte los movimientos identificados a `ingreso`/`detalle_ingreso` y `salida`/`detalle_salida`, sin agregar tablas ni columnas. Usar **una base de ensayo nueva**, con migraciones y los seeders básicos. No aplicar la apertura propuesta de la variante anterior: duplicaría las existencias.

```powershell
php tools/migration/history-to-mysql.php --input=output/pdv-migration/origen --output=output/pdv-history/sql
$env:PDV_DSN = 'mysql:host=127.0.0.1;port=13307;dbname=congelandia_historico;charset=utf8mb4'
$env:PDV_USER = 'pdv_ensayo'
# Definir la contraseña local del ensayo en PDV_PASSWORD.
php tools/migration/apply-sql.php --file=output/pdv-history/sql/01_catalogo.sql
php tools/migration/apply-sql.php --file=output/pdv-history/sql/02_historial.sql --accept-history-assumptions
php tools/migration/apply-sql.php --file=output/pdv-history/sql/03_verificar.sql
php tools/migration/apply-sql.php --file=output/pdv-history/sql/04_verificar_historial.sql
```

Resultados del respaldo recibido: 28.733 movimientos originales importables; 9.217 pendientes correspondientes a 107 códigos sin ficha ni precio recuperable en ninguno de los dos catálogos. Estos pendientes se conservan completos en `movimientos_pendientes.csv`; no se crean productos ficticios. El proceso no es una migración completa de los 37.950 registros mientras existan esos pendientes.

Se crea `Usuario histórico`, con rol Cajero, inactivo y una contraseña aleatoria no entregada, exclusivamente para atribuir registros. No se modifica el inicio de sesión. Todos los movimientos originales conservan su fecha y hora; las cantidades se convierten a diferencias para los ajustes. Los nuevos IDs empiezan en 1 en cada tabla vacía, se dejan preparados los siguientes autoincrementos y la equivalencia de cada registro original queda en `registro_acciones.referencia`, junto con el hash del respaldo, tipo y usuario originales. No se importan cajas.

Tipos de salida: `Salida histórica` para `s`; `Ajuste histórico` para ajustes que disminuyen existencias; `Conciliación histórica` para diferencias calculadas. Las entradas `e` y `d`, así como los ajustes positivos o sin cambio, se representan como ingresos. La interpretación de `d` es una entrada por su efecto en las existencias; no se inventa una relación con una venta ni una devolución comercial.

Ejemplo de ajuste: `HABIA=10`, `CANTIDAD=7`, `TIPO=a` genera una salida de **3**. Los 7.168 ajustes sin cambio de los productos identificados se conservan como detalles de ingreso con cantidad cero. No incrementan existencias ni importes. Para reproducir saldos se derivan 16 movimientos iniciales y 13 conciliaciones adicionales desde los valores `HABIA`; están diferenciados de los movimientos originales y documentados en `ajustes_calculados.csv`. Son cálculos del proceso, no operaciones originales acreditadas: revisar antes del corte definitivo. Resultado: 28.762 movimientos nativos y saldo propuesto total de 2.592, incluidos productos inactivos.

Por acuerdo de migración se usa **un precio por producto**: `PVENTA` de su ficha para las salidas históricas. Para las entradas se conserva `PCOSTO` como costo de referencia; no se interpreta como costo histórico confirmado. Solo `e` y `s` reciben importes calculados; ajustes, conciliaciones, saldos iniciales y entradas `d` tienen total cero. No se crean pagos, proveedores, clientes ni comprobantes. Los 171 costos cero originales se mantienen.

El Historial está paginado, con búsqueda en servidor por usuario, tipo, código y nombre de producto. Los reportes incluyen `Salida histórica` en las fechas originales y excluyen ajustes y conciliaciones; muestran que los importes son de referencia. Una salida original equivale a un movimiento por producto, no necesariamente a un ticket: las cantidades de salidas históricas no deben interpretarse como cantidad de boletas. Los importes de esa parte del reporte son estimaciones con el precio disponible, no recaudación acreditada. La demanda/pronóstico también puede utilizar esas salidas.

Ambos verificadores deben devolver `[]` antes de operar. La importación rechaza un destino con ingresos o salidas y se puede repetir sin duplicar registros. No cambia el archivo Firebird ni publica código. Para probar visualmente la base nueva, configurar la aplicación con la conexión indicada, ejecutar `php artisan config:clear` y abrir inventario, Historial, POS y Reportes. La base del ensayo anterior permanece separada.

## Alcance

Conversión local de una copia Firebird ODS 10.1 a SQL MySQL 8 para el esquema actual de Congelandia. No instala servicios, no lee la conexión de `.env` y no modifica el archivo original. Solo incorpora catálogo, categorías referenciadas, precios vigentes y una apertura propuesta de inventario.

Los scripts y pruebas se versionan; el archivo del cliente, las herramientas descargadas, los JSON y los SQL con datos quedan en `tmp/` y `output/`, ignorados por Git. No subirlos al repositorio ni adjuntarlos al PR.

## 1. Exportar y convertir (Windows)

Requiere Windows PowerShell 5.1 de 32 bits (el script se relanza automáticamente), acceso a Internet la primera vez y PHP 8.4 con `mbstring`, `iconv`, `json` y `pdo_mysql` para aplicar. No requiere extensión Firebird de PHP ni Composer adicional.

```powershell
& tools/migration/export-pdv.ps1 -Source 'C:\Users\domingo\Desktop\pdvdata1.fdb'
php tools/migration/pdv-to-mysql.php --input=output/pdv-migration/origen --output=output/pdv-migration/sql
```

El exportador descarga Firebird Embedded 2.5.9 x86 del [repositorio oficial](https://github.com/FirebirdSQL/firebird/releases/tag/R2_5_9) y el proveedor oficial Firebird .NET 5.12.1 de NuGet. Comprueba SHA-256 de ambos paquetes. Solo abre su copia de trabajo y comprueba que el original no cambió durante la extracción. Los JSON se leen en una transacción y se registran con hashes en `manifest.json`.

La codificación predeterminada es WIN1252 porque las columnas de este PDV declaran `NONE`. Si al revisar los textos no coincide, usar `-Charset` y otra salida. No corregir caracteres reemplazándolos silenciosamente.

Para otra extracción, usar un `-OutputDirectory` nuevo y luego otro `--output`. La conversión verifica los hashes y rechaza archivos incompletos, colisiones de códigos, importes no representables y tablas de clientes/ventas/pagos con datos que quedarían fuera de su alcance. `--cutoff="AAAA-MM-DD HH:MM:SS"` permite indicar el corte; por defecto usa la modificación del respaldo y no admite movimientos posteriores.

## 2. Revisar los resultados

| Archivo generado | Uso |
| --- | --- |
| `01_catalogo.sql` | Productos, categorías y precios; no incorpora stock |
| `02_stock_propuesto.sql` | Una apertura con costos y stock inferido; requiere revisión |
| `03_verificar.sql` | Debe devolver cero diferencias antes de comenzar a operar |
| `stock_revision.csv` | Origen del saldo, último movimiento, costo, precio y campos sin equivalencia |
| `resumen.json` | Alcance, exclusiones, anomalías y productos por revisar |

Reglas del stock: si `DINVENTARIO` es no negativo se conserva; si vale `-1`, se propone el saldo posterior al último registro por fecha e ID: `e`/`d` = había + cantidad; `s` = había − cantidad; `a` = cantidad absoluta. Estas reglas se infieren de la continuidad del historial; no sustituyen el inventario físico. Las discontinuidades se informan. No se suman todos los movimientos porque los ajustes son absolutos. Si hay saldo desconocido, negativo o fraccionario no se genera el SQL de stock.

Los códigos siguen siendo texto y conservan ceros iniciales; se recortan espacios exteriores. Si falta el nombre se usa el código y se registra. Tipos de venta distintos de `U` se conservan **inactivos** mediante `deleted_at`, hasta confirmar cómo representarlos con las cantidades enteras del POS. No reactivarlos sin revisar su unidad y precio.

El historial de inventario queda archivado en JSON. No se recrean ventas sin comprobantes e importes. `PRODUCTOS_BASE` queda como archivo auxiliar; no se mezcla con `PRODUCTOS`. Los dos registros de facturación usan RFC y no se transforman automáticamente en clientes con RUT. No se importan contraseñas. Precios mayoristas, mínimos, máximos y unidades sin equivalencia permanecen en los JSON y, cuando corresponde, en el CSV.

**Editar el CSV no cambia el SQL.** Ante una diferencia, corregir la regla o la fuente acordada, regenerar en otro directorio y repetir el ensayo. No importar un stock propuesto que no coincida con el inventario validado.

## 3. Ensayar en una base MySQL independiente

Crear una base vacía con el esquema de la versión que se usará y sembrar solo administrador, métodos de pago y tipos de salida. No cargar productos de demostración. No usar la base habitual de desarrollo como ensayo. Conservar un respaldo de ese estado inicial.

```powershell
$env:PDV_DSN = 'mysql:host=127.0.0.1;port=3306;dbname=congelandia_pdv_ensayo;charset=utf8mb4'
$env:PDV_USER = 'usuario_del_ensayo'
# Definir PDV_PASSWORD localmente sin guardarlo en scripts ni en Git.
php tools/migration/apply-sql.php --file=output/pdv-migration/sql/01_catalogo.sql
# Solo después de revisar el stock propuesto:
php tools/migration/apply-sql.php --file=output/pdv-migration/sql/02_stock_propuesto.sql --accept-proposed-stock
php tools/migration/apply-sql.php --file=output/pdv-migration/sql/03_verificar.sql
```

El aplicador ejecuta todo el archivo en una conexión y hace `ROLLBACK` si una sentencia falla. No usar clientes que continúen después de un error. Los SQL no borran ni reemplazan tablas y mantienen las claves foráneas. La categoría existente puede reutilizarse; los productos del destino deben estar vacíos para la primera etapa. Debe existir un administrador activo; se atribuye la apertura al de menor ID. Cada etapa queda identificada por el hash del origen en `registro_acciones`; repetirla devuelve `YA_IMPORTADO` sin duplicar datos. `BLOQUEADO` significa que no se aplicó la etapa y el aplicador sale con error.

La apertura se bloquea si ya existen detalles de ingresos o salidas para esos productos. No operar entre 01 y 02. Se crea un ingreso sin proveedor, sin vencimiento y con `totalCompra=0`: es saldo de apertura, no compra ni deuda nueva. Los detalles conservan costos, incluso para existencias cero. Es visible en historial; la categoría de ventas y los reportes de ventas no reciben operaciones ficticias. El cálculo de costos/pronóstico de la aplicación partirá de esta apertura, sin historia comercial anterior.

Verificar después: catálogo, `/inventario`, `/pos`, `/clientes`, `/historial`, `/dashboard`, `/reportes` y `/version-info`. Probar una venta de un producto migrado, un ingreso y una merma; verificar cantidad, precio y pago. Los clientes y abonos del respaldo inicial están vacíos, por lo que no hay deudas originales que conciliar. Restaurar el respaldo inicial y repetir el ensayo para verificar recuperación; guardar resultados y tiempos localmente.

## Traslado definitivo

Este procedimiento prepara y ensaya datos; no publica una versión. Antes del corte, obtener un respaldo consistente y actualizado del sistema anterior, acordar una pausa de escrituras y repetir exportación y conciliación. Si cambió el alcance, el conversor debe adaptarse antes de continuar. Respaldar el destino y conservar la base anterior hasta la aceptación del cliente. Revertir archivos de la aplicación no revierte datos: recuperar la base desde su respaldo, cuidando las operaciones posteriores al corte.

Si también se publica código, seguir [despliegue DirectAdmin](despliegue-directadmin.md): rama desde `main`, PR y CI; nunca desarrollar en `deploy`. Git no importa datos ni actualiza MySQL. Si se importa mediante phpMyAdmin, hacerlo en orden 01 → 02 → 03, en lotes completos y deteniéndose ante errores; conservar el respaldo y preferir el aplicador para asegurar rollback. Nunca ejecutar `composer run setup` en producción: utiliza `migrate:fresh`.
