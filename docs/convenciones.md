# Convenciones de código

Reglas que el equipo debe seguir en el código nuevo y en los ajustes a código existente. Varias "vigentes" aún no se cumplen en todo el proyecto: esas están anotadas en [pendientes.md](pendientes.md) como deuda a normalizar.

## Nombres

| Elemento | Regla | Ejemplos correctos | Violaciones actuales |
| --- | --- | --- | --- |
| Controlador (archivo y clase) | PascalCase, mismo nombre para archivo y clase | `ProductoController.php` → `class ProductoController` | `historialController.php`, `posController.php` (minúsculas); `SalidaController.php` contiene `class VentaController` |
| Metodos de controlador | camelCase, verbos claros | `listarProductos`, `registrarVenta`, `store`, `update` | Mezcla `index`/`listarProductos` como listados equivalentes |
| Métodos invocables de Auth | `__invoke()` | `Login`, `Logout` | — |
| Vistas | minúsculas y con la ruta del controller | `pos/index.blade.php` para `view('pos.index')` | Carpeta `POS/` en mayúsculas (rompe en Linux) |
| Rutas (nombre) | dominio consistente por recurso | `productos.index`, `productos.store` | `inventario.index` junto con `productos.crearProducto`; `venta.registrar` escribe en `salida` |
| Variables / props | camelCase | `$resultado` | — |
| Tablas BD | ver [modelos-datos.md](modelos-datos.md) — *respetar el esquema vigente hasta migrar* | `producto`, `salida` | Consultas crudas que mezclan `Categoria`/`Producto`/`detalle_ventas`(?) con minúsculas |

**Por qué importa el PascalCase:** el autoloader de Composer y el server de producción (Linux) son case-sensitive. En Windows funciona por casualidad; en deploy Linux `PosController` no se resuelve a `posController.php` y la aplicación rompe. Regla: **archivo == clase y ambos PascalCase**.

## Controladores

1. Decide primero si un módulo usa generación estándar (`index/store/update/destroy`) o verbos de negocio (`listarProductos`/`crearProducto`) — no mezclar en el mismo recurso.
2. Un método = una acción de una ruta. Métodos que ninguna ruta usa: eliminarlos o cablearlos.
3. Envuelve operaciones de escritura en `try/catch` y devuelve flash de error (`->with('error', ...)`) — es el patrón existente y el handler global de `bootstrap/app.php` ya registra la excepción en `log_errors`.
4. Usa el modelo y sus relaciones en vez de repetir subconsultas SQL. Consulta importante que hoy está copiada 4 veces (precio/costo/proveedor/stock): **consolidarla en un scope del modelo** (ej. `Producto::scopeConDetalle()`) antes de copiarla otra vez — ver [modelos-datos.md](modelos-datos.md).

## Validación

- Valida toda entrada en el servidor con `$request->validate()`; los `required`/`max` del HTML solo son cortesía de UX.
- Reglas compartidas entre `store` y `update`: extraerlas a un array/método privado o una Form Request para no duplicarlas (pasa hoy en `CategoriaController`, `ProveedorController`).
- Para campos de valores acotados (ej. `rol`), validar con lista permitida o tabla de referencia, no como `string` libre (cualquier string se vuelve un rol "válido").

## Vistas Blade

> Para la organización de CSS, componentes reutilizables y temas (tokens, `APP_TEMA`), ver [estilos.md](estilos.md).

1. **Assets solo por `@push`**: `@push('css')` y `@push('scripts')` — el layout `app.blade.php` ya define los stacks. Prohibido meter `@vite` dentro de `@section('content')` (normalizado en POS, inventario, categorías y usuarios).
2. **Prohibido CSS/JS inline masivo**. El caso histórico de `clientes/index.blade.php` ya fue extraído a `resources/css/clientes.css`; el bloque `<script>` de esa vista sigue inline (pendiente, ver [pendientes.md](pendientes.md)). Todo CSS nuevo va en `resources/css/<modulo>.css` y se carga con `@push`.
3. **Sin estilo duplicado**: si un módulo tiene CSS propio (ej. `resources/css/clientes.css`), no re-declarar los mismos estilos dentro de la blade. Clases o mejor: componentes compartidos para modales/tablas si el patrón se repite en 3+ módulos.
4. **IDs de DOM únicos** y data-attributes en vez de `onclick` con lógica:
```blade
<!-- mal -->
<button onclick="registrarIngreso($producto->codigo)">

<!-- bien -->
<button data-codigo="<?= $producto->codigo ?>" data-accion="ingreso">
```
(en JS, un listener por `[data-accion]`). Los handler de el JS acceden por `dataset`, nunca asumen que la blade imprimió PHP.
5. Flash de espera/validación: **nunca `dd($errors)`** en producción — mostrar los mensajes con `@error` o `@if($errors->any())`.
6. URLs: usar `route('nombre')` siempre que exista una ruta nombrada; `url()` solo para anclas de sección propias.
7. Cada modelo `href="#"` (enlace sin destino) es pendiente: retirarlo o darle destino real.

## Frontend/JS

- Un archivo JS por módulo en `resources/js/`, registrado como input en `vite.config.js`. Nombres correctos (`usuarios.js`, no `usuarioos.js`).
- La comunicación con backend es POST estándar de Blade o `fetch()` con el token de `meta[name=csrf-token]` (ya incluido en `layouts/app.blade.php`) → usar el header `X-CSRF-TOKEN`.
- URLs en JS: preguntar la base a data-attributes de la vista o usar helpers; no hardcodear `/clientes/${rut}` si la ruta puede cambiar.

## Base de datos y modelos

1. **Soft deletes**: si la tabla tiene `deleted_at`, el modelo declara `use SoftDeletes` — prohibido el `whereNull('deleted_at')` manual (duplicado en ~10 lugares hoy).
2. Si la tabla tiene PK no estándar, declararla (`$primaryKey`) y usar claves correctas en las relaciones (error actual: `ImagenProducto::producto()` apunta a `Producto.id`, que no existe).
3. Manejo de errores: el handler global ya persiste todo a `log_errors`; los controladores solo deciden la respuesta (redirect + flash).
4. Sin timestamps (`$timestamps = false`)? Entonces no esperar `created_at`/`updated_at` en queries — la tabla `Salida` ya lo hace bien.

## Roles y permisos

1. **Toda ruta administrativa** debe entrar al grupo con el middleware de rol, no depende del sidebar:
```php
Route::middleware(['auth', 'role:Administrador'])->group(...);
```
2. Usar el alias `role:` (registrado en `bootstrap/app.php`), no `CheckRole::class` —  ver `routes/web.php` (mezcla actual).
3. Los roles válidos deben tener una lista canónica (enum o tabla) y el select de usuarios se llena de ahí.

## Commits y ramas

- Mensajes cortos en español, describiendo el cambio (ej. "Creación apartado categorías", "Modificación historial"). Preferir el imperativo ("Agrega listado de X").
- Rama por feature desde `main` + Pull Request (flujo actual del equipo: `cambios-elias`, `recuperacion-categorias-domingo`).
- No commitear `.env`, `node_modules` ni dumps grandes de BD ya cubiertos por `.gitignore`.

## Estilo / formato

- Pint disponible (`composer.json` requiere `laravel/pint`): correr `vendor/bin/pint` sobre archivos propios antes de PR.
- `.editorconfig` presente: respetar indent y final newline.
- En Blade, las etiquetas HTML se cierran y no se imprimen `PHP` crudo sin `{{ }}` (ver ejemplo onclick arriba).
