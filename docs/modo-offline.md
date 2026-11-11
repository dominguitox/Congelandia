# Modo offline provisional

## Alcance y preparación

Permite ventas POS, ingresos para productos existentes y salidas por **Merma por Vencimiento** o **Merma por Daño**. Las ventas deben pagarse completas; admite pagos mixtos, efectivo recibido y vuelto. No crea productos, proveedores, clientes ni categorías, no ofrece fiados y no habilita reportes ni administración offline.

1. Publicar los recursos compilados con Vite y acceder mediante HTTPS (localhost también funciona para desarrollo).
2. Iniciar sesión con conexión en el navegador y computador que se utilizarán durante el corte.
3. En el panel **Trabajar sin internet**, pulsar **Descargar datos** y esperar la confirmación. La dirección **Servidor → Este equipo** indica que se descargan productos, precios y stock.
4. Abrir **Abrir ventas e inventario sin internet**. El navegador puede ofrecer **Instalar aplicación**; también se puede abrir `/offline` sin instalarla.

El primer inicio no funciona sin internet. El navegador necesita descargar tanto los recursos como una copia de datos. Se solicita almacenamiento persistente cuando la API está disponible; el navegador puede rechazar la solicitud. No borrar los datos del sitio ni utilizar una sesión privada para conservar movimientos a largo plazo.

## Durante el corte

- Abrir `/offline`. Si falla la carga de `/pos` o `/inventario`, el Service Worker muestra la misma pantalla mínima. También se usa ante respuestas 5xx en esas navegaciones.
- En **Vender**, seleccionar productos, cantidades, cliente existente opcional y método de pago. El pago único toma el total automáticamente; **Dividir entre dos medios de pago** permite repartirlo. El efectivo recibido es un solo campo: vacío significa pago exacto y un importe mayor calcula el vuelto. Revisar antes de guardar.
- En **Ingresos y salidas**, seleccionar el movimiento y un producto existente. Los ingresos conservan cantidad, precio unitario de compra, vencimiento y proveedor opcional. Las mermas conservan cantidad y uno de los dos motivos existentes. Al guardar se limpia el formulario y se muestra la confirmación con el stock actualizado.
- La confirmación guarda en IndexedDB el movimiento y el stock provisional en una sola transacción. El carrito, los pagos y el formulario de movimientos se conservan como borradores tras recargar.
- Un movimiento de inventario conserva el carrito de una venta en preparación. Solo confirmar la venta limpia ese carrito.
- El estado muestra la disponibilidad de red/servidor, la fecha de la copia y los movimientos pendientes. **Movimientos guardados** permite ver los datos originales y descargar una copia JSON, incluidos los borradores. La descarga es para revisión; no existe una función de importación o aplicación manual automática.

El stock local **no reserva existencias**. Otros computadores pueden registrar movimientos y la copia puede quedar desactualizada. Los precios y promociones corresponden a la preparación de esa copia. Una promoción que termina durante el corte puede producir un conflicto al sincronizar; no se modifica la venta original.

## Reconexión, reintentos y conflictos

Al abrir el módulo o detectarse la reconexión se intenta enviar los pendientes. También está disponible **Enviar pendientes (cantidad)**, con la dirección **Este equipo → Servidor**. Este botón registra los movimientos locales en el sistema; **Descargar datos** solo actualiza la copia de productos, precios y stock. Sin pendientes, el envío queda deshabilitado. Con pendientes, la descarga queda bloqueada para conservarlos. No depende de Background Sync.

Las operaciones se envían una a una, en orden de guardado. Cada una conserva UUID, usuario, fecha local y datos originales. La fecha local se registra para revisión; los movimientos oficiales usan la fecha del servidor al sincronizar. El vencimiento de un ingreso se valida contra la fecha local de su captura, para permitir sincronizarlo días después.

| Estado | Comportamiento |
| --- | --- |
| Pendiente | Sin confirmación; una respuesta perdida, un corte o un 5xx conservan la misma UUID y todos los datos. |
| Sesión | Iniciar sesión con el mismo usuario y volver a reintentar. No se envía la cola de otro usuario. |
| Rechazada | Error de validación conservado con su respuesta. La cola se detiene en esa operación. |
| Conflicto | Diferencia de stock/precio, producto retirado o clave utilizada con otros datos. La cola queda detenida para revisión. |
| Sincronizada | El servidor confirmó esa UUID. El registro local conserva la respuesta y no vuelve a descontar stock. |

El servidor compara el stock anterior a cada operación con su stock oficial. Si difiere, o cambia el precio efectivo de una venta, guarda la operación original en `operaciones_offline` con estado conflicto, sin crear ingresos, salidas o pagos. No ajusta precios, cantidades ni métodos, ni define cómo resolver la discrepancia. Las validaciones rechazadas también permanecen completas en el navegador.

**No se implementa resolución de conflictos**. Conservar el equipo y la copia descargada para revisar el movimiento y definir la decisión de negocio antes de intervenir. No volver a registrar la misma venta como una venta nueva para intentar superar el conflicto.

La cola bloqueada impide actualizar la copia local. Una vez sincronizados todos los movimientos se puede actualizar el catálogo. Las operaciones confirmadas conservan su historial local.

Si faltan las tablas o columnas requeridas, el servidor responde con un aviso de actualización de base de datos pendiente y conserva la cola. La pantalla no muestra el SQL interno. Se aplican los SQL incrementales indicados más abajo antes de reintentar.

Los datos locales aparecen antes de consultar la sesión del servidor. Los cambios de conexión y las confirmaciones de envío no reconstruyen el formulario en edición. Una nueva versión descarga sus recursos completos antes de activarse y ofrece **Actualizar pantalla**; recargar conserva IndexedDB, borradores e historial.

## Usuario, sesión y almacenamiento

- Cada cuenta conserva su propia copia, borrador y cola en este navegador. Al iniciar sesión con otra cuenta se muestra su asociación, sin enviar la cola anterior.
- Cerrar sesión retira la asociación activa, **sin borrar las colas**. Para retomarlas, iniciar sesión con el usuario original. Otras pestañas reciben la actualización.
- No se guardan contraseñas, cookies ni tokens en IndexedDB o localStorage. El CSRF se solicita mediante la sesión vigente y solo se mantiene en memoria durante el envío.
- La copia contiene productos, precios/promociones aplicables, stock, fecha de actualización, métodos de pago y únicamente identificador/nombre de clientes y proveedores existentes.
- Sin conexión no se pueden comprobar desactivaciones ni cambios de permisos. El servidor vuelve a comprobar la sesión y el estado del usuario al reconectar.
- En el POS online también se conservan borradores y cobros inciertos en IndexedDB. Si no hay copia offline preparada, un cobro incierto se reintenta desde el POS con su clave original; no se transforma en otra venta offline.
- localStorage conserva solo preferencias existentes de productos fijados y temas. El antiguo carrito se migra a IndexedDB después de guardarlo correctamente; no se escriben transacciones nuevas allí.

## Implementación

- [Pantalla mínima](../resources/views/offline/index.blade.php), [interfaz](../resources/js/offline/index.js) y [estilos](../resources/css/offline.css).
- [IndexedDB y transacciones locales](../resources/js/offline/store.js), [sincronización](../resources/js/offline/sync.js) y [registro de la PWA](../resources/js/offline/register.js).
- [Service Worker](../resources/js/offline/worker.js) generado por [PwaController](../app/Http/Controllers/PwaController.php) usando el manifiesto de Vite. Solo precarga el HTML genérico y sus recursos explícitos. Nunca guarda HTML autenticado, respuestas privadas ni el endpoint de sesión en CacheStorage. Cambiar de versión de la PWA no borra IndexedDB.
- [Catálogo mínimo](../app/Services/CatalogoOffline.php) y [operaciones transaccionales](../app/Services/SincronizarOffline.php). El servidor usa las consultas compartidas de stock que excluyen padres y detalles eliminados.
- UUID única en la tabla de operaciones, firma de todos los datos originales, bloqueo del usuario y productos, y escritura del movimiento, resultado y auditoría en una misma transacción. Un reintento confirmado devuelve el resultado original antes de revalidar catálogos que pudieran haber cambiado.
- [Cobro compartido](../app/Services/CobroVenta.php): integra el commit `81f0ea1` de pagos mixtos y vuelto mediante cherry-pick, conservando los productos fijados de `main`. Se rechazan los métodos fiados también en el servidor.
- Se mantiene el flujo online. No se modifica la rama `deploy` ni se versionan `vendor/` o `public/build/`.

## Migraciones y revisión previa a una futura publicación

Esta tarea no despliega. En desarrollo se aplican únicamente migraciones incrementales con `php artisan migrate`. Para un servidor sin terminal, respaldar la base e importar mediante phpMyAdmin, en este orden y solo si falta cada cambio:

1. [Pagos mixtos y vuelto](../database/sql/2026_11_09_000000_cobro_ventas.sql): columnas en `salida` e identificador único del cobro.
2. [Operaciones offline](../database/sql/2026_11_10_000000_operaciones_offline.sql): tabla de confirmaciones/conflictos, datos originales y auditoría temporal. Requiere el esquema de `main`, incluyendo el proveedor opcional en ingresos.

Las migraciones equivalentes son [cobros](../database/migrations/2026_11_09_000000_add_cobro_to_salida_table.php) y [operaciones offline](../database/migrations/2026_11_10_000000_create_operaciones_offline_table.php). No se modifica ni elimina información previa. Git no actualiza la base de datos.

Para una publicación futura, seguir la [guía DirectAdmin](despliegue-directadmin.md): elegir una versión SemVer nueva, ejecutar **Preparar despliegue DirectAdmin** desde `main`, mantener la elección configurada de `public_html` o `public`, revisar Release, CHANGELOG y rama `deploy`, y efectuar **Fetch → Deploy**. Conservar manualmente `.env`, `APP_KEY`, credenciales MySQL, `storage/` y fotos. No usar `composer run setup` en producción: ejecuta `migrate:fresh`.

Después de esa publicación probar `/version-info`, `/pos` (productos fijados, ofertas, cobro simple/mixto y vuelto), `/inventario` (ingresos y ambos motivos de merma), `/offline` (preparación, desconexión, recarga, reconexión y cola), `/historial`, `/acciones` y el historial del cliente si se asignó alguno. Restaurar archivos no revierte la base; conservar las colas al restaurar una versión.

## Validación

```sh
node --test tools/offline/*.test.mjs tools/pos/cobro.test.mjs
php artisan test
npm run build
```

Ejecutar Laravel únicamente con la base de pruebas configurada: las pruebas existentes reinician ese esquema. La validación local usa SQLite en memoria; la demostración de navegador utiliza una base SQLite nueva y separada de cualquier base del usuario. CI incluye SQLite y MySQL.

Las pruebas cubren persistencia al cerrar/reabrir IndexedDB, orden ingreso/venta/merma, escrituras concurrentes, rollback local, respuesta perdida, 401/403/419/422/5xx, cola bloqueada, aislamiento por usuario, reintentos idempotentes, pagos mixtos, conflictos de stock/precio y reglas de caché del Service Worker. Las pruebas de stock existentes se conservan.

Comprobación en navegador: preparar desde el POS online, desconectar la red, recargar con carrito/pagos, confirmar venta con vuelto, ingresar mercadería y registrar merma, recargar conservando tres operaciones pendientes, reconectar y comprobar las tres confirmadas en el orden original. Comprobar también que CacheStorage contiene solo la pantalla genérica, manifiesto, iconos y estáticos declarados.

### Revisión manual del flujo simplificado (11 de noviembre de 2026)

Realizada mediante los botones y formularios del navegador sobre SQLite de demostración, separada de la base del usuario:

- Con stock inicial 26, desconectar, ingresar 5 unidades sin proveedor y retirar 2 por daño: stock local 29 y dos pendientes. Los campos del ingreso quedan vacíos al guardar.
- Recargar sin red: siguen los dos movimientos, el stock 29 y la pestaña de inventario. Reconectar y pulsar **Enviar pendientes**: ambos quedan **Enviado**; el inventario oficial muestra 29.
- Desde el inventario online, ingresar 3 y retirar 1 por vencimiento: stock oficial 31. Descargar de nuevo antes del siguiente corte.
- Sin red, intentar retirar 999: se rechaza sin crear movimiento. Retirar 1 por vencimiento y vender 1 por $1.500, recibiendo $2.000: vuelto $500 y stock local 29. Reconectar: envío automático de ambos.
- En el POS online, vender 1 por $1.500 con $500 en efectivo y $1.000 en débito, recibiendo $1.000: vuelto $500. Otra venta con el efectivo recibido vacío confirma el pago exacto con vuelto cero. Ambas aparecen una sola vez en el historial.
- Una actualización de recursos ofrece **Actualizar pantalla** y conserva el historial local al recargar.
