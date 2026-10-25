# Migraciones (rama `migracion-sql-a-laravel`)

> ⚠️ **Esta documentación corresponde a los cambios de la rama `migracion-sql-a-laravel`** (pendiente #61 de [pendientes.md](pendientes.md)). Todavía **no está en `main`** hasta que el equipo apruebe el merge. En `main` sigue vigente la vía SQL manual de [instalacion.md](instalacion.md).

En esta rama el esquema de negocio completo pasa de los archivos SQL manuales a **migraciones Laravel**. Ahora `php artisan migrate:fresh` + `php artisan db:seed` produce una base de datos 100% usable sin importar `create table.sql` ni `inserts.sql`.

## Mapa de migraciones

Orden real de ejecución (los FK exigen este orden):

| Migración | Tabla | FKs |
| --- | --- | --- |
| `0001_*_create_users_table` | `users`, `password_reset_tokens`, `sessions` | — (tablas de framework; `sessions` es usada por `SESSION_DRIVER=database`) |
| `0001_*_create_cache_table` / `_jobs_table` | `cache*`, `jobs*` | — |
| `2026_09_19_*_create_usuario_table` | `Usuario` (PK `idUsuario`) | — |
| `2026_10_09_*_create_log_errors_table` | `log_errors` | — |
| `2026_10_19_*_create_cliente_table` | `Cliente` (PK `rutCliente`) | — |
| `_create_categoria_table` | `Categoria` | — |
| `_create_producto_table` | `Producto` (PK `codigo`) | `Categoria` (SET NULL) |
| `_create_lista_precio_table` | `Lista_Precio` | `Producto` (CASCADE) |
| `_create_promocion_table` | `Promocion` | `Producto` (CASCADE) |
| `_create_proveedor_table` | `Proveedor` | — |
| `_create_ingreso_table` | `Ingreso` | `Proveedor`, `Usuario` |
| `_create_detalle_ingreso_table` | `Detalle_Ingreso` | `Ingreso` (CASCADE), `Producto` |
| `_create_tipo_salida_table` | `Tipo_Salida` | — |
| `_create_salida_table` | `Salida` | `Tipo_Salida`, `Usuario`, `Cliente` |
| `_create_detalle_salida_table` | `Detalle_Salida` | `Salida` (CASCADE), `Producto` |
| `_create_metodo_pago_table` | `Metodo_Pago` | — |
| `_create_pago_salida_table` | `Pago_Salida` | `Salida` (CASCADE), `Metodo_Pago` |
| `_create_tipo_devolucion_table` | `Tipo_Devolucion` | — |
| `_create_devolucion_table` | `Devolucion` | `Salida`, `Tipo_Devolucion`, `Producto` |
| `_create_imagen_producto_table` | `IMAGEN_PRODUCTO` | `Producto` (CASCADE) |
| `_create_versiones_table` | `Versiones` | — |
| `2026_10_21_*_add_soft_deletes_to_business_tables` | agrega `deleted_at` a 11 tablas de negocio | — |

Columnas y longitudes replicadas del `create table.sql` original (nombres y PK exactamente iguales: `rutCliente`, `codigo`, `idUsuario`, `totalSalida`, etc.).

## Cómo montar el entorno con esta rama

```bash
# si ya tienes el proyecto clonado con datos viejos:
php artisan migrate:fresh --seed

# desde cero: `composer run setup` conserva todos los pasos; ahora usa migrate:fresh + db:seed
```

El `DatabaseSeeder` crea solo al usuario administrador (`UsuarioSeeder`, credenciales en el archivo). El resto de los datos semilla de `inserts.sql` (clientes, productos, ventas de muestra) quedaron en ese archivo como legado: si los necesitas para demo, importa `inserts.sql` manualmente después de migrar, o crea secciones en `database/seeders/` siguiendo el patrón de `UsuarioSeeder`.

## Deltas intencionales respecto del SQL original

| Delta | Razón |
| --- | --- |
| identificadores enteros como BIGINT UNSIGNED (estándar Laravel `id()`) en vez de INT | coherencia de tipos con los FKs generados; no afecta la app |
| FK en `IMAGEN_PRODUCTO.codigoProducto` (el SQL no la tenía) | evita huérfanos; es la misma vía que ya usaba el código |
| `log_errors.usuario` nullable | el handler global de `bootstrap/app.php` la escribe NULL (columna NOT NULL causaba un error secundario) |
| Migración de usuarios reemplazada | la anterior creaba una tabla `usuarios` estilo Laravel (`id`/`password`) que ningún modelo usaba; la nueva replica `Usuario` (`idUsuario`/`contrasena`) usada por `App\Models\User` |
| `DetalleSalida.$primaryKey = 'idDetalle'` declarado en el modelo | el esquema real define esa PK; Laravel asumía `id` |
| Seeder corregido | `UsuarioSeeder` usaba `App\Models\Usuario` (clase inexistente) y el campo no-fillable `password`; ahora usa `App\Models\User` con `contrasena` hasheada |
| Soft delete en tablas de negocio | migración nueva agrega `deleted_at` a `Ingreso`, `Detalle_Ingreso`, `Detalle_Salida`, `Lista_Precio`, `Promocion`, `IMAGEN_PRODUCTO`, `Pago_Salida`, `Tipo_Salida`, `Metodo_Pago`, `Tipo_Devolucion` y `Devolucion`; `Log_Errors` y `Versiones` quedan sin soft delete |

## Precauciones

1. **`migrate:fresh` borra todos los datos.** Las BD de desarrollo con datos de prueba deben regenerarse con `db:seed` o importando `inserts.sql` después. No ejecutar nunca en producción.
2. **Casing de tablas** (`Usuario`, `Cliente`, `Lista_Precio`, `IMAGEN_PRODUCTO`, ...): conservado igual al `create table.sql`. Funciona en Windows (MySQL case-insensitive) y en el servidor actual; si se despliega en un MySQL Linux con `lower_case_table_names=0`, hay que normalizar casing en migraciones + modelos + queries crudas a la vez (ver [pendientes.md](pendientes.md) #66).
3. El framework sigue creando `users`/`sessions`/`cache`: `sessions` es necesaria porque `SESSION_DRIVER=database`. La tabla `users` de stock no la usa la app; se mantiene por la `$table migrations` del framework.
4. `create table.sql`, `inserts.sql` y `procedimientos.sql` quedan como **legado/referencia**; los cambios de esquema a futuro se hacen con migraciones nuevas (nunca editando las ya corridas).

## Tests del esquema

`tests/Feature/MigrationsTest.php` verifica con cada corrida de `composer run test` (usa sqlite `:memory:`):

1. Que todas las tablas del negocio existen tras `migrate:fresh`.
2. Que el grafo de FK soporta un flujo de venta completo (usuario → cliente → producto → salida → detalle → pago) usando los modelos reales.
3. Que una FK inválida (idTipo inexistente) es rechazada.
