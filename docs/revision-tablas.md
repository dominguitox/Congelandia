# Tablas portables para Linux (#66)

Base: `0c10668`. Rama independiente, cambios sin commit.

El esquema canónico usa minúsculas snake_case en migraciones, modelos, validaciones, queries, tests y SQL de referencia. Las clases PHP mantienen PascalCase. Las columnas y claves primarias conservan su nombre.

En instalaciones nuevas, las migraciones crean los nombres canónicos. En instalaciones existentes, la migración 2026_11_06 normaliza los nombres sin recrear tablas ni borrar filas; comprueba colisiones antes del primer rename y usa un nombre intermedio para cambios solo de casing. Los motores que ya devuelven nombres en minúscula no requieren renombrado. Nunca se debe ejecutar migrate:fresh sobre la BD existente.

Despliegue: respaldar datos/esquema, detener escrituras, ejecutar `php artisan migrate --force` y probar login, catálogo, POS, inventario, historial, clientes, proveedores y reportes. La migración no revierte nombres automáticamente: para volver a código anterior, restaurar el respaldo. MySQL hace DDL con commits implícitos; un fallo durante el rename requiere inspeccionar los nombres `_normalizando_*` y restaurar o completar el paso antes de reanudar. No se cambia la configuración global lower_case_table_names.

Pruebas: suite Pest en SQLite (migración nueva, conservación de filas/FK y modelos) y, antes de producción, suite en MySQL Linux con `SELECT @@lower_case_table_names` igual a 0 sobre una BD exclusiva de pruebas. Las pruebas de migración usan migrate:fresh; jamás apuntarlas a datos reales. SQLite no demuestra sensibilidad al casing ni comportamiento DDL de MySQL.

No depende de otras ramas. Al integrar cambios de reportes de la sesión previa, normalizar también sus consultas nuevas. Con las demás ramas conservar nombres minúsculos en nuevas pruebas, reglas y queries; habrá posibles conflictos en archivos compartidos.

## Resultados ejecutados (2026-11-06)

- Pest con PHP 8.4.23 y SQLite en memoria: **17 tests, 122 aserciones, todos pasan**.
- `php artisan route:list --json` y `php artisan view:cache`: correctos.
- Build de frontend no repetido: esta rama no modifica entradas JS/CSS de Vite.
- Sintaxis PHP de archivos modificados/nuevos: correcta. Pint ejecutado sobre pruebas nuevas y la nueva migración cuando corresponde.
- MySQL Linux **no ejecutado**: Docker CLI está instalado, pero no hay motor Docker Linux accesible (pipe dockerDesktopLinuxEngine inexistente). No se descargaron imágenes ni se arrancaron servicios. SQLite no sustituye esta prueba. Las migraciones de compatibilidad de soft deletes/activa conservan los nombres antiguos para permitir upgrades previos a la normalización.
- No se realizó prueba visual ni interacción real en navegador. Los pasos manuales indicados arriba quedan pendientes para revisión.
- Dependencias existentes reutilizadas localmente; no se modificaron manifiestos/lockfiles ni el .env original. En un checkout limpio instalar con Composer/npm antes de repetir pruebas.
