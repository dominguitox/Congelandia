# Consulta compartida, stock y validación (#26, #36, #37, #55, #63)

Base: `0c10668`. Rama independiente, cambios sin commit.

Producto::conDetalle centraliza stock (ingresos menos salidas), precio vigente, costo/proveedor del último ingreso y vencimiento. POS conserva sus subconsultas de ofertas e imagen. Inventario, dashboard y el show desactivado usan el scope; no se reactiva esa ruta. precioVenta es un alias del precio calculado y desaparece la segunda consulta divergente. ImagenProducto::producto apunta a codigo.

Los modales de ingreso/salida ya mostraban errores; creación/edición ahora muestran resumen, recuperan datos y reabren el formulario correcto. En edición se reconstruye la acción desde la ruta nombrada después de un error.

Prueba: `php artisan test --filter=ProductosStockTest` y suite completa. Cubrir ingresos/salidas, mermas, padres y detalles borrados, productos sin movimientos, selección del precio vigente (descartar futuro/finalizado/borrado), relación inversa de imágenes y validación con retorno al modal. Revisar en navegador el inventario, alertas del dashboard y ofertas del POS. No se cambia la política de cascada de borrado.

No depende de otras ramas. La portabilidad general de tablas sigue siendo #66. Al integrar frontend conservar sus data-attributes de rutas y la reapertura del modal; con limpieza conservar el retiro de index().

## Resultados ejecutados (2026-11-06)

- Pest con PHP 8.4.23 y SQLite en memoria: **24 tests, 115 aserciones, todos pasan**.
- `php artisan route:list --json` y `php artisan view:cache`: correctos.
- `npm run build` con Vite 8.2.1: correcto. Advertencia no bloqueante del plugin de fuentes: fontaine opcional ausente.
- Sintaxis PHP de archivos modificados/nuevos: correcta. Pint ejecutado sobre pruebas nuevas y la nueva migración cuando corresponde.
- No se realizó prueba visual ni interacción real en navegador. Los pasos manuales indicados arriba quedan pendientes para revisión.
- Dependencias existentes reutilizadas localmente; no se modificaron manifiestos/lockfiles ni el .env original. En un checkout limpio instalar con Composer/npm antes de repetir pruebas.
