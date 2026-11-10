# Usuarios e historial de acciones

Rama `feat/usuarios-historial-acciones`, independiente desde `main`. Agrupa la corrección de los botones del módulo Usuarios y la funcionalidad de auditoría.

## Usuarios

Los administradores pueden desactivar y reactivar usuarios. La cuenta se conserva, con sus ventas y demás relaciones. No se permite desactivar la cuenta propia ni dejar el sistema sin administradores activos. Las operaciones usan bloqueo de filas para evitar cambios contradictorios entre administradores.

Una cuenta inactiva no puede iniciar sesión. Si tenía una sesión abierta, su siguiente petición invalida la sesión y rechaza la operación; en navegador vuelve al login y en JSON responde 401. La reactivación permite iniciar sesión nuevamente. Repetir un estado que ya estaba aplicado no genera otra entrada de modificación.

## Historial

El menú **Historial de acciones** abre `/acciones`. El botón de historial de cada usuario abre el mismo módulo filtrado por las acciones que realizó esa persona. Solo los administradores pueden consultarlo o cambiar estados de usuarios.

La consulta permite filtrar por usuario (incluidos inactivos/eliminados), módulo y fechas inclusivas. Muestra 25 registros por página, conserva los filtros al cambiar de página y ordena por fecha e ID descendentes. El nombre del actor se conserva como dato histórico.

Se registran accesos y cierres de sesión exitosos y operaciones de escritura de usuarios, productos, ingresos, mermas, ventas, clientes, pagos, proveedores, categorías y ofertas. La lista explícita de rutas vive en [RegistrarAcciones](../app/Http/Middleware/RegistrarAcciones.php). Los cambios y su registro se guardan en la misma transacción: si falla el registro, se revierte el cambio de negocio.

Cada entrada guarda fecha, ID y nombre del actor, acción, módulo, ruta y referencia al objeto cuando está disponible. No se guardan formularios completos, contraseñas, hashes, tokens ni cabeceras. Los rechazos y errores no aparecen como acciones exitosas; las consultas GET no generan entradas. Los reintentos que el cobro identifica como repetidos tampoco crean otra acción.

El historial comienza al instalar esta funcionalidad; no reconstruye acciones anteriores ni registra procesos CLI/seeders. La aplicación no ofrece edición ni eliminación de entradas.

## Base de datos y verificación

La migración crea `registro_acciones`. En desarrollo se aplica con `php artisan migrate`. En DirectAdmin sin terminal, respaldar e importar una sola vez [el SQL incremental](../database/sql/2026_11_09_010000_registro_acciones.sql) mediante phpMyAdmin **antes de actualizar los archivos**, porque el middleware necesita esa tabla. El script registra también la migración. Git no modifica la base de datos; no usar `composer run setup` en producción. Ver [despliegue](despliegue-directadmin.md).

Se validaron desactivación/reactivación, sesiones inactivas, permisos, filtros, paginación, conservación de nombres históricos, omisión de secretos y reversión ante fallos de auditoría, junto con la suite existente: 88 pruebas y 432 aserciones. La compilación Vite pasó. La interfaz se comprobó en navegador a 320, 375 y 1280 px con datos ficticios y SQLite en memoria.
