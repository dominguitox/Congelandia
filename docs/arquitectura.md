# Arquitectura

Descripción contrastada con `0c10668`; ver [auditoría](revision-documentacion.md). No incorpora el trabajo sin commit de reportes de otra sesión.

## Stack y estructura

Laravel 13 con PHP >= 8.4, MySQL 8, Blade, Bootstrap 5, Tailwind 4 y Vite 8. La interfaz comparte tokens, temas y componentes CSS; ver [estilos.md](estilos.md). Pest valida migraciones, páginas públicas y ofertas/POS, además de los ejemplos.

app/Http/Controllers concentra acciones y lógica de negocio; app/Models contiene relaciones Eloquent. database/migrations ya cubre todo el esquema de negocio y framework; database/seeders crea el administrador. routes/web.php define rutas web. resources/views organiza vistas por módulo, layouts y partials; resources/css y resources/js contienen entradas de Vite.

## Flujo

La petición entra por public/index.php y bootstrap/app.php, pasa por middleware, controlador y modelo/query builder y vuelve como Blade, redirect o JSON para fetch. En la base no hay una capa de servicios de negocio versionada. El trabajo sin commit de reportes añade una, pero no forma parte de esta revisión de la base.

Bootstrap registra el alias `role` para CheckRole y configura respuesta JSON según api/* o expectsJson. **No existe el handler global histórico que escribía excepciones en log_errors**: se usa el tratamiento estándar de Laravel, además de los catch de cada controlador. Revisar logs y respuestas específicas, no suponer que todas las excepciones se guardan en la tabla.

## Acceso y vistas

Login invocable busca usuario activo por email y valida contrasena mediante Hash::check; Logout cierra la sesión. User usa Usuario/idUsuario y SoftDeletes. Los roles reales de los datos son Administrador y Cajero; el selector también ofrece Jefe sin política y UserController acepta texto libre (#12/#64).

Las rutas internas exigen auth. Crear producto y gestionar ofertas exigen Administrador; otras rutas administrativas siguen expuestas a cualquier autenticado (#11). CheckRole devuelve 404 por política deliberada. El menú oculta reportes a no administradores, pero en la base esa restricción no existe en servidor.

layouts/app incluye header, sidebar, footer, CSRF y stacks de assets. layouts/public sostiene home, catálogo y contacto. El footer interno vacío sí está incluido; navbar y welcome no tienen consumidores. Modales Bootstrap y overlays manuales conviven (#41).

## Datos y portabilidad

Stock = cantidades de ingresos activos menos cantidades de salidas activas. Los filtros de deleted_at en queries crudas son necesarios; los redundantes en Eloquent siguen como deuda. No hay cascada lógica definida para todas las relaciones. La consulta de producto se repite en inventario/POS/dashboard (#36/#37).

Las migraciones usan PascalCase y varios modelos/queries minúsculas; Linux con lower_case_table_names=0 exige normalizar el conjunto (#66). Los nombres de archivos/clases/vistas ya fueron corregidos (#15–#19). Los SQL raíz quedan como legado: las migraciones son la vía de instalación.

Spatie está instalado y no participa en los roles; laravel/pao es una dependencia real de desarrollo para salida de pruebas optimizada para agentes, distinta de Pail. La configuración base aún usa UTC/en (#34).
