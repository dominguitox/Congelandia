# Creación y listado de clientes

El listado incluye a todos los clientes activos, aunque todavía no tengan compras ni pagos.

- **Deudas activas:** clientes cuyo total vendido menos sus pagos es mayor que cero.
- **Clientes sin deuda:** los demás clientes activos. Sin pagos previos, muestran «Sin registro».
- Crear un cliente muestra un mensaje de éxito. Una validación fallida conserva los datos, mantiene abierto el formulario y muestra el error en español.
- Editar, consultar historial y eliminar también están disponibles para clientes sin compras. Los eliminados lógicamente quedan fuera del listado.

La vista usa [clientes.js](../resources/js/clientes.js), cargado con Vite, y las rutas generadas por Laravel en atributos `data-*`. No se restaura el JavaScript inline de la rama antigua.

La corrección no requiere migraciones ni recrear clientes: los registros guardados que antes estaban ocultos aparecen automáticamente.

## Verificación

[ClientesListadoTest](../tests/Feature/ClientesListadoTest.php) cubre alta y visibilidad, clasificación de saldos, eliminación lógica, edición sin compras y recuperación del formulario tras errores.

```sh
php artisan test --filter=ClientesListadoTest
```

Las pruebas recrean el esquema: usar SQLite en memoria o una base exclusiva de pruebas, como hace CI.

En navegador, crear un cliente sin compras, buscarlo, editarlo y consultar su historial vacío. Intentar repetir su RUT debe mostrar el error sin perder los datos. Eliminar el cliente de prueba debe retirarlo del listado.
