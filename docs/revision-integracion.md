# Corrección de la integración de noviembre de 2026

Base: `0600270`, después de los PR #50 a #56. Rama: `fix/integracion-main`.

## Cambios recuperados

- Se conserva el esquema canónico en minúsculas: modelo de usuarios, validaciones de productos y fixtures de pruebas.
- Inventario, dashboard, POS y el método `show` desactivado vuelven a usar `Producto::conDetalle()`. El POS conserva ofertas e imágenes. La selección del precio para cobrar usa también `idPrecio` para desempatar fechas iguales, como la consulta compartida.
- Se adapta la corrección de clientes del commit `7628188`, que quedó fuera del PR #51: clientes sin compras visibles, mensajes de éxito, validación con recuperación del formulario y sus pruebas. Se conserva el JavaScript separado y las rutas por atributos `data-*`.
- El cajero recupera los enlaces operativos de inventario, historial y clientes. Las rutas administrativas mantienen `role:Administrador` y el 404 deliberado para accesos no autorizados.
- Reportes tiene una sola declaración de ruta.

No se modifica el esquema ni se ejecutan migraciones sobre la base de desarrollo.

## Validación local (2026-11-08)

PHP 8.4.23 de Herd, SQLite en memoria:

- Antes del arreglo, fallaba la prueba existente de nombres exactos (`Usuario` frente a `usuario`).
- Las comprobaciones de cliente recién creado, enlaces operativos del cajero y consistencia de precio entre pantallas fallaron antes del arreglo.
- Después: **59 pruebas y 320 aserciones correctas** en la suite completa.
- Sintaxis de los diez PHP modificados/nuevos correcta; compilación de vistas, caché de rutas y `npm run build` correctos.
- El build conserva la advertencia previa de `fontaine` opcional; no bloquea la compilación.

La verificación local en SQLite no demuestra sensibilidad a mayúsculas de MySQL. [CI](../.github/workflows/ci.yml) ejecuta la suite completa sobre SQLite y MySQL 8.4 en Linux, compila frontend/vistas/rutas y comprueba que MySQL tenga `lower_case_table_names=0`.

## Integración

Antes de integrar, exigir los checks `Pruebas (sqlite)` y `Pruebas (mysql)` sobre el resultado actualizado del PR. Los resultados históricos obtenidos por ramas individuales no validan su combinación.

Al resolver conflictos, conservar simultáneamente las consultas compartidas, las tablas en minúsculas, las rutas con nombre, el filtro de roles y las mejoras del listado de clientes. Revisar el diff completo del PR después de resolver cada conflicto.
