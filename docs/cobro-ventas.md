# Confirmación de venta, vuelto y pagos mixtos

Rama: `feat/cobro-confirmado-pagos-mixtos`, basada en `feat/seeders-tipos-movimientos` porque necesita los catálogos iniciales y el ID real de Venta. No depende de la rama de tablas móviles.

Al pulsar Cobrar se abre un modal para revisar productos, total, cliente y métodos. La venta se registra al confirmar. El cliente es opcional. Los pagos deben cubrir el total completo: no se agregan ventas fiadas ni pagos pendientes.

Cada método se usa una vez por venta y guarda su importe aplicado. En efectivo se ingresa por separado el dinero recibido. Los botones suman billetes de $1.000, $2.000, $5.000, $10.000 y $20.000; también se admite un importe manual o el monto exacto. Denominaciones de la [familia actual del Banco Central](https://www.billetesymonedas.cl/Billetes/FamiliaBilletesActuales).

Ejemplo: total $6.500, débito $4.000 y efectivo $2.500. Si se reciben $5.000 en efectivo, el vuelto es $2.500. Los pagos guardados suman $6.500, nunca el importe recibido antes de devolver el vuelto. Los importes se ingresan en pesos enteros.

El servidor vuelve a calcular los precios y verifica métodos activos, importes, cliente y efectivo suficiente dentro de una transacción. Mantiene la validación de precios vigentes; las discrepancias de precio/stock pertenecen a otro grupo pendiente de definición.

Cada solicitud del nuevo modal lleva una clave única y una firma de los datos. Repetir la misma solicitud devuelve la venta original, incluso si cambió el precio después. Otra solicitud con la misma clave devuelve conflicto. La restricción única en la base también cubre peticiones simultáneas. Se conserva compatibilidad con solicitudes anteriores de un método; estas no tienen protección contra duplicados si no envían una clave.

Mientras se envía se bloquean los controles. Ante una respuesta incierta se conservan el carrito y el cobro pendiente en el navegador, asociados al usuario; el reintento usa los mismos datos y clave incluso después de recargar. No se debe borrar el almacenamiento del navegador mientras haya un cobro pendiente.

## Base de datos

La migración agrega `claveOperacion`, `firmaCobro`, `efectivoRecibido` y `vuelto` a `salida`. En desarrollo se aplica con `php artisan migrate`. Las ventas anteriores conservan sus datos y tienen vuelto cero.

Para DirectAdmin sin terminal: respaldar y ejecutar una sola vez [el SQL incremental](../database/sql/2026_11_09_000000_cobro_ventas.sql) mediante phpMyAdmin **antes** de actualizar los archivos del POS. El SQL también registra la migración aplicada. Git no actualiza la base de datos. No volver a importar una base inicial ni ejecutar `composer run setup` en producción. Ver la [guía de despliegue](despliegue-directadmin.md).

Validación: 82 pruebas PHP (429 aserciones), 8 pruebas JavaScript del cálculo, compilación Vite y comprobación del modal en navegador a 375 px con datos ficticios. El backend prueba pagos mixtos, vuelto, rollback de importes inválidos, métodos eliminados, clave reutilizada y repetición después de un cambio de precio.
