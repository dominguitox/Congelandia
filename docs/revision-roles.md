# Autorización administrativa (#11, #12, #13, #64)

Base: `0c10668`. Rama independiente, cambios sin commit.

Usuarios, categorías, proveedores y edición del catálogo exigen `auth` y `role:Administrador`. Reportes también respeta la restricción que ya expresaba el menú. POS, clientes, consulta de inventario y movimientos mantienen acceso autenticado.

Los únicos roles asignables son `User::ROLES`: Administrador y Cajero. Se retira Jefe del selector porque no existe una política para ese rol. No se reescriben usuarios existentes: revisar sus roles antes de desplegar. Se conserva el 404 deliberado de CheckRole para ocultar rutas administrativas.

Prueba: `php artisan test --filter=RolesTest`. Como Cajero, intentar GET/POST/PUT/DELETE de administración: 404 y ninguna escritura. Como Administrador, crear usuarios con ambos roles y rechazar Jefe/texto libre (422 JSON). Revisar menú y acceso directo a URLs. No depende de otras ramas; al integrar tablas Linux, conservar sus nombres de tabla en las reglas de validación.

## Resultados ejecutados (2026-11-06)

- Pest con PHP 8.4.23 y SQLite en memoria: **39 tests, 156 aserciones, todos pasan**.
- `php artisan route:list --json` y `php artisan view:cache`: correctos.
- Build de frontend no repetido: esta rama no modifica entradas JS/CSS de Vite.
- Sintaxis PHP de archivos modificados/nuevos: correcta. Pint ejecutado sobre pruebas nuevas y la nueva migración cuando corresponde.
- No se realizó prueba visual ni interacción real en navegador. Los pasos manuales indicados arriba quedan pendientes para revisión.
- Dependencias existentes reutilizadas localmente; no se modificaron manifiestos/lockfiles ni el .env original. En un checkout limpio instalar con Composer/npm antes de repetir pruebas.
