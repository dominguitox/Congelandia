# Modelos y base de datos

## Fuentes de verdad del esquema

1. **`create table.sql`** (raíz) — esquema real de las tablas de negocio. Lo que usan los modelos.
2. **`MWB.mwb`** — diagrama ER de MySQL Workbench.
3. **`database/migrations/`** — ver [migraciones.md](migraciones.md): en la rama `migracion-sql-a-laravel` (pendiente #61, aun sin merge) el esquema completo vive en migraciones; en `main` solo cubren framework + `log_errors`.

> ⚠️ **En `main`, las tablas de negocio no tienen migraciones Laravel**: la BD se monta con `create table.sql` + `inserts.sql` manualmente. En la rama `migracion-sql-a-laravel` `migrate:fresh --seed` produce la BD completa.

## Diagrama lógico (resumen)

```
Usuario (idUsuario, nombre, email, contrasena, rol, activo)
   └── salida.idUsuario → Usuario.idUsuario
   └── ingresa productos (los ingresos no registran usuario)

Categoria (idCategoria, nombre)  [soft delete]
   └── producto.idCategoria → Categoria.idCategoria   [soft delete]

Producto (codigo PK string, nombre, descripcion, idCategoria)  [soft delete]
   ├── IMAGEN_PRODUCTO.codigoProducto → producto.codigo  [soft delete]
   ├── lista_precio.codigoProducto → producto.codigo  [soft delete]
   ├── detalle_ingreso.codigoProducto → producto.codigo  [soft delete]
   ├── detalle_salida.codigoProducto → producto.codigo  [soft delete]
   └── promocion.codigoProducto → producto.codigo  [soft delete]

Proveedor (idProveedor, ...)  [soft delete]
   └── ingreso.idProveedor → proveedor.idProveedor  [soft delete]

Ingreso (idIngreso, idProveedor, ...)  [soft delete]
   └── detalle_ingreso.idIngreso → ingreso.idIngreso  [soft delete]

Cliente (rutCliente PK string, nombre, telefono, correo)  [soft delete]
   └── salida.rutCliente → Cliente.rutCliente

Tipo_Salida (idTipo, nombre)  [soft delete]
   └── salida.idTipo → tipo_salida.idTipo

Salida (idSalida, fecha, idTipo, idUsuario, rutCliente, totalSalida)  [soft delete]
   ├── detalle_salida.idSalida → salida.idSalida  [soft delete]
   └── pago_salida.idSalida → salida.idSalida  [soft delete]

Metodo_Pago (metodos de pago)  [soft delete], Tipo_Devolucion + Devolucion  [soft delete] (tablas definidas en SQL, sin modelos ni uso en app)

Versiones, Log_Errors (sistema, sin soft delete)
```

## Modelos (`app/Models/`)

### Entidades de negocio

| Modelo | Tabla | PK | SoftDeletes | Notas |
| --- | --- | --- | --- | --- |
| `Producto` | `producto` | `codigo` (string, no incremental) | Sí | No usa timestamps. Relaciones: `categoria`, `imagenes` (hasMany ImagenProducto por `codigoProducto`), `precioActual` (hasOne ListaPrecio vigente), `detallesIngreso` |
| `Categoria` | `categoria` | `idCategoria` | Sí | `productos()` hasMany |
| `Cliente` | `Cliente` | `rutCliente` (string) | Sí | `salidas()` hasMany. `destroy()` usa `delete()` (soft delete vía trait) |
| `Proveedor` | `proveedor` | `idProveedor` | Sí | `ingresos()` hasMany |
| `Ingreso` | `ingreso` | `idIngreso` | Sí | `proveedor()`, `detalles()` |
| `DetalleIngreso` | `detalle_ingreso` | `idDetalle` | Sí | `ingreso()`, `producto()` |
| `Salida` | `salida` | `idSalida` | Sí | No usa timestamps. `usuario()`, `detalles()`, `tipo()`, `pagos()` |
| `DetalleSalida` | `detalle_salida` | `idDetalle` (✓ declarado en rama `migracion-sql-a-laravel`) | Sí | `producto()` |
| `TipoSalida` | `tipo_salida` | `idTipo` | Sí | `salidas()` |
| `PagoSalida` | `pago_salida` | `idPago` | Sí | `salida()` |
| `ListaPrecio` | `lista_precio` | `idPrecio` | Sí | Histórico de precios (vigente = `fechaFin` null, última `fechaInicio`) |
| `Promocion` | `promocion` | `idPromocion` | Sí | `producto()` mapea `codigoProducto`→`codigo`; `vigentes()` alimenta dashboard, inventario y POS. El gestor de ofertas vive en Inventario; las acciones se limitan a Administrador. |
| `ImagenProducto` | `IMAGEN_PRODUCTO` | *(no declarada)* | Sí | ⚠️ Su relación `producto()` mapea a `Producto.id` que **no existe** (la PK real es `codigo`) — relación rota, nunca la usa el código; los controladores usan `Producto::imagenes()` |
| `ProductoVenta` | `PRODUCTO_VENTA` | *default* | No | **Sin ningún uso** en la app; sin tabla/migración activa |

> **Soft deletes (commit `5282d75`)**: la migración `2026_10_21_000000_add_soft_deletes_to_business_tables.php` agregó `deleted_at` a `ingreso`, `detalle_ingreso`, `detalle_salida`, `lista_precio`, `promocion`, `imagen_producto`, `pago_salida`, `tipo_salida`, `metodo_pago`, `tipo_devolucion` y `devolucion`. Junto con las tablas que ya lo tenían (`usuario`, `cliente`, `categoria`, `producto`, `proveedor`, `salida`), todas las tablas de negocio soportan borrado lógico. Se excluyó `log_errors` (log) y `versiones` (metadato del sistema). `metodo_pago`, `tipo_devolucion` y `devolucion` tienen la columna pero aún no tienen modelo; al crearlos deben usar `SoftDeletes`.

### Entidades de sistema

| Modelo | Tabla | PK | Notas |
| --- | --- | --- | --- |
| `User` (autenticable) | `Usuario` | `idUsuario` | Sin timestamps. Campos: nombre, email, `contrasena` (oculto), `rol`, `activo` (cast boolean). `getAuthPassword()` devuelve `contrasena`; `getRememberTokenName()` devuelve null. `tieneRol($rol)` compara exacto (trim sobre texto libre). `SoftDeletes` aplicado: usuarios eliminados no aparecen ni pueden autenticarse |
| `Version` | `versiones` | *(no declarada)* | Usada por `PublicController::versionInfo` |
| `LogError` | `log_errors` | *default* | Escrito desde el handler global de excepciones (`bootstrap/app.php`). Sin campo `usuario` poblado |
| `Reporte` | (default `reportes`) | *default* | **Sin uso** — `ReporteController` hace queries crudas con `DB::table`, no pasa por este modelo. Candidato a eliminar |

## Convención de stock

El stock no existe como columna: se **calcula** como ingresos − salidas, con la fórmula:

```sql
COALESCE((SELECT SUM(cantidad) FROM detalle_ingreso di
          JOIN ingreso i ON i.idIngreso = di.idIngreso
          WHERE di.codigoProducto = producto.codigo
            AND di.deleted_at IS NULL
            AND i.deleted_at IS NULL), 0)
-
COALESCE((SELECT SUM(cantidad) FROM detalle_salida ds
          JOIN salida s ON s.idSalida = ds.idSalida
          WHERE ds.codigoProducto = producto.codigo
            AND ds.deleted_at IS NULL
            AND s.deleted_at IS NULL), 0)
```

Las consultas crudas deben filtrar `deleted_at` a mano; Eloquent lo aplica solo por el trait `SoftDeletes`. Esa subconsulta está copiada en 4 lugares (PosController, ProductoController ×2, DashboardController). Consolidarla en un scope o helper es uno de los mejoramientos del backlog (ver [pendientes.md](pendientes.md) y [convenciones.md](convenciones.md)).

## Precios y costos

- **Precio de venta:** tabla `lista_precio` con vigencia (`fechaInicio`/`fechaFin`). El precio vigente es el de `fechaFin IS NULL` más reciente.
- **Costo de compra:** proviene de `detalle_ingreso` (pago proveedor), se consulta por el último ingreso del producto.

## Procedimientos almacenados (`procedimientos.sql`)

| Procedimiento | Qué hace | Estado |
| --- | --- | --- |
| `SP_RegistrarVenta` | Inserta en `salida` | OK (no lo llama la app) |
| `SP_RegistrarDetalleVenta` | Inserta en detalle de venta | ⚠️ **Roto**: inserta en columna `idVenta`, la tabla usa `idSalida` |
| `SP_ObtenerProductoPorId` | Producto + stock calculado | OK (no lo llama la app) |
| `SP_listarProveedores` | Listado proveedores | OK (no lo llama la app) |
| `SP_listarCategorias` | Listado categorías | OK (no lo llama la app) |
| `SP_listarHistorial` | Historial de ventas | OK (no lo llama la app) |
| `SP_listarSalidas` | Listado salidas | OK (no lo llama la app) |
| `SP_DetalleSalida` | Detalle de una salida | OK (no lo llama la app) |

> Comentario importante: la app hace *todas* las consultas desde Eloquent/query builder. Los SP son un artefacto del diseño de BD (pueden mantenerse como utilidad del motor o eliminarse — decisión pendiente del equipo). El archivo del encabezado baja 13 procedimientos y solo crea 8 (los faltantes: `SP_CrearProducto`, `SP_ListarProductos`, `SP_RegistrarSalida`, `SP_DetalleVenta`).

## Buenas prácticas al tocar BD

Ver [convenciones.md](convenciones.md). Resumen:

- Todos los nombres de tabla/columnas que usan los controladores deben coincidir exactamente con el esquema. En MySQL Linux el casing de **tablas** es sensible: si la BD nace de migraciones, modelos (`$table`) y queries crudas deben usar el mismo casing que la migración (`Cliente`, `Producto`, `Ingreso`, `Salida`, etc.). Ver pendientes #66.
- Todos los modelos de negocio usan `SoftDeletes`. En consultas Eloquent no hace falta `whereNull('deleted_at')`; en consultas crudas (`DB::table`, subqueries, reportes) sí hay que filtrar `deleted_at IS NULL` explícitamente.
- Los cambios de esquema van a `create table.sql` hasta que el equipo decida migrar todo a migraciones Laravel (ver [pendientes.md](pendientes.md)).
