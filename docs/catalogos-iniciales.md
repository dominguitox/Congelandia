# Catálogos iniciales

El seeder general incluye los datos mínimos para operar:

| Seeder | Datos |
| --- | --- |
| `UsuarioSeeder` | Administrador inicial, solo si aún no existe su correo |
| `MetodoPagoSeeder` | Efectivo, Débito y Crédito |
| `TipoSalidaSeeder` | Venta, Merma por Vencimiento y Merma por Daño |

Después de aplicar las migraciones en desarrollo:

```bash
php artisan db:seed
```

Se puede repetir sin duplicar los registros iniciales ni cambiar sus IDs. Los catálogos se buscan por nombre; se reactivan sus valores iniciales si estaban eliminados mediante soft delete y se conservan otros valores existentes. La cuenta administradora existente se conserva incluso si está eliminada: no se restablecen su contraseña, rol, nombre ni estado.

Para ejecutar únicamente los catálogos, sin crear la cuenta inicial:

```bash
php artisan db:seed --class=MetodoPagoSeeder
php artisan db:seed --class=TipoSalidaSeeder
```

El POS obtiene el ID del tipo `Venta` de la base de datos; no depende de que sea 1. No existe un catálogo de tipos de ingreso: los ingresos actuales corresponden al abastecimiento desde proveedores.

En DirectAdmin, publicar código con Git no ejecuta los seeders. Para una instalación nueva, incluir estos datos en la exportación MySQL inicial. Para una instalación existente sin terminal, respaldar la base y aplicar mediante phpMyAdmin los datos que falten, respetando los IDs existentes. No volver a importar la base inicial ni usar `composer run setup` en producción. Ver la [guía de despliegue](despliegue-directadmin.md).
