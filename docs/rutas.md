# Rutas

Referencia completa de `routes/web.php`. Es la única fuente de rutas (no hay `routes/api.php` activo).

## Sitio público (sin autenticación)

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| GET | `/` | `public.index` | `PublicController@index` | Home del sitio público |
| GET | `/catalogo` | `public.catalogo` | `PublicController@catalogo` | Catálogo público con búsqueda (`q`) y filtro por `categoria` |
| GET | `/contacto` | `public.contacto` | `PublicController@contacto` | Página de contacto y datos de atención |
| GET | `/login` | `login` | — (Route::view) | Formulario de acceso; solo con sesión cerrada |
| POST | `/login` | — | `Auth\Login` | Autentica contra tabla `Usuario` (comparación texto plano — ver pendientes) |

## Sistema (requiere login — middleware `auth`)

### Generales

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| GET | `/version-info` | — | `PublicController@versionInfo` | Información de la versión del sistema |
| GET | `/dashboard` | `dashboard.index` | `DashboardController@index` | Panel principal (órdenes de hoy, alertas) |
| GET | `/reportes` | `reportes.index` | `ReporteController@index` | Reportes ⚠️ parcialmente roto: consultas a tablas inexistentes (`inventario`, `lote`) — ver pendientes |
| GET | `/historial` | `historial.index` | `HistorialController@index` | Historial de ventas |

### Inventario

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| GET | `/inventario` | `inventario.index` | `ProductoController@listarProductos` | Listado de productos con stock/precio/costo |
| ~~GET~~ | ~~`/inventario/{codigo}`~~ | ~~`inventario.show`~~ | ~~`ProductoController@show`~~ | Desactivada: ruta comentada en `web.php`; no se implementará por ahora |
| PUT | `/productos/{codigo}` | `inventario.editarProducto` | `ProductoController@editarProducto` | Editar producto |
| POST | `/inventario/ingresos` | `inventario.ingresos.store` | `ProductoController@registrarIngreso` | Registrar ingreso con proveedor, cantidad, costo y vencimiento |
| POST | `/inventario/salidas` | `inventario.salidas.store` | `ProductoController@registrarSalida` | Registrar merma por vencimiento o daño; valida stock disponible |
| POST | `/productos/guardar` | `productos.crearProducto` | `ProductoController@crearProducto` | Crear producto (con ingreso inicial) — Administrador |

### POS / Ventas

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| GET | `/pos` | `pos.index` | `PosController@index` | Punto de venta |
| POST | `/venta/registrar` | `venta.registrar` | `PosController@registrarVenta` | Registrar venta (crea `salida` + `detalle_salida`) |

### Categorías

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| GET | `/categorias` | `categorias.index` | `CategoriaController@index` | Listado |
| POST | `/categorias` | `categorias.store` | `CategoriaController@store` | Crear |
| PUT | `/categorias/{idCategoria}` | `categorias.update` | `CategoriaController@update` | Editar |
| DELETE | `/categorias/{idCategoria}` | `categorias.destroy` | `CategoriaController@destroy` | Eliminar (soft delete) |

### Clientes

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| GET | `/clientes` | `clientes.index` | `ClienteController@index` | Listado + deudas |
| POST | `/clientes` | `clientes.store` | `ClienteController@store` | Crear |
| GET | `/clientes/{rutCliente}/historial` | `clientes.historial` | `ClienteController@historial` | Compras y deudas de un cliente |
| POST | `/clientes/venta/{idSalida}/pago` | `clientes.registrarPago` | `ClienteController@registrarPago` | Registrar pago de una venta a crédito ⚠️ la vista nunca apunta el form a esta ruta (ver pendientes) |
| PUT | `/clientes/{rutCliente}` | `clientes.update` | `ClienteController@update` | Editar |
| DELETE | `/clientes/{rutCliente}` | `clientes.destroy` | `ClienteController@destroy` | Eliminar |

### Proveedores

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| GET | `/proveedores` | `proveedores.index` | `ProveedorController@listarProveedores` | Listado |
| POST | `/proveedores` | `proveedores.store` | `ProveedorController@store` | Crear |
| GET | `/proveedores/{idProveedor}/productos` | `proveedores.productos` | `ProveedorController@productos` | Productos de un proveedor ⚠️ **rota**: el método `productos` no existe — ver pendientes |
| PUT | `/proveedores/{idProveedor}` | `proveedores.update` | `ProveedorController@update` | Editar |
| DELETE | `/proveedores/{idProveedor}` | `proveedores.destroy` | `ProveedorController@destroy` | Eliminar (soft delete) |

### Usuarios

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| GET | `/usuarios` | `usuarios.index` | `UserController@index` | Listado — Administrador en esta rama |
| POST | `/usuarios` | `usuarios.store` | `UserController@store` | Crear usuario (hashea con bcrypt; ver pendientes por incompatibilidad con login) |

### Sesión

| Método | URL | Nombre | Controller@method | Descripción |
| --- | --- | --- | --- | --- |
| POST | `/logout` | `logout` | `Auth\Logout` | Cerrar sesión |

## Control de roles en esta rama (sin integrar)

Usuarios, categorías, proveedores, edición de productos y reportes requieren auth + role:Administrador. Creación de productos y gestión de ofertas conservan la misma restricción, ahora con el alias role. POS, clientes, historial, consulta de inventario e ingresos/salidas operativas mantienen auth. El middleware deniega con 404 deliberadamente.

User::ROLES define Administrador y Cajero. Ver [pruebas y alcance](revision-roles.md). Las tablas de arriba describen la base: su nota histórica «sin restricción de rol» queda corregida por esta sección.

## Convenciones actuales de nombres de rutas (inconsistencias a normalizar)

- Mezcla de dominios: `inventario.*` vs `productos.crearProducto` para el mismo recurso.
- Mezcla de terminología: `venta.registrar` escribe en la tabla `salida`.
- Mezcla de estilos en vistas: `route()` en algunos enlaces, `url()` hardcodeada en otros.
- Parámetro de ruta `{idCategoria}` vs firma del método `$id`.
- Listado en [pendientes.md](pendientes.md).
