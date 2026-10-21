# Arquitectura

## Stack tecnológico

| Capa | Tecnología | Versión / nota |
| --- | --- | --- |
| Backend | Laravel | 13.x (`laravel/framework ^13.17`, instalado v13.33) |
| Lenguaje | PHP | >= 8.4 (ver cherry de incoherencia en [instalacion.md](instalacion.md)) |
| Base de datos | MySQL | 8.x — esquema gestionado por SQL manual, no por migraciones |
| Frontend | Bootstrap | 5.3 (dependencia npm) |
| CSS adicional | Tailwind CSS | 4.x vía `@tailwindcss/vite` |
| Assets | Vite | 8.x (`laravel-vite-plugin`) |
| Test framework | Pest | 5.x (solo tests stub) |
| Servidor dev | `php artisan serve` + `concurrently` | - |

> **Nota:** conviven Bootstrap y Tailwind. Eso es intencional (distintos módulos usaron stacks distintos) pero conviene no mezclarlos dentro de una misma vista. Ver [convenciones.md](convenciones.md).

> `spatie/laravel-permission` está en `composer.json` pero **no se usa**: el control de roles es casero vía columna `Usuario.rol` + middleware `CheckRole`.

## Estructura de carpetas

```
app/
  Http/
    Controllers/          # 15 controladores (algunos muertos — ver pendientes.md)
      Auth/               # Login.php y Logout.php (invocables)
    Middleware/           # CheckRole.php (roles caseros)
  Models/                 # 19 modelos Eloquent
  Providers/              # AppServiceProvider
bootstrap/
  app.php                 # Bootstrap 3.x: rutas, middleware alias, handler global de excepciones
config/                   # Config Laravel estándar (app.php sin personalizar: name 'Laravel', locale 'en', tz UTC)
database/
  migrations/             # 5 migraciones: stock Laravel + usuarios + log_errors (NO cubre tablas de negocio)
  seeders/                # UsuarioSeeder (hardcodeado) y DatabaseSeeder stub
  factories/              # UserFactory stub (incoherente con el modelo User real)
docs/                     # Esta documentación
public/                   # Punto de entrada, assets compilados, imágenes subidas (images/productos/)
resources/
  css/                    # app.css (entrada) + theme/ (tokens y temas) + components/ (compartidos) + un CSS por módulo — ver estilos.md
  js/                     # app.js + venta.js, categorias.js, proveedores.js, producto.js... — algunos vacíos
  views/                  # Vistas Blade por módulo + layouts/ + partials/
routes/
  web.php                 # Todas las rutas del sistema (única entrada de rutas)
  console.php             # Artisan commands
storage/                  # Logs, cache, sesiones
tests/                    # Pest — stubs de ejemplo
viejo/                    # Carpeta vacía (legado, se puede eliminar — ver pendientes.md)
-- raíz --
create table.sql          # Esquema real de negocio (fuente de verdad de la BD)
inserts.sql               # Datos semilla de negocio
procedimientos.sql        # Stored procedures
congelandia_backup.sql    # Dump de tablas de framework (no de negocio)
MWB.mwb                   # Diagrama ER Workbench
```

## ¿Qué patrón se usa?

Patrón estándar MVC de Laravel, **sin servicios ni repositorios**: los controladores concentran la lógica de negocio y consultan los modelos (o hacen queries crudas con `DB::table`/`selectRaw`) directamente. No hay API REST: todo es web tradicional con Blade (más algunos `fetch()` desde JS).

## Flujo de una petición

```
Navegador
  → public/index.php
  → bootstrap/app.php (Application::configure)
      • withRouting: routes/web.php
      • withMiddleware: alias 'role' → CheckRole
      • withExceptions: handler global de excepciones
  → Middleware:
      • 'guest' en rutas de login
      • 'auth' en el grupo del sistema (ver rutas.md)
      • CheckRole:Administrador en el grupo admin (solo crear producto)
  → Controller (métodos concretos; lógica + validación inline)
  → Model / DB (Eloquent o query builder)
  → Blade view (layouts/app.blade.php envuelve las vistas internas con sidebar/header/footer)
```

## Layouts y plantillas

| Layout / partial | Uso |
| --- | --- |
| `layouts/app.blade.php` | **Layout interno usado por el sistema**: carga header, sidebar, footer, `@stack('css')`, `@stack('scripts')`, meta CSRF y muestra flash de error |
| `partials/header` | Navbar superior fijo |
| `partials/sidebar` | Menú lateral; muestra enlaces de admin solo si `rol === 'Administrador'` (cuidado: es solo ocultamiento visual, el backend no restringe salvo crear producto — ver [pendientes.md](pendientes.md)) |
| `partials/footer` | Vacío (shell) |
| `layouts/public.blade.php`, `partials/navbar.blade.php`, `welcome.blade.php` | **Sin uso** (candidatos a eliminación) |
| `public/index.blade.php` | Sitio público, documento HTML standalone (no extiende layout) |

## Manejo de errores

Definido en `bootstrap/app.php`:

1. Se ignoran (pasan al default de Laravel) `AuthenticationException`, `ValidationException` y `NotFoundHttpException`.
2. Para cualquier otra excepción en web (no API):
   - Se inserta una fila en la tabla `log_errors` (mensaje, archivo, línea) vía modelo `LogError`.
   - Se redirige atrás con flash `error` = "Ocurrió un error en el sistema."
3. Varios controladores también envuelven su lógica en `try/catch` propios y redirigen con `->with('error', ...)`.
4. Las vistas internas muestran el flash de error dentro del layout.

Para depurar en desarrollo, revisa la tabla `log_errors` o `storage/logs/laravel.log`.

## Autenticación y roles

- Login invocable `App\Http\Controllers\Auth\Login` compara credenciales **manualmente** contra la tabla `Usuario` (email + contraseña). ⚠️ Compara en texto plano — ver [pendientes.md](pendientes.md).
- `App\Http\Controllers\Auth\Logout` cierra sesión.
- Middleware `App\Http\Middleware\CheckRole` (alias `role`): recibe un rol, llama `Auth::user()->tieneRol($rol)` (comparación exacta sobre `Usuario.rol`) y hace `abort(404)` si no coincide (oculta la ruta como Not Found en vez de 403).
- Roles definidos hasta ahora (columna `rol`, texto libre): `Administrador`, `Cajero` (ver `inserts.sql`).

## Consideraciones Windows vs Linux

Ya se normalizaron los nombres de archivo que dependían del filesystem case-insensitive de Windows:

- `app/Http/Controllers/PosController.php` (archivo == clase)
- `app/Http/Controllers/HistorialController.php` (archivo == clase)
- `app/Http/Controllers/VentaController.php` (antes `SalidaController.php`; clase ya era `VentaController`)
- `resources/views/pos/` (antes `POS/`)
- `resources/js/usuarios.js` registrado en Vite (antes `usuarioos.js`)

Queda un riesgo de **casing de tablas en MySQL**: las migraciones crean `Cliente`, `Categoria`, `Producto`, `Lista_Precio`, `Ingreso`, `Salida`, etc., mientras casi todos los modelos declaran `$table` en minúsculas (`categoria`, `producto`, `ingreso`, `salida`, `detalle_salida`, `tipo_salida`) y varias queries crudas usan minúsculas. En Linux con `lower_case_table_names=0` fallan tanto Eloquent como `DB::table()`/subqueries. Si la BD se importa desde los `.sql` (todo minúsculas), en cambio fallan los modelos con PascalCase (`Cliente`, `Usuario`, `IMAGEN_PRODUCTO`). Detalle y plan en [pendientes.md](pendientes.md) #66.
