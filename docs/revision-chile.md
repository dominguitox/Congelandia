# Fecha e idioma de Chile (#34)

Base: `0c10668`. Rama independiente, cambios sin commit.

La aplicación usa America/Santiago y locale es por defecto; las variables APP_TIMEZONE/APP_LOCALE permiten sobrescribirlos. Faker usa es_CL. El entorno existente no se modifica.

En cada instalación, ajustar `.env`: APP_TIMEZONE=America/Santiago, APP_LOCALE=es, APP_FALLBACK_LOCALE=es, APP_FAKER_LOCALE=es_CL; ejecutar `php artisan config:clear` y regenerar la caché de configuración si corresponde. Revisar dashboard cerca de medianoche y límites de vigencia de ofertas/vencimientos. No convierte fechas históricas: confirmar si los registros existentes se guardaron en UTC o en hora local antes del despliegue. Laravel no incluye traducciones completas de validación al español: este cambio configura el idioma, no agrega un catálogo de traducciones.

Sin dependencias con otras ramas.

## Resultados ejecutados (2026-11-06)

- Pest con PHP 8.4.23 y SQLite en memoria: **14 tests, 69 aserciones, todos pasan**.
- `php artisan route:list --json` y `php artisan view:cache`: correctos.
- Build de frontend no repetido: esta rama no modifica entradas JS/CSS de Vite.
- Sintaxis PHP de archivos modificados/nuevos: correcta. Pint ejecutado sobre pruebas nuevas y la nueva migración cuando corresponde.
- No se realizó prueba visual ni interacción real en navegador. Los pasos manuales indicados arriba quedan pendientes para revisión.
- Dependencias existentes reutilizadas localmente; no se modificaron manifiestos/lockfiles ni el .env original. En un checkout limpio instalar con Composer/npm antes de repetir pruebas.
