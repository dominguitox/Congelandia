# Categorías y proveedores desde inventario

Tipo de cambio: `feat`. Rama independiente `feat/creacion-relacionados`, basada en `main`.

## Comportamiento

- Crear y editar un producto permite seleccionar una categoría existente, elegir **Sin categoría** o **Crear nueva categoría…** e ingresar su nombre.
- El abastecimiento inicial y el formulario Registrar ingreso permiten seleccionar un proveedor existente, elegir **Sin proveedor** o crear uno por nombre. El modelo admite teléfono y correo vacíos; se pueden completar desde Proveedores.
- Crear categorías y proveedores conserva el permiso de Administrador. Un cajero puede registrar ingresos con un proveedor existente o sin proveedor, pero no crear proveedores, ni siquiera enviando una petición manual.
- El nombre nuevo se valida hasta 100 caracteres, se recortan espacios y se reutiliza un registro activo con el mismo nombre sin distinguir mayúsculas. No se restauran registros eliminados. Esto no establece una restricción global de unicidad sobre los catálogos existentes.
- Las nuevas entidades se guardan al confirmar el formulario, dentro de la transacción de producto/ingreso. Cancelar, fallar la validación o fallar la transacción no deja entidades creadas por este flujo.
- No se crea un proveedor cuando el stock inicial es cero o está vacío. Un stock positivo sigue exigiendo costo y vencimiento.
- Los errores de validación recuperan el modal, sus datos y los nombres nuevos. La sección de abastecimiento se abre si había stock inicial positivo. Cambiar entre opciones mantiene lo escrito mientras se completa el formulario.
- **Sin categoría/proveedor** representa una relación `NULL`; no necesita seeds ficticios. Los ingresos sin proveedor siguen sumando stock y aparecen en el historial como **Sin proveedor**. El proveedor mostrado en inventario corresponde al último ingreso activo, incluso si ese ingreso no tiene proveedor.

## Instalación

Aplicar la migración [allow_ingresos_without_proveedor](../database/migrations/2026_11_09_020000_allow_ingresos_without_proveedor.php) con `php artisan migrate`. Cambia únicamente la nulabilidad del proveedor de ingresos y mantiene la clave foránea para valores asignados. La categoría de productos ya admitía `NULL`.

En DirectAdmin sin terminal, respaldar e importar [el SQL incremental](../database/sql/2026_11_09_020000_ingresos_sin_proveedor.sql) mediante phpMyAdmin **antes de publicar los formularios**. El script incluye el registro de la migración y puede repetirse. Usar el SQL o la migración, según el entorno. El SQL MySQL requiere validarse en una copia de la base del hosting; las pruebas locales usan SQLite en memoria.

Para revertir el esquema se deben asignar primero proveedores reales a todos los ingresos sin proveedor, incluidos los eliminados. La migración de reversión se detiene si existen valores `NULL`, para evitar reemplazarlos o perder datos.

## Validación

[RelacionesInventarioTest](../tests/Feature/RelacionesInventarioTest.php) cubre creación y edición, opciones vacías, ingresos con proveedor nuevo/existente, nombres inválidos y referencias eliminadas, permisos, recuperación del formulario, stock/historial y reversión de toda la transacción si falla el guardado del detalle.

Validación local: suite completa con **96 pruebas y 506 aserciones**, compilación con `npm run build` y revisión de los formularios en navegador a 320, 375 y 1280 px con vistas Blade reales y datos ficticios en memoria. Se verificó conservar lo escrito al alternar opciones, restaurar el modal tras errores, reiniciar correctamente la edición de otro producto, ocultar la creación de proveedores al cajero y desplegar/enfocar un campo obligatorio dentro del abastecimiento plegado. La base real no se modificó.

## Pendientes acordados

Por decisión del usuario se mantienen pendientes las ventas con discrepancias y los atajos de fechas, incluidas las festividades en reportes. Este grupo no implementa esos comportamientos.
