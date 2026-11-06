# Instalación y puesta en marcha

## Requisitos

PHP >= 8.4 con extensiones de Laravel, PDO MySQL y PDO SQLite para pruebas; Composer 2; Node compatible con Vite 8 (consultar engines de la versión instalada), npm; MySQL 8; Git.

## Entorno nuevo

```bash
composer install
cp .env.example .env
```

En PowerShell usar `Copy-Item .env.example .env`. Configurar `.env` antes de ejecutar migraciones:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=congelandia_db
DB_USERNAME=usuario
DB_PASSWORD=clave
```

Crear previamente la base vacía con ese nombre. Luego:

```bash
php artisan key:generate
php artisan migrate --seed
npm ci --ignore-scripts
npm run build
composer run dev
```

Las migraciones de negocio y el seeder están en main; ver [migraciones.md](migraciones.md). UsuarioSeeder crea el administrador inicial (credenciales en el archivo); cambiar su contraseña al usar un entorno real. El login valida hashes con Hash::check. No importar create table.sql/inserts.sql encima del esquema migrado.

`composer run setup` instala dependencias, genera clave, ejecuta **migrate:fresh --force**, seed y build. Ese comando destruye las tablas de la conexión configurada; usar únicamente en una base nueva o desechable. Para conservar datos usar migrate, y revisar los efectos del seeder antes de repetirlo.

## SQL histórico

create table.sql e inserts.sql conservan esquema/datos de demostración de la vía manual. No son la fuente principal de instalación; difieren de las migraciones (incluidos soft deletes y promociones). procedimientos.sql es opcional y no se llama desde la app: tiene deuda #29/#62 y no se recomienda importarlo. El respaldo congelandia_backup.sql solo cubre tablas de framework. MWB.mwb es el diagrama histórico, no una migración.

## Verificaciones

```bash
composer run test
php artisan route:list
php artisan view:cache
npm run build
```

La suite tiene pruebas de negocio (migraciones/FK y ofertas/POS) además de ejemplos. phpunit.xml usa SQLite en memoria. Nunca sobrescribir su conexión para apuntar a datos reales: las pruebas usan migrate:fresh.

En navegador probar login/logout, catálogo/contacto, dashboard, POS, inventario, clientes y administración. Las tablas mezclan casing en la base actual: comprobar MySQL Linux con lower_case_table_names=0 antes de desplegar; ver #66 y [auditoría](revision-documentacion.md).

## Diagnóstico

- Assets ausentes: revisar Vite en desarrollo o ejecutar npm run build.
- Tablas ausentes: revisar conexión y php artisan migrate:status; no usar migrate:fresh como reparación en una base con datos.
- Login: verificar usuario activo, contraseña hasheada y conexión. UserController ya crea hashes compatibles.
- Errores: revisar storage/logs/laravel.log y el canal LOG_CHANNEL configurado. bootstrap/app.php usa el manejo estándar de Laravel; no hay persistencia global automática en log_errors.
- Copia de texto bloqueada: la regla user-select está en resources/css/theme/base.css (pendiente #35).

Despliegue: [despliegue-directadmin.md](despliegue-directadmin.md). Estado contrastado: [revision-documentacion.md](revision-documentacion.md).
