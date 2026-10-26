# Despliegue a DirectAdmin

Guía para subir el proyecto Laravel ya compilado y funcional a un servidor con panel DirectAdmin (hosting compartido típico). Supuesto: el hosting entrega PHP + MySQL dominio con `public_html` como raíz.

## 0. Requisitos del servidor

En DirectAdmin entra a **"Select PHP Version"** (o "PHP Selector") y elige:

- PHP **8.3 o superior** (el proyecto usa `php: ^8.3`)
- Extensiones activas: `pdo_mysql`, `mbstring`, `intl`, `fileinfo`, `gd`, `zip`, `bcmath`, `opcache`, `openssl`, `curl`

Si el hosting ofrece SSH, úsalo (mucho más fácil). Si no, todo se puede hacer con el **File Manager** de DirectAdmin y **phpMyAdmin**.

## 1. Preparar el proyecto en tu PC

### 1.1 Compilar el frontend (ya lo tienes, pero por si acaso)

```powershell
npm ci
npm run build
```

Esto genera `public/build/` con los assets versionados. **Ese carpeta es la que se sube; no se compila en el servidor.**

### 1.2 Instalar dependencias de producción de PHP

```powershell
composer install --no-dev --optimize-autoloader
```

(`--no-dev` quita pest/pint/faker, que no sirven en producción)

### 1.3 Limpiar cachés locales de desarrollo

```powershell
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 1.4 Crear el ZIP para subir

Crea un zip con TODO el proyecto excepto lo que no debe subir:

| Excluir | Por qué |
|---|---|
| `node_modules/` | Se sirven ya compilados en `public/build` |
| `.git/` | Administración interna |
| `.env` | Se crea uno nuevo en el servidor |
| `tests/`, `viejo/` | No necesarios en producción |
| `congelandia_backup.sql`, `*.mwb` | Respaldo no va en el hosting |

Con PowerShell (desde la raíz):

```powershell
$excl = @('.git','node_modules','tests','viejo','.env','congelandia_backup.sql','MWB.mwb')
Get-ChildItem -Exclude $excl | Compress-Archive -DestinationPath ..\congelandia-deploy.zip -Force
```

También puedes excluir `vendor/` y dentro del servidor ejecutar `composer install --no-dev` por SSH. Pero si no tienes SSH, **sí sube la carpeta `vendor/` completa** (el zip quedará pesado, funciona igual).

> ⚠️ Si el File Manager de tu DirectAdmin limita el tamaño de subida (normalmente 50–80 MB) y `vendor/` no cabe: pide al host acceso SSH o sube el zip por FTP con FileZilla.

## 2. Crear la base de datos en DirectAdmin

1. Panel DirectAdmin → **MySQL Management** → **Create new Database**
2. Anota los datos (DirectAdmin suele antepone el usuario de la cuenta al nombre):
   - Database name: `usuario_congelandia`
   - Username: `usuario_congel`
   - Password: (la que definas — guárdala para el `.env`)

## 3. Importar el esquema y datos con migraciones

El proyecto ya no usa los archivos SQL crudos ni stored procedures; el esquema vive en `database/migrations/` y los datos iniciales en `database/seeders/` (`UsuarioSeeder`).

Antes de migrar, asegúrate de tener el `.env` del servidor ya configurado con la BD (paso 6) o crea temporalmente un `.env` mínimo con los datos de conexión. Luego ejecuta las migraciones por una de estas vías:

### Opción A: con SSH (recomendada)

Desde la raíz del proyecto en el servidor:

```bash
php artisan key:generate        # solo si aún no hay APP_KEY
php artisan migrate --force
php artisan db:seed --force
```

### Opción B: migrar desde tu PC apuntando a la BD remota

Si el hosting no da SSH, habilita en DirectAdmin → **MySQL Management** → (editar usuario) la opción **"Acceso remoto MySQL"** (Host: `%` o tu IP), y desde tu PC cambia tu `.env` local (o usa variables de entorno) para apuntar a la BD remota:

```dotenv
DB_HOST=IP-del-servidor   # o dominio del hosting
DB_PORT=3306
DB_DATABASE=usuario_congelandia
DB_USERNAME=usuario_congel
DB_PASSWORD=tu_password_de_BD
```

Luego desde tu PC:

```powershell
php artisan migrate
php artisan db:seed
```

⚠️ Al terminar, restablece tu `.env` local a tu BD local y cierra el acceso remoto.

### Opción C: sin SSH ni acceso remoto — phpMyAdmin como respaldo de datos manual

Si no puedes ni migrar remotamente, la única vía es importar SQL generado localmente: con tus migraciones ejecutadas en local, exporta desde tu phpMyAdmin local la estructura + datos de cada tabla y expansión a indicar los usuarios y datos, y expórtalos vía **Importar** en el phpMyAdmin de DirectAdmin (pestaña "Exportar" → formato SQL → importar en el servidor). Es un plan B menos automático pero funciona.

> 📌 Verificación: en phpMyAdmin → pestaña **"Estructura"** de la BD deben aparecer las tablas (`usuario`, `cliente`, `categoria`, `producto`, `lista_precio`, `promocion`, `proveedor`, `ingreso`, `detalle_ingreso`, `tipo_salida`, `salida`, `detalle_salida`, `metodo_pago`, `pago_salida`, `tipo_devolucion`, `devolucion`, `imagen_producto`, `versiones`, `sessions`, `cache`, `jobs`, etc.), y en `usuario` debe estar tu usuario sembrado para hacer login.

> ⚠️ **Casing de tablas en Linux**: las migraciones conservan el casing original de las tablas (`Usuario`, `Categoria`, `Lista_Precio`, `IMAGEN_PRODUCTO`, ...). En Windows (MySQL case-insensitive) funciona, pero los servidores de DirectAdmin corren sobre Linux, donde MySQL distingue mayúsculas por defecto (`lower_case_table_names=0`). Si al abrir un módulo sale "table doesn't exist", confirma el casing en `SHOW VARIABLES LIKE 'lower_case_table_names'`; si es tu caso, normaliza el casing en migraciones + modelos + queries crudas (detalle en [migraciones.md](migraciones.md) y [pendientes.md](pendientes.md) P2). Si el hosting permite configurar `lower_case_table_names=1`, esa es la solución rápida.

## 4. Subir y extraer el proyecto

1. DirectAdmin → **File Manager** → carpeta `domains/tudominio.com/`
2. **Sube** `congelandia-deploy.zip` (botón Upload)
3. **Extraer** el zip ahí. Debe quedar:

```
domains/tudominio.com/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env        ← se creará (paso 6)
└── artisan
```

## 5. Apuntar el dominio al `public/` (punto crítico)

Laravel expone **solo** `public/` al mundo. Tienes dos formas según lo que permita tu hosting:

### Opción A (ideal): cambiar el docroot vía .htaccess

En hosting compartido muchas veces NO puedes cambiar el docroot del panel DirectAdmin (los planes avanzados sí lo permiten en *Account Manager → Domain Pointers* o el hosting te lo configura a pedido).

Si el docroot es fijo (`public_html` o `domains/tudominio.com/public_html`) y apunta al nivel del proyecto, **no dejes el código fuente expuesto** — hay que hacer un rearranque:

1. En File Manager, dentro del proyecto, **copia el CONTENIDO de `public/`** (index.php, .htaccess, build/, favicon.ico, etc.) a la carpeta `public_html/`
2. Edita `public_html/index.php` para que apunte a las carpetas del proyecto padre que acabas de subir:

```php
<?php

use Illuminate\Foundation\Application;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->handleRequest(Request::capture())->send();
```

so, ya que el zip quedó al nivel tipo `domains/tudominio.com/`, las rutas `__DIR__.'/../...'` ya encajan; solo revisa el nivel de carpeta.

3. Protege el código fuente expuesto agregando al **.htaccess del proyecto padre** (`domains/tudominio.com/.htaccess`, el que está junto a `app/`):

```apache
<FilesMatch "\.(env|md|sql|txt|json|lock|yml|yaml|ini)">
    Require all denied
</FilesMatch>

<IfModule mod_rewrite.c>
    RewriteRule ^(app|bootstrap|config|database|resources|routes|storage|vendor|tests)/ - [F,L]
</IfModule>
```

### Opción B (la mejor si el plan lo permite): pedir cambio de docroot

Escribe un ticket al hosting pidiendo:

> "Cambiar el Document Root de tudominio.com a `/domains/tudominio.com/public/` (instalación Laravel)."

Con docroot = `public/` no hay que copiar ni proteger nada: `public/index.php` ya es correcto.

## 6. Crear el `.env` de producción

En File Manager, crea el archivo `.env` en la raíz del proyecto (al nivel de `artisan`) con:

```dotenv
APP_NAME="Congelandia"
APP_ENV=production
APP_KEY=              ← se genera en el paso 7
APP_DEBUG=false       ← NUNCA true en producción
APP_URL=https://tudominio.com

APP_TEMA=base

APP_LOCALE=es
APP_FALLBACK_LOCALE=es

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=usuario_congelandia
DB_USERNAME=usuario_congel
DB_PASSWORD=tu_password_de_BD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_PATH=/
SESSION_DOMAIN=

FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_HOST=mail.tudominio.com
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="no-reply@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

> 📌 El `.gitignore` del repo excluye `.env`, por eso no vino en el zip. `php artisan key:generate` (siguiente paso) completa `APP_KEY`.

Si tu hosting no permite crear archivos de texto con File Manager, sube un `.env.example` renombrado a `.env` y edítalo dentro del servidor.

## 7. Configurar en el servidor (SSH)

Si tienes SSH (DirectAdmin → SSH keys o terminal), desde la carpeta del proyecto:

```bash
# 1. Generar la clave de la app (obligatorio si aún no existe)
php artisan key:generate

# 2. Cachés de producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. (Opcional) crear el enlace simbólico para descargas de archivos
php artisan storage:link
```

### Si NO tienes SSH (todo por File Manager)

- **key:generate**: genera la clave en una instalación local (`php artisan key:generate --show`) y pega el resultado en `APP_KEY=base64:...` del `.env` del servidor.
- **config:cache / route:cache / view:cache**: sin cachés el sitio funciona (solo un poco más lento) — nada impide arrancar. Si más adelante consigues SSH, ejecútalos.

### 7.1 El truco equivalente de `storage:link` sin SSH

En producción, si no puedes ejecutar `php artisan storage:link` por falta de SSH, hay una alternativa igual de válida: crea la carpeta `public/storage` manualmente (vía File Manager) y copia ahí el contenido de `storage/app/public/`.

```powershell
# en tu PC, crear zip que puedes extraer en public/
Get-ChildItem storage\app\public | Compress-Archive -DestinationPath ..\storage-public.zip -Force
```

En el servidor, extrae ese zip en `domains/tudominio.com/public/storage/`. Sirve igual que el symlink.

---

## 8. Permisos de carpetas

En File Manager (DirectAdmin), botón "Set Permissions" sobre:

| Carpeta | Permiso |
|---|---|
| `storage/` y todo su contenido | 755 / archivos 644 |
| `bootstrap/cache/` y su contenido | 755 / archivos 644 |
| `.env` | 600 o 640 (protegido) |

El usuario de Apache (generalmente `apache` o `www-data`) debe poder escribir en `storage/` y `bootstrap/cache/` (sesiones, logs, cachés). Si la sesión falla con error 500, revisa esto primero.

## 9. Comprobar que funciona

Abre `https://tudominio.com` — debería cargar la página de login / home del sistema.

Mensajes comunes de falla y su causa:

| Síntoma | Causa probable |
|---|---|
| Página en blanco | Cambio de permissions / `APP_DEBUG=false` + revisar `storage/logs/laravel.log` |
| `500 - whoops` y no log | Revisar el log del servicio del hosting (cPanel/DA error_log) |
| "Missing manifest" | No subiste `public/build/` (ejecuta `npm run build` y súbelo) |
| Error de tabla no existe | Faltó ejecutar `php artisan migrate --force` |
| Error 500 tras subir | `.env` sin `APP_KEY` |
| Sesiones no persisten | Permisos de `storage/framework/sessions/` |
| CSS/JS rotos | `APP_URL` mal configurado, o subiste el proyecto en un subdirectorio sin actualizar docroot |

> 📌 Con `APP_DEBUG=false` los errores se registran en `storage/logs/laravel-YYYY-MM-DD.log` — revisa ahí, no es la pantalla.

---

## 10. Checklist rápido de subida

```text
[ ] npm run build local                    → public/build/
[ ] composer install --no-dev local        → vendor/
[ ] ZIP sin node_modules/.git/.env/tests
[ ] BD creada en DirectAdmin (MySQL Mgmt)
[ ] Migraciones ejecutadas: php artisan migrate --force
[ ] Seed ejecutado: php artisan db:seed --force
[ ] ZIP subido y extraído en domains/tudominio.com
[ ] public/* en public_html + index.php corregido (o docroot = public/)
[ ] .htaccess de protección en la raíz del proyecto
[ ] .env creado con APP_ENV=production, APP_DEBUG=false, APP_URL y BD reales
[ ] APP_KEY generada
[ ] Permisos 755/644 en storage/ y bootstrap/cache/
[ ] public/storage con contenido de storage/app/public/ (si no hay SSH)
[ ] Landing carga en https://tudominio.com
[ ] Login de administración funciona
```

## 11. Mantenimiento posterior

- **Actualizar archivos**: sube solo los que cambien vía File Manager o FTP; para cambios en código PHP recuerda no cachear si no vas a correr `config:cache` de nuevo (o ejecútelo por SSH).
- **Logs**: descarga `storage/logs/` para inspección, o revisa desde File Manager.
- **Backup**: respalda la BD con "Exportar" en phpMyAdmin y descargar `storage/app/` cada ciertos intervalos.

## 12. Alternativa: desplegar con Git (recomendado a futuro)

Si el hosting te da SSH, la forma más limpia es subir por git y correr composer en el servidor:

```bash
git clone https://github.com/usuario/Congelandia.git ~/domains/tudominio.com
cd ~/domains/tudominio.com
composer install --no-dev --optimize-autoloader
npm ci && npm run build       # solo si el plan da Node.js; si no, sube build/ por FTP
php artisan key:generate
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Así las actualizaciones son un `git pull` + 2 comandos.

## 13. Deploy automático con GitHub Actions (un click)

El workflow `.github/workflows/deploy.yml` hace todo el proceso desde **GitHub → Actions → Deploy → Run workflow**:

1. Instala PHP/Node, compila con Vite (`npm run build`) y corre los tests.
2. Entra por SSH al servidor, ejecuta `git pull`, `composer install --no-dev` y `php artisan migrate --force`.
3. Sube `public/build` compilado.
4. Registra la versión con `php artisan version:registrar` y regenera las cachés.

### 13.1 Requisitos en DirectAdmin

- Acceso **SSH** habilitado (DirectAdmin → SSH Keys).
- Repositorio clonado en el servidor (`git clone`) apuntando a `main`, con permiso de lectura (deploy key del repo).
- PHP 8.3+ y **Composer** disponibles por SSH.
- `.env` de producción ya creado y `APP_KEY` generada.
- El usuario SSH debe poder escribir en `storage/`, `bootstrap/cache/` y `public/build/`.

### 13.2 Secrets del repositorio (GitHub → Settings → Secrets and variables → Actions)

| Secret | Contenido |
| --- | --- |
| `SSH_HOST` | IP o dominio del servidor |
| `SSH_USER` | Usuario SSH |
| `SSH_PORT` | Puerto SSH (opcional, por defecto 22) |
| `SSH_PRIVATE_KEY` | Clave privada cuya pública está en `~/.ssh/authorized_keys` del servidor |
| `DEPLOY_PATH` | Ruta absoluta del proyecto en el servidor (donde está `artisan`) |

### 13.3 Uso

1. GitHub → **Actions** → **Deploy** → **Run workflow**.
2. Escribe la versión (ej. `1.3.0`) y los comentarios.
3. El workflow falla si los tests no pasan; si todo va bien, la fila queda en `versiones` y esa versión aparece en el sitio público.

También se puede ejecutar el comando a mano en el servidor:

```bash
php artisan version:registrar 1.3.0 --comentarios="Corrige reportes 9 y 10"
```

### 13.4 Si solo tienes la herramienta Git de DirectAdmin (sin SSH)

La herramienta Git del panel es *pull* (el servidor jala cambios), no se puede disparar desde GitHub Actions. En ese caso:

- Configura el Git de DirectAdmin para hacer `git pull` de `main` por cron o webhook.
- El workflow puede seguir compilando y subiendo `public/build` por FTP, o bien compilar en local y subir esa carpeta.
- Para registrar la versión, agrega un comando post-pull en el servidor (`php artisan version:registrar ...`); si el panel no permite hooks, ejecútalo manualmente por SSH/cron.

> ⚠️ Recordatorio: en Linux el casing de tablas es sensible (ver #66 en [pendientes.md](pendientes.md)). Si `migrate` o `version:registrar` fallan con "table doesn't exist", hay que normalizar el casing antes de usar este deploy.
