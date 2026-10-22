# Pendientes y deuda técnica

Backlog derivado de la auditoría del proyecto (octubre 2026). Cada ítem tiene referencias a archivo y línea para ubicarlo rápido. Al resolver un ítem: marcarlo y mover la referencia a lo resuelto en el PR correspondiente. Los ítems marcados ✓ en la rama `implementacion-estilos-css` corresponden a la unificación visual del panel (ver [estilos.md](estilos.md)). Los marcados ✓ sin rama se resolvieron en `main`.

## P0 — Rompe la app hoy (críticos)

| # | Problema | Ubicación |
| --- | --- | --- |
| 1 | ~~Vista `public.catalogo` no existe~~ ✓ **resuelto**: catálogo implementado con búsqueda, filtro por categoría y tarjetas sin carrito ni stock | `PublicController.php`; `resources/views/public/catalogo.blade.php` |
| 2 | ~~Vista `inventario.show` no existe → `GET /inventario/{codigo}` (ruta viva) lanza error~~ ✓ **resuelto**: ruta desactivada (comentada) en `routes/web.php`; no se implementará por ahora | `routes/web.php:101-108` |
| 3 | `show()` no filtra por código: devuelve TODOS los productos y muestra el primero, ignorando el parámetro de ruta. La ruta quedó desactivada (#2), pero el método sigue así | `ProductoController.php:227-261` (falta `where('producto.codigo', $codigo)`) |
| 4 | `ProveedorController::productos` no existe → `GET /proveedores/{id}/productos` (ruta viva) rompe | `routes/web.php:214`; implementar el método o quitar la ruta |
| 5 | Login compara contraseñas en **texto plano**, pero `usuarios.store` guarda con `bcrypt` → un usuario creado desde el sistema nunca puede iniciar sesión | `Auth\Login.php:24` vs `UserController.php:29`. Decidir esquema (recomendado: hash en todos lados y usar `Hash::check` en Login) |
| 6 | Reportes rompen: queries sobre tablas inexistentes (`inventario`, `lote`) para "bajo stock" y "próximos a vencer" (el comentario del código lo admite) | `ReporteController.php:172-204` |
| 7 | `onclick="registrarIngreso('...')"` en inventario: se **corrigió** el PHP crudo dentro del atributo (ahora string JS válido), pero la función `registrarIngreso` **no existe** (solo `registrarIngresoSingle`, que apunta a `/ingresos/...` inexistente) → sigue el `ReferenceError` | `inventario/index.blade.php`; `resources/js/producto.js:11-19` |
| 8 | Form de registro de pago sin `action` y su ruta `clientes.registrarPago` es huérfana → al pagar se hace POST a la página actual (rutas equivocadas) | `clientes/index.blade.php:1013`; `routes/web.php:182-185` |
| 9 | En POS, el botón llama `registrarVenta()` sin argumento y la función espera `metodoPagoSeleccionado` → el método de pago viaja `undefined` en el payload | `pos/index.blade.php:81`; `resources/js/venta.js:123,167` |
| 10 | ~~Dashboard muestra "0" hardcodeado en Órdenes/Alertas~~ ✓ **resuelto** (usa `$ordenesHoy`/`$alertasActivas`) | `dashboard/index.blade.php` (rama `implementacion-estilos-css`) |

## P1 — Seguridad

| # | Problema | Ubicación |
| --- | --- | --- |
| 11 | **Sin restricción de rol en backend** para: gestión de usuarios (crear Administradores), CRUD de categorías, CRUD de proveedores, edición de productos. Cualquier usuario autenticado (ej. Cajero) puede usarlas; el sidebar solo oculta los enlaces | `routes/web.php:60-252`. Agregar `'role:Administrador'` a los grupos correspondientes |
| 12 | `rol` se valida como `string` libre → cualquier texto se vuelve un "rol válido" (rompe `tieneRol` silenciosamente) | `UserController.php:23`. Usar lista permitida (enum/const) |
| 13 | `CheckRole` hace `abort(404)` en vez de 403. Deliberado (oculta la ruta), pero conviene documentarlo o cambiarlo | `CheckRole.php:19` |
| 14 | Contraseñas de los usuarios semilla (`inserts.sql`) están en texto plano y el comentario del propio archivo lo admite. Relacionado con #5 | `inserts.sql:5-13`, `database/seeders/UsuarioSeeder.php:22` |

## P2 — Portabilidad bloqueante (Windows → Linux)

Todo esto **funciona en Windows por case-insensitive del filesystem** y rompe en un deploy Linux/hosting. Los ítems #15–#19 ya están resueltos; queda el casing de tablas en MySQL:

| # | Problema | Ubicación |
| --- | --- | --- |
| 15 | ~~`posController.php` (archivo minúscula) contiene `class PosController`~~ ✓ **resuelto**: archivo renombrado a `PosController.php` y rutas alineadas | `app/Http/Controllers/PosController.php`, `routes/web.php:8` |
| 16 | ~~`historialController.php` + `class historialController` en minúsculas~~ ✓ **resuelto**: archivo y clase `HistorialController`, imports/uso actualizados | `app/Http/Controllers/HistorialController.php`, `routes/web.php:3,134` |
| 17 | ~~Carpeta de vistas `POS/` (mayúsculas) vs `view('pos.index')` buscada en minúsculas~~ ✓ **resuelto**: carpeta renombrada a `resources/views/pos/` | `resources/views/pos/` |
| 18 | ~~`usuarioos.js` (typo, vacío 0 bytes) referenciado por `@vite` en usuarios pero NO está como input de vite~~ ✓ **resuelto**: renombrado a `usuarios.js` y registrado en `vite.config.js` (sigue vacío, ver #33) | `resources/js/usuarios.js`, `vite.config.js` |
| 19 | ~~`public.css` se referencia vía `@vite` y no es input de vite; `public.js` no existe~~ ✓ **resuelto** (`public.css` registrado como input; se quitó la referencia a `public.js`) | `public/index.blade.php`, `vite.config.js` (rama `implementacion-estilos-css`) |
| 66 | **Casing de tablas en MySQL Linux**: las migraciones crean `Cliente`, `Categoria`, `Producto`, `Lista_Precio`, `Ingreso`, `Salida`, `Detalle_Salida`, `Tipo_Salida`, etc., pero casi todos los modelos usan `$table` en minúsculas (`categoria`, `producto`, `ingreso`, `salida`, `detalle_salida`, `tipo_salida`) y varias queries crudas también. Con `lower_case_table_names=0` fallan tanto Eloquent como `DB::table()`/subqueries. Si en cambio la BD se importa desde los `.sql` (minúsculas), fallan los modelos que usan PascalCase (`Cliente`, `Usuario`, `IMAGEN_PRODUCTO`). Hay que unificar todo a un solo casing (recomendado: minúsculas snake_case) | Modelos en `app/Models/*.php`; `HistorialController.php:33,61,86,121,229`; `ReporteController.php`; `ProveedorController.php:111,141,229`; migraciones `create_*` |

> Estrategia sugerida para #66: un PR único "normalización de tablas" que cambie migraciones, modelos y queries crudas al mismo casing, y valide con `migrate:fresh` + tests en un MySQL Linux (o contenedor).

## P3 — Bugs funcionales de menor impacto

| # | Problema | Ubicación |
| --- | --- | --- |
| 20 | ~~Imagen 404 `images/empanadas.jpg` (existe en `images/productos/`) y `images/default.png` que no existe en ningún lado~~ ✓ **resuelto**: hero usa la imagen existente y las tarjetas sin foto muestran un placeholder SVG inline | `public/index.blade.php`; `public/partials/producto-card.blade.php` |
| 21 | Menú "Roles" apunta a `/Roles` → 404 (no existe la ruta). Decidir: eliminar el link o crear el módulo | `partials/sidebar.blade.php:14` |
| 22 | ~~Enlaces muertos `href="#"`: "Contacto" (layout público)~~ ✓ **resuelto**: página y ruta `public.contacto` | `layouts/public.blade.php` |
| 23 | ~~Typo `Orden #s{{...}}` renderiza "Orden #s5"~~ ✓ **resuelto** | `dashboard/index.blade.php` |
| 24 | ~~Dos `</div>` duplicados generan DOM malformado en el dashboard~~ ✓ **resuelto** (vista reescrita) | `dashboard/index.blade.php` |
| 25 | ~~Card de usuarios malformada (`card-body` fuera del `card`); tabla vacía con `colspan="5"` para 6 columnas~~ ✓ **resuelto** | `usuarios/index.blade.php` |
| 26 | `dd($errors)` en modales de inventario: se **eliminó** el volcado de debug; falta mostrar los mensajes de validación con `@error` | `inventario/index.blade.php` |
| 27 | ~~Login con clases DaisyUI que no está instalado~~ ✓ **resuelto** (reescrito con Bootstrap + `auth.css`) | `auth/login.blade.php` |
| 28 | ~~Div con clases inválidas y `style="padding-top:45px"` impreso antes de `<body>` (HTML inválido) y `session('usuario')` puede lanzar si la sesión trae escalar~~ ✓ **resuelto**: home reescrita sobre `layouts.public` y nombre de sesión normalizado | `layouts/public.blade.php`; `public/index.blade.php` |
| 29 | `SP_RegistrarDetalleVenta` inserta en columna `idVenta` que no existe (la tabla usa `idSalida`) — ningún SP se llama desde la app hoy | `procedimientos.sql:57` |
| 30 | Incoherencia PHP: `composer.json` declara `^8.3`, el proyecto corre con 8.4 — alinear declaración | `composer.json:12` |
| 31 | Dependencia `laravel/pao` (typo de `laravel/pail`? ya está `pail`) — revisar para qué está o quitarla | `composer.json:20` |
| 32 | `spatie/laravel-permission` instalada y sin uso (los roles son columna de texto + CheckRole casero). Decidir: adoptar o eliminar la dependencia | `composer.json:15` |
| 33 | Assets muertos: `error.js` (placeholder "nose despues lo pienso mas"), `usuarios.js` (vacío). ~~CSS vacíos (`reportes.css`, `categorias.css`, `inventario.css`, `dashboard.css`)~~ ✓ estos ya tienen contenido | `resources/js` |
| 34 | Timezone UTC y locale `en`: cambiar a `America/Santiago` y `es` (efectos en fechas mostradas y validaciones) | `config/app.php:58-68`, `.env` (`APP_LOCALE`, `APP_TIMEZONE` no existe → agregar) |
| 35 | `* { user-select: none; }` global evita que el usuario pueda copiar texto (también afecta inputs de búsqueda) | `resources/css/app.css:2-4` |

## P4 — Deuda técnica / refactor

| # | Problema | Ubicación |
| --- | --- | --- |
| 36 | Consulta de producto con precio/costo/proveedor/stock **copiada 4 veces** → consolidar en scope `Producto::scopeConDetalle()` | `PosController.php:22-53`, `ProductoController.php:95-134,227-258`, `DashboardController.php:45-92` |
| 37 | `listarProductos` selecciona `precio` Y `precioVenta` (dos subconsultas a la misma tabla) — redundante | `ProductoController.php:103-109,127-132` |
| 38 | ~~CSS inline masivo en clientes~~ ✓ **CSS extraído** a `resources/css/clientes.css`; falta extraer el bloque `<script>` inline (`clientes/index.blade.php`) a `resources/js/clientes.js` (duplica el toggle de `resources/js/app.js:28-46`) | `clientes/index.blade.php` |
| 39 | Los 3 modales de inventario (ingreso/salida/abastecimiento) tienen cuerpo idéntico con **IDs de DOM duplicados** y los 3 apuntan a `productos.crearProducto` (ingreso/merma no hacen lo que dicen) | `inventario/index.blade.php:83-351` |
| 40 | ~~Soft delete manual con `whereNull('deleted_at')` duplicado~~ ✓ **parcial**: `SoftDeletes` aplicado a todos los modelos de negocio y `ClienteController::destroy` usa `delete()`. Queda limpiar los `whereNull('deleted_at')` redundantes (~10 lugares) y decidir la cascada lógica de hijos | `PosController.php:58`, `ProductoController.php:24,137`, `DashboardController.php:115`, `ClienteController.php:22,146,191`, `ProveedorController.php` (varios) |
| 41 | Dos sistemas de modales convivendo (Bootstrap en inventario/categorías/usuarios vs overlays hechos a mano en clientes/proveedores) → estandarizar componentes | vistas de ambos módulos |
| 42 | Copia de mensajes de error: "Error al cargar el historial" dentro de DashboardController | `DashboardController.php:132` |
| 43 | URLs hardcodeadas en JS en lugar de rutas nombradas / data-attributes | `venta.js:156`, `categorias.js:24,42`, `proveedores.js:282,359`, `producto.js:8,18`, `clientes/index.blade.php:1569,1682,2007` |
| 44 | Sidebar mezcla `route()` y `url()` y checks de activación (`routeIs` vs `is`) con patrones inconsistentes | `partials/sidebar.blade.php` |

## P5 — Código muerto (eliminación propuesta)

Antes de eliminar: acordar en el equipo y hacer **un solo PR** con la limpieza, tras estar seguros de que nada lo referencia (validar con búsqueda global).

| # | Ítem | Nota |
| --- | --- | --- |
| 45 | `resources/views/welcome.blade.php` | Starter de Laravel, `welcome.blade.php` sin uso |
| 46 | ~~`resources/views/layouts/public.blade.php`~~ ✓ **resuelto**: layout compartido por home, catálogo y contacto | `layouts/public.blade.php` |
| 47 | `resources/views/partials/navbar.blade.php` | Sin uso; competía con `partials/header` ; logout con `action=""` |
| 48 | `partials/footer.blade.php` | Vacío — decidir si se llena o se elimina |
| 49 | `DetalleIngresoController`, `IngresoController`, `ListaPrecioController` | Archivos vacíos |
| 50 | `VentaController` (archivo `VentaController.php`, antes `SalidaController.php`) | **PSR-4 ✓ resuelto**, pero sigue huérfano: SQL incompatible con el esquema y sin rutas |
| 51 | `PromocionController` + modelo `Promocion` | CRUD completo huérfano: sin rutas ni vistas `promociones.*` (decidir si la feature de promociones entra o se elimina) |
| 52 | `VersionController` | Importado en `routes/web.php:14` y no usado; su `index()` apunta a `view('dashboard')` inexistente |
| 53 | `ProductoController::index()` (método vacío) | Sin ruta |
| 54 | Modelo `Reporte` | Sin referencias (el controlador usa queries crudas) |
| 55 | Modelo `ImagenProducto` (su relación `producto()` rota → `Producto.id` inexistente) | El código funcional usa `Producto::imagenes()` |
| 56 | `ProductoVenta` (tabla `PRODUCTO_VENTA`) | Sin uso en la app |
| 57 | Carpeta `viejo/` | Vacía |
| 58 | `congelandia_backup.sql` | Dump de tablas de framework, sin datos de negocio — decidir si se conserva en git |
| 59 | `resources/js/error.js` | Placeholder |
| 60 | `routes/web.php:20` import de `CheckRole` no utilizado como alias, y ruta `VersionController` import muerta | limpiar imports |

## P6 — Estructura del proyecto (decisiones pendientes del equipo)

| # | Decisión | Contexto |
| --- | --- | --- |
| 61 | ~~**Migrar tablas de negocio a migraciones Laravel**~~ ✓ **resuelto en rama `migracion-sql-a-laravel`** (pendiente de merge a main) | Todas las tablas de negocio + secciones/`Versiones`/`IMAGEN_PRODUCTO` tienen migración; `migrate:fresh` + `db:seed` producen una BD usable (`composer run setup` ahora corre ambos). La migración `usuarios` incoherente fue reemplazada. Detalle y deltas: [migraciones.md](migraciones.md) |
| 62 | ¿Se mantienen los stored procedures? Hoy la app no los llama | `procedimientos.sql` — si se conservan, corregir #29 y quitar los `select *` debug y los `DELIMITER //` sueltos |
| 63 | Definir suite de tests mínima para ventas/stock (regresión del cálculo ingresos-salidas) | ~~Solo 2 stubs~~ se agrego `tests/Feature/MigrationsTest.php` (esquema + FK) en la rama `migracion-sql-a-laravel`; falta la regresión del cálculo de stock |
| 64 | Definir roles canónicos y documentar en el seed | `Administrador`, `Cajero` aparecen en datos; `UserController` acepta cualquier string (ver #12) |
| 65 | ~~Welcome/branding: imágenes faltantes (#20), texto placeholder "Delivery? Ubicación?" sin definir~~ ✓ **resuelto**: hero con imagen existente, placeholder SVG y contacto centralizado en `config/public.php` | `public/index.blade.php` |

## Cambios resueltos en `main` (octubre 2026)

- **`5282d75` — Soft deletes en tablas de negocio**: migración `2026_10_21_000000_add_soft_deletes_to_business_tables`, trait `SoftDeletes` en 11 modelos, `ClienteController::destroy` migrado a `delete()` y queries crudas de stock/historial/reportes ajustadas para excluir registros eliminados. `log_errors` y `versiones` quedan fuera. Detalle en [modelos-datos.md](modelos-datos.md).
- **`aea433e` — Normalización para Linux**: `PosController.php`, `HistorialController.php`, `VentaController.php` (antes `SalidaController.php`) y `resources/views/pos/` con archivo == clase y casing correcto. Resuelve #15–#17.

## Cómo abordarlos (sugerencia)

1. **P0** en un PR por bug o grupo pequeño — son rápidos y desbloquean rutas.
2. **P2 (portabilidad)**: #15–#19 ya resueltos; queda #66 (casing de tablas) en un PR único que toque migraciones, modelos y queries crudas a la vez, validado en MySQL Linux.
3. **P1 (roles)** completo en un PR, con pruebas de que Cajero queda bloqueado.
4. **P4/P5** con acuerdos del equipo; P5 en un solo PR de limpieza con `git rm`.
5. **P6** discutirlo en reunión: impacta cómo se trabaja hacia adelante más que un fix puntual. #61 ya está resuelto como rama `migracion-sql-a-laravel` — solo falta aprobar el merge a `main`.
