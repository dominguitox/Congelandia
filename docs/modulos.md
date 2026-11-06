La [auditoría de noviembre](revision-documentacion.md) describe el estado de la base 0c10668. Login usa hashes; catálogo/contacto, movimientos de inventario, registro de pagos, métodos de pago del POS, ofertas y migraciones ya están implementados. Los reportes nuevos sin commit de otra sesión no se incluyen en esta base.

# Módulos del sistema

Descripción funcional de cada módulo: qué hace, dónde vive (rutas/vistas/controladores) y estado actual.

## 1. Sitio público

| | |
| --- | --- |
| **Rutas** | `GET /` (`public.index`), `GET /catalogo` (`public.catalogo`), `GET /contacto` (`public.contacto`) |
| **Controlador** | `PublicController` |
| **Vistas** | `layouts/public.blade.php` (layout compartido), `public/index.blade.php`, `public/catalogo.blade.php`, `public/contacto.blade.php`, `public/partials/producto-card.blade.php` |
| **Estado** | ✅ Funcional: home, catálogo con búsqueda y filtro por categoría, contacto con datos configurables, sin carrito ni stock visible |

Muestra el catálogo de productos con imagen, precio vigente (`Producto::precioActual`) y categoría para usuarios no autenticados. El stock no se expone en el sitio público. Los datos de contacto se configuran con variables `PUBLIC_*` en `.env` (ver `config/public.php`).

## 2. Acceso (login/logout)

| | |
| --- | --- |
| **Rutas** | `GET /login` (view), `POST /login`, `POST /logout` |
| **Controladores** | `Auth\Login`, `Auth\Logout` (invocables) |
| **Vista** | `resources/views/auth/login.blade.php` |
| **Estado** | ✓ Login valida hashes con Hash::check y la vista usa Bootstrap/auth.css. Las contraseñas creadas desde usuarios son compatibles. |

Tabla de usuarios: `Usuario` (idUsuario, nombre, email, contrasena, rol, activo).

## 3. Dashboard

| | |
| --- | --- |
| **Rutas** | `GET /dashboard` (`dashboard.index`) |
| **Controlador** | `DashboardController` |
| **Vista** | `dashboard/index.blade.php` |
| **Estado** | Muestra órdenes, alertas y ofertas vigentes. La tarjeta de ofertas enlaza al modal de ofertas del inventario mediante `#ofertas`. |

Métricas del día: órdenes de hoy, alertas activas (bajo stock), últimas ventas (reutiliza la vista-card de historial). Requiere normalizar la doble fuente de verdad con reportes.

## 4. POS (punto de venta)

| | |
| --- | --- |
| **Rutas** | `GET /pos` (`pos.index`), `POST /venta/registrar` (`venta.registrar`) |
| **Controlador** | `PosController` (archivo `PosController.php`) |
| **Vistas** | `pos/index.blade.php`; JS: `resources/js/venta.js` |
| **Estado** | ✓ Incluye selector de método de pago, registra `pago_salida` (fiado queda con monto 0) y aplica las ofertas vigentes. El POS muestra comparación de totales sin/con ofertas y el servidor recalcula el monto antes de registrar. |

Flujo: buscar producto por código → agregar al carrito → seleccionar cliente y método de pago → `venta.registrar` crea `salida` (tipo venta, totalSalida) + `detalle_salida` + `pago_salida` (monto 0 si el método es fiado/pendiente). El stock se descuenta implícitamente (se calcula ingresos − salidas, nunca en la tabla).

## 5. Inventario

| | |
| --- | --- |
| **Rutas** | `GET /inventario` (`inventario.index`), `PUT /productos/{codigo}` (`inventario.editarProducto`), `POST /inventario/ingresos` (`inventario.ingresos.store`), `POST /inventario/salidas` (`inventario.salidas.store`), `POST /productos/guardar` (`productos.crearProducto` — **admin**). `GET /inventario/{codigo}` quedó desactivada |
| **Controlador** | `ProductoController`, JS: `resources/js/producto.js` |
| **Vista** | `inventario/index.blade.php` |
| **Estado** | Los modales de ingreso y salida registran movimientos separados; el ingreso precarga el producto desde la fila y la salida impide superar el stock disponible. El botón de ofertas muestra cuántas promociones siguen vigentes y abre su detalle. show ya filtra por código y su ruta sigue desactivada. El botón Borrar no tiene handler. Ver pendientes |

Listado con filtros, ficha de producto (precio vigente, costo, proveedor, stock calculado), detalle y gestor de ofertas (alta, edición, pausa/reactivación y eliminación lógica, solo Administrador), modales separados para registrar ingreso y salida por merma, además de edición/creación de productos con imagen (`images/productos/`).

## 6. Clientes

| | |
| --- | --- |
| **Rutas** | `GET/POST /clientes`, `GET /clientes/{rut}/historial`, `POST /clientes/venta/{idSalida}/pago`, `PUT/DELETE /clientes/{rut}` |
| **Controlador** | `ClienteController` |
| **Vista** | `clientes/index.blade.php` (con script inline; CSS extraído a clientes.css), JS: `resources/js/app.js` (función duplicada) |
| **Estado** | ⚠️ Funcional en general (gestión de deudas/créditos); el formulario de abono usa la ruta `clientes.registrarPago`. Sigue la deuda técnica de JS inline y dos tablas con lógica duplicada. |

Gestión de clientes con compras referenciadas. Dos pestañas: deudas abiertas y pagados. Los pagos se registran sobre `pago_salida` y descienden el saldo total de la venta a crédito.

## 7. Proveedores

| | |
| --- | --- |
| **Rutas** | `GET/POST /proveedores`, `PUT/DELETE /proveedores/{id}` |
| **Controlador** | `ProveedorController`, JS: `resources/js/proveedores.js` |
| **Vista** | `proveedores/index.blade.php` |
| **Estado** | La ruta huérfana de productos se retiró; los productos se cargan desde listarProveedores para la vista. Usa `SoftDeletes` del modelo (consistente con el resto). Es el módulo más reciente y usa un sistema de modales hecho a mano, distinto al resto de la app. |

CRUD estándar con soft delete y consulta de productos ofertados por proveedor (via `ingreso`/`detalle_ingreso`).

## 8. Categorías

| | |
| --- | --- |
| **Rutas** | `GET/POST /categorias`, `PUT/DELETE /categorias/{idCategoria}` |
| **Controlador** | `CategoriaController`, JS: `resources/js/categorias.js` |
| **Vista** | `categorias/index.blade.php` |
| **Estado** | ⚠️ Sin restricción admin en backend (creación de producto y ofertas sí la tienen). El JS (`resources/js/categorias.js`) hardcodea URLs `/categorias/{id}` en vez de usar rutas nombradas. |

CRUD completo con soft delete (`Categoria` usa SoftDeletes).

## 9. Historial de ventas

| | |
| --- | --- |
| **Rutas** | `GET /historial` (`historial.index`) |
| **Controlador** | `HistorialController` (archivo y clase normalizados para Linux) |
| **Vista** | `historial/index.blade.php` |
| **Estado** | Usa stacks de assets y controles aria-expanded; lista ingresos, ventas y mermas con sus detalles. |

Listado de ventas (salidas + detalles + cliente + método de pago) con filtros por fecha/tipo.

## 10. Reportes

| | |
| --- | --- |
| **Rutas** | `GET /reportes` (`reportes.index`) |
| **Controlador** | `ReporteController` |
| **Vista** | `reportes/index.blade.php` |
| **Estado** | Bajo stock calcula ingresos menos salidas y vencimientos consulta detalle_ingreso para próximos 30 días. Faltan por agregar: clientes con deuda, deuda total, ganancia estimada, ventas por festividad (TODO en el código) |

Reportes implementados (numeración del código):
1. Ventas por tipo en un rango — ✅
2. Productos más vendidos — ✅
3. Clientes frecuentes — ✅
4. Inventario total — ✅
5. Historial de ingresos — ✅
6. Historial de ventas por rango — ✅
7. Ventas anuales — ✅
8. Ventas mensuales — ✅
9. Productos con bajo stock — ✅
10. Productos próximos a vencer — ✅
11. Clientes con deuda — 🔜 pendiente
12. Deuda total — 🔜 pendiente
13. Ganancia estimada — 🔜 pendiente
14. Ventas por festividad — 🔜 pendiente

## 11. Usuarios

| | |
| --- | --- |
| **Rutas** | `GET/POST /usuarios` |
| **Controlador** | `UserController`, JS: `resources/js/usuarios.js` (vacío) |
| **Vista** | `usuarios/index.blade.php` |
| **Estado** | ⚠️ Solo creación + listado. Sin admin-check server-side (cualquiera puede crear un Administrador). Botones "Desactivar/Ver historial" sin handler. Markup corregido; contraseña con bcrypt compatible con el login. Ver pendientes |

Gestión de usuarios del sistema sin update ni delete (mejoras pendientes).

## 12. Sistema y configuración

| | |
| --- | --- |
| **Rutas** | `GET /version-info` |
| **Controlador** | `PublicController::versionInfo` (modelo `Version` → tabla `versiones`) |
| **Estado** | ⚠️ Ligero pero funcional. `VersionController` es código huérfano (no se usa en las rutas y su `index()` apunta a una vista que no existe). |

Permite mostrar la versión de la app en el sistema (sidebar).

## Módulos huérfanos (código sin ruta ni vista)

- `IngresoController`, `DetalleIngresoController`, `ListaPrecioController` — en blanco.
- `VentaController` (archivo `VentaController.php`, antes `SalidaController.php`) — SQL incompatible y ninguna ruta lo referencia.
- Modelo `Reporte` — sin referencias.

Ver propuesta de limpieza y decisiones en [pendientes.md](pendientes.md).
