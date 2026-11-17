# Plan de pruebas del modo offline

## Objetivo y aislamiento

Comprobar captura, persistencia, sincronización y recuperación de ventas e inventario con datos temporales. Las pruebas Laravel usan exclusivamente SQLite en memoria definida en `phpunit.xml`; cada caso reconstruye su esquema y no modifica la base MySQL de desarrollo. Las pruebas de cola usan IndexedDB simulada en memoria. No ejecutar las pruebas contra una base con datos que se deban conservar.

## Matriz

| Área | Escenarios | Resultado esperado |
| --- | --- | --- |
| Preparación | Sesión, permisos, copia y datos privados | Copia mínima; usuario no autorizado rechazado |
| Sin conexión | Venta, ingreso y merma; cantidades inválidas; varias líneas | Persistencia atómica y stock local no negativo |
| Datos cambiados | Producto eliminado, método de pago eliminado, cliente o proveedor retirado, precio cambiado | Conservar originales; no crear movimientos parciales |
| Stock compartido | Otro equipo consume una parte o todo antes de sincronizar; datos locales alterados | Conflicto explícito sin venta, pago ni stock negativo |
| Reintentos | Respuesta perdida, UUID repetida, modificación de la carga, fallos de red y servidor | Misma operación sin duplicados; detener cola al primer error |
| Secuencia | Varias operaciones pendientes, ingreso seguido de venta, conflicto en medio | Orden conservado; las posteriores esperan |
| Seguridad | Usuario distinto o desactivado; identificadores y pagos inválidos | Rechazo y ausencia de escrituras parciales |
| Recuperación | Reinicio del navegador, descarga con pendientes, cambio de cuenta | Conservar cola y borradores; no reemplazar copia pendiente |
| Recursos | Service Worker y caché, recarga sin red, actualización de versión | Solo recursos públicos; no exponer respuestas privadas |

## Ejecución

1. Ejecutar `php artisan test tests/Feature/OfflineTest.php` y las pruebas relacionadas con cobro e inventario.
2. Ejecutar `node --test tools/offline/*.test.mjs tools/pos/cobro.test.mjs`.
3. Ejecutar la suite Laravel completa y la compilación, si el entorno lo permite.
4. En navegador, con una base de demostración separada, descargar copia, desconectar, registrar varias operaciones, modificar stock o catálogos desde otro equipo, reconectar y revisar la cola y el estado oficial. Repetir recarga offline y pérdida de respuesta. Registrar navegador, datos usados y resultados. Las pruebas automatizadas no sustituyen este paso.

## Hallazgos y límites

- Se corrigieron los mensajes genéricos `validation.exists` para clientes, proveedores, métodos de pago y tipos de venta retirados. El rechazo conserva la cola local y no registra un movimiento parcial.
- Los conflictos y rechazos detienen la cola y no existe una interfaz para resolver o descartar una operación individual. La descarga de copia también queda bloqueada. Es una decisión de producto pendiente; descartar ventas requiere comprobar si el servidor las confirmó y cómo corregir el stock local y las operaciones posteriores.
- SQLite y IndexedDB simulada no demuestran concurrencia real entre procesos MySQL ni comportamiento de todos los navegadores; CI con MySQL y una prueba manual de varios equipos siguen siendo necesarias.
- El módulo conserva la fecha de captura como dato de auditoría; el movimiento oficial usa la fecha de sincronización.

## Ejecución del 16 de noviembre de 2026

- Laravel: 63 pruebas focalizadas y 362 aserciones correctas; suite completa: 198 pruebas y 1.304 aserciones correctas. SQLite en memoria.
- Cola, Service Worker y cobro: 35 pruebas correctas con IndexedDB simulada. Compilación Vite correcta, con aviso de dependencia opcional `fontaine`.
- Navegador integrado: en una base SQLite temporal se confirmó la descarga del producto de prueba, el cambio a estado sin conexión, agregarlo al carrito y abrir la confirmación de venta. La automatización no consiguió confirmar la venta mediante los controles del diálogo; no se comprobó el guardado, la recarga ni la reconexión en navegador. Las pruebas automáticas de cola sí cubren persistencia y reconexión simuladas.
- El primer servidor iniciado con `artisan serve` mostró el catálogo de la conexión habitual: se detuvo sin registrar ventas ni movimientos. Se inició sesión en ese servidor antes de detectar el problema. Para la prueba parcial se usó después el servidor PHP con SQLite explícito y se verificó el catálogo aislado.
- La prueba manual con dos equipos, la pérdida de respuesta real y la concurrencia MySQL permanecen pendientes.
