# Retiro de código sin uso (#33, #45, #47, #49, #50, #52–#54, #56, #59)

Base: `0c10668`. Rama independiente, cambios sin commit.

La búsqueda global de referencias confirmó que estos controladores, modelos y vistas no participan en rutas, relaciones ni includes activos. Se eliminan también error.js (placeholder) y usuarios.js (vacío), junto con su entrada Vite y carga Blade. Se retira ProductoController::index sin ruta.

Se conservan ImagenProducto (sí se utiliza al cargar y guardar imágenes), partials/footer (incluido por el layout), procedimientos SQL y el respaldo SQL. `viejo/` no está versionado. Se conserva laravel/pao: su composer.json confirma que es un paquete real para salida de pruebas optimizada para agentes, distinto de Pail. La decisión sobre Spatie se mantiene abierta; no se cambian dependencias ni lockfiles.

Prueba: `php artisan route:list`, `php artisan view:cache`, `npm run build`, suite Pest. Revisar usuarios, creación de producto y navegación. No depende de otras ramas; puede coincidir con roles (imports/rutas/vista de usuarios) y con consulta de productos (el método index eliminado).

## Resultados ejecutados (2026-11-06)

- Pest con PHP 8.4.23 y SQLite en memoria: **14 tests, 69 aserciones, todos pasan**.
- `php artisan route:list --json` y `php artisan view:cache`: correctos.
- `npm run build` con Vite 8.2.1: correcto. Advertencia no bloqueante del plugin de fuentes: fontaine opcional ausente.
- Sintaxis PHP de archivos modificados/nuevos: correcta. Pint ejecutado sobre pruebas nuevas y la nueva migración cuando corresponde.
- No se realizó prueba visual ni interacción real en navegador. Los pasos manuales indicados arriba quedan pendientes para revisión.
- Dependencias existentes reutilizadas localmente; no se modificaron manifiestos/lockfiles ni el .env original. En un checkout limpio instalar con Composer/npm antes de repetir pruebas.
