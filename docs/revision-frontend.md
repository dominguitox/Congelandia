# Rutas del frontend y script de clientes (#38, #43, #44)

Base: `0c10668`. Rama independiente, cambios sin commit.

El JavaScript de clientes se carga como entrada de Vite y se retira su toggle duplicado de app.js. Los handlers usados por onclick permanecen explícitamente en window. Las acciones dinámicas usan URLs generadas por route() en data-attributes, incluida la edición de ofertas; el sidebar usa rutas nombradas y routeIs. Se mantienen los modales y las clases visuales actuales. /admin conserva su URL con el nombre dashboard.alias; /dashboard mantiene dashboard.index. Se retira la declaración duplicada de reportes, de modo que las rutas se pueden cachear sin nombres repetidos.

Prueba: `npm run build`, `node --check resources/js/clientes.js`, `php artisan view:cache`. En clientes: abrir/cerrar formulario, buscar, editar, consultar historial, seleccionar venta y registrar pago. En inventario/categorías/proveedores: abrir edición, guardar y cancelar/eliminar. En POS: registrar venta y comprobar la URL de red. Repetir con APP_URL que incluya el prefijo de despliegue y códigos/RUT codificados. Se necesita validación manual del navegador.

Sin dependencia de otras ramas. Al integrar roles, conservar su filtrado administrativo del menú; con productos/stock conservar la lógica de validación de modales. Los archivos compartidos pueden requerir resolver conflictos.

## Resultados ejecutados (2026-11-06)

- Pest con PHP 8.4.23 y SQLite en memoria: **14 tests, 69 aserciones, todos pasan**.
- `php artisan route:list --json`, `php artisan route:cache` y `php artisan view:cache`: correctos. La caché de rutas se limpió después de verificarla.
- `npm run build` con Vite 8.2.1: correcto. Advertencia no bloqueante del plugin de fuentes: fontaine opcional ausente.
- node --check: clientes, producto, venta, proveedores, categorías y app correctos.
- No se realizó prueba visual ni interacción real en navegador. Los pasos manuales indicados arriba quedan pendientes para revisión.
- Dependencias existentes reutilizadas localmente; no se modificaron manifiestos/lockfiles ni el .env original. En un checkout limpio instalar con Composer/npm antes de repetir pruebas.
