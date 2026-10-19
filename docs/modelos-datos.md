# Modelos y base de datos

## Fuentes de verdad del esquema

1. **`create table.sql`** (raíz) — esquema real de las tablas de negocio. Lo que usan los modelos.
2. **`MWB.mwb`** — diagrama ER de MySQL Workbench.
3. **`database/migrations/`** — solo migraciones de framework + `usuarios` (incompatible con el modelo real) + `log_errors`.

> ⚠️ **Las tablas de negocio no tienen migraciones Laravel.** `php artisan migrate:fresh` deja una base de datos que la app no puede usar. La BD se monta con `create table.sql` + `inserts.sql` manualmente. Ver [pendientes.md](pendientes.md).

## Diagrama lógico (resumen)

```
Usuario (idUsuario, nombre, email, contrasena, rol, activo)
   └── salida.idUsuario → Usuario.idUsuario
   └── ingresa productos (los ingresos no registran usuario)

Categoria (idCategoria, nombre)  [soft delete]
   └── producto.idCategoria → Categoria.idCategoria   [soft delete]

Producto (codigo PK string, nombre, descripcion, idCategoria)  [soft delete]
   ├── IMAGEN_PRODUCTO.codigoProducto → producto.codigo
   ├── lista_precio.codigoProducto → producto.codigo
   ├── detalle_ingreso.codigoProducto → producto.codigo
   ├── detalle_salida.codigoProducto → producto.codigo
   └── promocion.codigoProducto → producto.codigo

Proveedor (idProveedor, ...)  [soft delete]
   └── ingreso.idProveedor → proveedor.idProveedor

Ingreso (idIngreso, idProveedor, ...)
   └── detalle_ingreso.idIngreso → ingreso.idIngreso

Cliente (rutCliente PK string, nombre, telefono, correo)   [sin SoftDeletes, borrado manual con deleted_at]
   └── salida.rutCliente → Cliente.rutCliente

Tipo_Salida (idTipo, nombre)
   └── salida.idTipo → tipo_salida.idTipo

Salida (idSalida, fecha, idTipo, idUsuario, rutCliente, totalSalida)
   ├── detalle_salida.idSalida → salida.idSalida
   └── pago_salida.idSalida → salida.idSalida

Metodo_Pago (metodos de pago), Tipo_Devolucion + Devolucion  (tablas definidas en SQL, sin modelos ni uso en app)

Versiones, Log_Errors (sistema)
```

## Modelos (`app/Models/`)

### Entidades de negocio

| Modelo | Tabla | PK | SoftDeletes | Notas |
| --- | --- | --- | --- | --- |
| `Producto` | `producto` | `codigo` (string, no incremental) | Sí | No usa timestamps. Relaciones: `categoria`, `imagenes` (hasMany ImagenProducto por `codigoProducto`), `precioActual` (hasOne ListaPrecio vigente), `detallesIngreso` |
| `Categoria` | `categoria` | `idCategoria` | Sí | `productos()` hasMany |
| `Cliente` | `Cliente` | `rutCliente` (string) | **No** — borrado manual con `deleted_at` en controlador | `salidas()` hasMany. ⚠️ Inconsistente con los demás: agregar `SoftDeletes` (ver pendientes) |
| `Proveedor` | `proveedor` | `idProveedor` | Sí | `ingresos()` hasMany |
| `Ingreso` | `ingreso` | `idIngreso` | No | `proveedor()`, `detalles()` |
| `DetalleIngreso` | `detalle_ingreso` | `idDetalle` | No | `ingreso()`, `producto()` |
| `Salida` | `salida` | `idSalida` | No | No usa timestamps. `usuario()`, `detalles()`, `tipo()`, `pagos()` |
| `DetalleSalida` | `detalle_salida` | *(no declarada — Laravel usará `id`)* | No | `producto()`. ⚠️ declarar la PK real (ver pendientes) |
| `TipoSalida` | `tipo_salida` | `idTipo` | No | `salidas()` |
| `PagoSalida` | `pago_salida` | `idPago` | No | `salida()` |
| `ListaPrecio` | `lista_precio` | `idPrecio` | No | Histórico de precios (vigente = `fechaFin` null, última `fechaInicio`) |
| `Promocion` | `promocion` | `idPromocion` | No | `producto()` mapea `codigoProducto`→`codigo`. **Sin uso en la app** (controlador huérfano — ver pendientes) |
| `ImagenProducto` | `IMAGEN_PRODUCTO` | *(no declarada)* | No | ⚠️ Su relación `producto()` mapea a `Producto.id` que **no existe** (la PK real es `codigo`) — relación rota, nunca la usa el código; los controladores usan `Producto::imagenes()` |
| `ProductoVenta` | `PRODUCTO_VENTA` | *default* | No | **Sin ningún uso** en la app |

### Entidades de sistema

| Modelo | Tabla | PK | Notas |
| --- | --- | --- | --- |
| `User` (autenticable) | `Usuario` | `idUsuario` | Sin timestamps. Campos: nombre, email, `contrasena` (oculto), `rol`, `activo` (cast boolean). `getAuthPassword()` devuelve `contrasena`; `getRememberTokenName()` devuelve null. `tieneRol($rol)` compara exacto (trim sobre texto libre) |
| `Version` | `versiones` | *(no declarada)* | Usada por `PublicController::versionInfo` |
| `LogError` | `log_errors` | *default* | Escrito desde el handler global de excepciones (`bootstrap/app.php`). Sin campo `usuario` poblado |
| `Reporte` | (default `reportes`) | *default* | **Sin uso** — `ReporteController` hace queries crudas con `DB::table`, no pasa por este modelo. Candidato a eliminar |

## Convención de stock

El stock no existe como columna: se **calcula** como ingresos − salidas, con la fórmula:

```sql
COALESCE((SELECT SUM(cantidad) FROM detalle_ingreso di
          JOIN ingreso i ON i.idIngreso = di.idIngreso
          WHERE di.codigoProducto = producto.codigo), 0)
-
COALESCE((SELECT SUM(cantidad) FROM detalle_salida ds
          JOIN salida s ON s.idSalida = ds.idSalida
          WHERE ds.codigoProducto = producto.codigo), 0)
```

Esa subconsulta está copiada en 4 lugares (PosController, ProductoController ×2, DashboardController). Consolidarla en un scope o helper es uno de los mejoramientos del backlog (ver [pendientes.md](pendientes.md) y [convenciones.md](convenciones.md)).

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

- Todos los nombres de tabla/columnas que usan los controladores deben coincidir con `create table.sql` (cuidado con mayúsculas: `IMAGEN_PRODUCTO`, `Usuario`, `Cliente` vs minúsculas `producto`, `salida`).
- Tablas con soft delete: usar `SoftDeletes` del modelo, no `whereNull('deleted_at')` manual.
- Los cambios de esquema van a `create table.sql` hasta que el equipo decida migrar todo a migraciones Laravel (ver [pendientes.md](pendientes.md)).
