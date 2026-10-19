# Instalación y puesta en marcha

## Requisitos

| Software | Versión |
| --- | --- |
| PHP | >= 8.4 |
| Composer | 2.x |
| Node.js | 18+ (con npm) |
| MySQL | 8.x (o compatible con MariaDB 10.x) |
| Git | — |

Verifica tus versiones:

```bash
php -v
composer -V
node -v
mysql --version
```

## Instalación

### 1. Clonar e instalar dependencias

```bash
git clone https://github.com/dominguitox/Congelandia
cd Congelandia
composer run setup
```

El script `setup` (definido en `composer.json`) ejecuta en orden:

1. `composer install`
2. Copia `.env.example` a `.env` (si no existe)
3. `php artisan key:generate`
4. `php artisan migrate --force`
5. `npm install --ignore-scripts`
6. `npm run build`

> **Ojo:** `migrate` solo crea las tablas de framework + `usuarios` y `log_errors`. **Las tablas del negocio NO se crean con migraciones**: deben cargarse con los archivos SQL (paso siguiente).

> ⚠️ **Incoherencia conocida:** `composer.json` declara `"php": "^8.3"`, pero el proyecto corre con PHP 8.4 (y Laravel 13 que exige PHP 8.4+). Ver [pendientes.md](pendientes.md).

### 2. Configurar `.env` para MySQL

El `.env.example` trae la conexión SQLite por defecto. Descomenta y completa estas claves:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=congelandia_db
DB_USERNAME=root
DB_PASSWORD=tu_clave
```

La base de datos `congelandia_db` se crea sola al importar `create table.sql` (incluye `CREATE DATABASE`). Si la creas a mano, usa el mismo nombre.

### 3. Cargar las tablas del negocio y los datos

Los archivos SQL están en la raíz del repositorio. Importar **en este orden** (desde MySQL CLI o Workbench):

```bash
mysql -u root -p < "create table.sql"      # esquema completo (business tables)
mysql -u root -p < "inserts.sql"           # datos semilla
mysql -u root -p < "procedimientos.sql"    # stored procedures (opcional)
```

| Archivo | Qué hace | Advertencias |
| --- | --- | --- |
| `create table.sql` | Crea la BD `congelandia_db` y todas las tablas de negocio (`Usuario`, `Cliente`, `Producto`, etc.). Es el esquema real que usan los modelos. | ⚠️ Corre destrucción: contiene `DROP TABLE` y `SET FOREIGN_KEY_CHECKS=0`. No correr en ambientes con datos reales sin revisar. |
| `inserts.sql` | Datos semilla: 7 usuarios, 5 clientes, 8 categorías, 10 productos (PROD-001..010), proveedores, ingresos y ventas de ejemplo. | ⚠️ Contraseñas en texto plano (los usuarios semilla entran con la comparación actual de `Auth\Login`). Ver [pendientes.md](pendientes.md). |
| `procedimientos.sql` | Almacena 8 procedimientos (`SP_RegistrarVenta`, `SP_ObtenerProductoPorId`, `SP_listarProveedores`, etc.). | ⚠️ `SP_RegistrarDetalleVenta` tiene un bug: inserta en columna `idVenta` que no existe (la tabla usa `idSalida`). Ver [pendientes.md](pendientes.md). |
| `congelandia_backup.sql` | Dump mysqldump de las tablas de **framework** (cache, sessions, users...). NO contiene datos de negocio. | Solo sirve como respaldo de tablas de framework. |
| `listado procedimientos.txt` | Nota de diseño con procedimientos planeados. | No es SQL ejecutable. |
| `MWB.mwb` | Diagrama ER de MySQL Workbench (fuente del esquema). | Abrir con Workbench, no ejecutable. |

### 4. Levantar el entorno de desarrollo

```bash
composer run dev
```

Ejecuta 3 procesos en paralelo (con `concurrently`):

| Proceso | Comando | Descripción |
| --- | --- | --- |
| `server` | `php artisan serve` | App en http://localhost:8000 |
| `queue` | `php artisan queue:listen --tries=1` | Worker de colas |
| `vite` | `npm run dev` | Assets con hot reload |

### 5. Usuarios semilla para login

Tras importar `inserts.sql` hay usuarios disponibles (ver el propio archivo para credenciales). El usuario administrador semilla tiene rol `Administrador`, necesario para acceder a las secciones restringidas (ver [rutas.md](rutas.md)).

## Assets de producción (deploy)

```bash
npm run build
```

El manifiesto está configurado en `vite.config.js`. Los bundles se generan en `public/build/`.

## Solución de problemas

| Síntoma | Causa probable | Solución |
| --- | --- | --- |
| Error SQL "table not found" al abrir un módulo | Faltan importar `create table.sql`/`inserts.sql` | Importa los SQL en el orden indicado |
| Clase no encontrada (`PosController`, `historialController`) en Linux/Mac | Nombres de archivo con minúsculas que solo funcionan en Windows (case-insensitive) | Ver [pendientes.md](pendientes.md) — renombrar archivos al estándar PSR-4 |
| `@vite` no encuentra entrada en producción | Entradas faltantes en `vite.config.js` (`usuarioos.js`, `public.css`) | Ver [pendientes.md](pendientes.md) |
| Login rechaza credenciales creadas desde la app | `Auth\Login` compara texto plano pero `UserController::store` guarda con `bcrypt` | Ver [pendientes.md](pendientes.md) (bug crítico) |
| Página de error genérica tras una excepción | El handler global lo redirige atrás con mensaje "Ocurrió un error en el sistema" y registra en `log_errors` | Revisa la tabla `log_errors` para el detalle real |
| `user-select` bloqueado (no puedes copiar texto) | Regla global `* { user-select: none; }` en `resources/css/app.css` | Remover la regla (ver [pendientes.md](pendientes.md)) |

## Tests

```bash
composer run test
```

Solo existen los tests stub de Laravel (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`). No hay cobertura de negocio aún — ver [pendientes.md](pendientes.md).
