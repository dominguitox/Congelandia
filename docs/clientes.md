# Creación y listado de clientes

La rama `fix/clientes-sin-compras` corrige clientes que se guardaban pero no aparecían en el listado. La causa era la clasificación del controlador: solo mostraba clientes con deuda o con un pago registrado, dejando fuera a los clientes nuevos.

## Comportamiento

- **Deudas activas:** clientes cuyo saldo calculado es mayor que cero.
- **Clientes sin deuda:** todos los demás clientes activos, incluidos quienes aún no tienen compras ni pagos. Sus datos de último pago muestran «Sin registro» cuando no existen.
- El alta muestra un mensaje de éxito. Si la validación falla, mantiene abierto el formulario, conserva los datos e indica el error en español (incluido un RUT duplicado).
- Editar, consultar historial y eliminar están disponibles también para clientes sin compras. Los clientes eliminados lógicamente no aparecen.

No cambia la fórmula de deuda ni la política de eliminación. No requiere migraciones, seeders ni volver a crear clientes: los que ya estaban guardados y ocultos aparecen con la corrección.

## Prueba manual

1. Iniciar sesión y abrir `/clientes`.
2. Crear un cliente con RUT único, nombre y teléfono. Debe aparecer inmediatamente en «Clientes sin deuda», con deuda cero y sin registro de pago.
3. Buscarlo por nombre o teléfono y comprobar que se puede editar y abrir su historial vacío.
4. Intentar crear otro con el mismo RUT: debe aparecer el error, sin guardar un duplicado y sin perder los datos ingresados.
5. Verificar que un cliente con compra pendiente permanezca en «Deudas activas» y que uno con compra pagada esté en «Clientes sin deuda».
6. Eliminar el cliente de prueba y comprobar que desaparece del listado.

## Verificación automatizada

[ClientesListadoTest](../tests/Feature/ClientesListadoTest.php) cubre creación y visibilidad, clasificación de saldos, clientes eliminados, edición/eliminación sin compras y errores de validación. Las pruebas usan SQLite en memoria; no ejecutarlas contra una base con datos reales.

```bash
php artisan test --filter=ClientesListadoTest
php artisan test
```

Si el entorno local tiene APP_URL sin esquema, utilizar temporalmente APP_URL=http://localhost para ejecutar Artisan. No hace falta cambiar el dominio usado por Herd para probar en el navegador.

Resultados del 6 de noviembre de 2026: se reprodujo el fallo con las seis pruebas nuevas antes de corregirlo. Después pasaron las 20 pruebas de la suite (119 aserciones, incluidas las seis nuevas) en SQLite en memoria. La sintaxis PHP y Pint de la prueba nueva pasaron; `npm run build` también pasó. No se ejecutaron migraciones sobre la base de desarrollo ni se cambiaron sus datos. Queda pendiente la comprobación manual en el navegador.

## Independencia de ramas

La corrección parte de `main`, sin incorporar `refactor/rutas-frontend` ni los demás fixes. Al integrarla junto con el refactor, conservar su carga de JavaScript y sus rutas por data-attributes, además de la clasificación y validación de este cambio. Ambas ramas modifican la vista de clientes y pueden requerir resolver conflictos.
