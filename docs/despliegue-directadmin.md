# Publicar Congelandia en DirectAdmin sin SSH

El servidor recibe una entrega terminada mediante su herramienta Git. GitHub compila CSS/JavaScript, instala las dependencias PHP de producción, ejecuta las pruebas y prepara la rama `deploy`. No se ejecutan comandos en el hosting.

## 1. Qué guarda cada rama y cada versión

- Las ramas de trabajo sirven para desarrollar; `main` reúne el código aprobado.
- `deploy` contiene la aplicación preparada, incluidos `vendor/`, los compilados, las fuentes descargadas por Vite, `release.json` y `CHANGELOG.md`.
- `v1.3.0` identifica el commit de código fuente de una publicación.
- `deploy-v1.3.0` conserva su entrega compilada exacta. Permite restaurarla sin recompilar ni volver a instalar dependencias.
- La Release **Congelandia 1.3.0** contiene sus notas y el ZIP `congelandia-deploy-v1.3.0.zip`. Los enlaces automáticos de GitHub **Source code** contienen código fuente, no la entrega preparada.

La versión se escribe manualmente: corrección `1.3.1`, funcionalidad nueva `1.4.0`, cambio incompatible `2.0.0`. La automatización valida el formato, exige un número mayor al instalado en `deploy` y rechaza etiquetas reutilizadas. El changelog se genera con los commits que no sean merges desde la entrega anterior, más los comentarios opcionales. En la primera entrega recoge hasta 100 commits.

La web lee la versión y las notas desde `release.json`, fuera de la carpeta pública. No necesita modificar la base de datos para informar su versión. En desarrollo y en instalaciones antiguas sin ese archivo, conserva la lectura de la tabla `versiones`. `/version-info` requiere iniciar sesión, igual que antes. Las fechas indican cuándo se preparó la entrega, no cuándo se pulsó Deploy en el hosting.

## 2. Activar la automatización una vez

1. Subir estos cambios mediante una rama de trabajo y un pull request hacia `main`. No mezclar la rama antigua `deploy-automatico-pr`: su mecanismo requiere SSH.
2. En GitHub, comprobar que Actions está habilitado para el repositorio. Los workflows declaran el permiso `contents: write`; las políticas de la organización deben permitirlo.
3. Las reglas de protección deben permitir al token de GitHub Actions escribir en `deploy` y crear las etiquetas `v*` y `deploy-v*`. Se usa el token temporal del propio workflow; no se necesitan credenciales del hosting ni un token personal.
4. Cuando los archivos del workflow estén en `main`, aparecerán **Preparar despliegue DirectAdmin** y **Restaurar entrega DirectAdmin** en Actions. Ambos se ejecutan seleccionando la rama `main`.

GitHub exige que un workflow manual esté en la rama predeterminada para poder iniciarlo desde la interfaz. Ver [ejecución manual de workflows](https://docs.github.com/en/actions/how-tos/manage-workflow-runs/manually-run-a-workflow).

Los cambios sin commit o que solo existan en el PC no se incluyen: el workflow toma el commit de `main` con el que se inició.

## 3. Preparar una nueva publicación

1. Integrar los cambios aprobados en `main` y comprobar que CI termine en verde.
2. Abrir el repositorio en GitHub → **Actions** → **Preparar despliegue DirectAdmin** → **Run workflow**.
3. Elegir la rama **main**.
4. Introducir una versión nueva, por ejemplo **1.3.0**, y, opcionalmente, un resumen comprensible de los cambios.
5. Elegir la carpeta pública:
   - **public_html**: para el directorio habitual de DirectAdmin. La entrega renombra `public` a `public_html`; Laravel adapta también `public_path()` para encontrar los compilados y guardar las fotos en el lugar correcto.
   - **public**: si el proveedor ya configuró el dominio para servir el directorio `public` de Laravel.
6. Pulsar **Run workflow** y esperar a que todos los pasos estén en verde.
7. Revisar la nueva Release y las notas. Ya está preparada la rama `deploy`; todavía no se ha actualizado el hosting.

Mantener la misma carpeta pública en futuras publicaciones. La automatización rechaza un cambio de carpeta sobre una entrega ya preparada para evitar romper las rutas y separar las fotos de productos.

Si ya existe una instalación antigua, antes de la primera actualización respaldar las fotos y comprobar su ubicación: al pasar de `public` a `public_html` hay que copiar `public/images/productos/` a `public_html/images/productos/` conservando los nombres. La rama antigua `deploy` tenía fotos de ejemplo versionadas; al retirarlas de Git pueden desaparecer del destino. Restaurarlas desde el respaldo después del primer despliegue si aún las usa la base. Las siguientes entregas ya no versionan fotos de productos.

El flujo prueba Laravel con SQLite y MySQL en Linux. Después instala `vendor` sin dependencias de desarrollo, arma el paquete y arranca una copia con una base SQLite temporal. Comprueba páginas públicas, login y metadatos de versión usando las dependencias reales de producción. No copia las cachés ni los datos generados durante esa prueba a la entrega.

## 4. Primera instalación en DirectAdmin

### Requisitos

Seleccionar PHP **8.4 o superior compatible con composer.lock** y habilitar las extensiones que requieren Laravel y sus dependencias, incluyendo PDO/MySQL, mbstring, intl, fileinfo, bcmath, gd, zip, openssl, curl, DOM y XML. Las entregas se preparan con PHP 8.4. Ver [requisitos de Laravel](https://laravel.com/docs/13.x/deployment).

El servidor debe poder escribir como el usuario de PHP en `storage/`, `bootstrap/cache/` y la carpeta de fotos. Los permisos concretos dependen del propietario y del modo de ejecución de PHP; no se requiere dar permisos 777.

### Ubicación con public_html

Para un dominio de ejemplo, la distribución debe quedar así:

```text
domains/tudominio.cl/
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/                 # Datos persistentes de ejecución
├── vendor/                  # Incluido en deploy
├── .env                     # Se crea una sola vez en el servidor
├── release.json
├── CHANGELOG.md
└── public_html/             # Única raíz visible desde internet
    ├── index.php
    ├── .htaccess
    ├── build/               # Incluido, con manifest.json y fuentes
    └── images/
        ├── brand/
        ├── hero/
        └── productos/       # Fotos subidas en producción; no se versionan
```

**El destino de Git es `domains/tudominio.cl`, no `domains/tudominio.cl/public_html`.** Así, la carpeta pública de la entrega coincide con la que sirve el dominio y el código privado queda fuera de ella. Utilizar una carpeta de aplicación dedicada y respaldar antes un sitio existente: esta distribución supone que los nombres `app`, `vendor`, etc. no pertenecen a otra aplicación.

Con la opción `public`, el destino es la raíz de la aplicación y el proveedor debe apuntar el Document Root a su subcarpeta `public`. No subir todo Laravel dentro de una carpeta visible desde internet.

### Conectar el repositorio

1. Abrir DirectAdmin → **Advanced Features / Funciones avanzadas → Git**.
2. Crear o modificar el repositorio remoto de Congelandia.
3. Para un repositorio público, usar su URL HTTPS. Para uno privado, configurar en Git Manager una clave de lectura de GitHub según lo que permita el proveedor. Una clave usada por el panel para descargar Git no exige que tengas acceso a una terminal SSH.
4. Descargar las referencias con **Fetch** o la acción equivalente del panel.
5. En **Modify**, elegir **Deployment branch: deploy**.
6. Introducir como **Deploy directory** la carpeta existente `domains/tudominio.cl`, relativa al directorio de tu usuario. No escribir la ruta absoluta `/home/...` en ese campo.
7. Guardar y pulsar **Deploy**.

DirectAdmin documenta el despliegue como un checkout del árbol de trabajo desde su repositorio bare. No configurar scripts de limpieza ni copias que vacíen el destino: `.env`, `storage/` y las fotos de producción no están en Git y deben conservarse. Hacer la primera prueba en un subdominio con datos independientes y verificar que una segunda entrega conserva un archivo de prueba en `public_html/images/productos/` y en `storage/` antes de utilizarlo con ventas reales.

Referencia: [Git Manager de DirectAdmin](https://docs.directadmin.com/other-hosting-services/git/).

### Configuración privada

En File Manager, copiar `.env.example` a `.env` en la raíz privada de la aplicación y editar:

- `APP_URL`: URL HTTPS real.
- `APP_KEY`: clave propia de producción, generada una sola vez en el PC.
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: datos de MySQL creados en DirectAdmin.
- Datos de contacto, horario y tema visual.

Para generar solo la clave en tu instalación local:

```powershell
php artisan key:generate --show
```

Copiar el resultado `base64:...` al `.env` del hosting. No reemplaza la clave de tu entorno local. No compartirla ni guardarla en Git. Conservar la misma clave en las siguientes entregas.

La plantilla ya usa `APP_ENV=production`, `APP_DEBUG=false`, sesiones y caché en archivos, y cola síncrona: no necesita un worker permanente. El correo queda en el log hasta configurar un proveedor real. Conservar HTTPS para las cookies seguras.

### Base de datos inicial

Git despliega archivos; no crea ni actualiza tablas. Crear una base MySQL y su usuario en DirectAdmin e importar con phpMyAdmin una exportación preparada a partir de una base **MySQL vacía** con las migraciones de la versión elegida. Incluir la tabla `migrations` para conservar el historial del esquema y únicamente los datos iniciales necesarios. No exportar SQLite ni la base de pruebas del workflow.

La preparación de esa base se hace en una instalación local separada, nunca apuntando a la base real del negocio. El seeder actual crea un administrador con una contraseña de ejemplo; sustituirla por una contraseña propia antes de publicar. No cargar datos de ventas de prueba al sitio real.

## 5. Actualizaciones habituales

Para la opción **Sin proveedor** en ingresos, aplicar antes de los formularios el [SQL incremental de ingresos sin proveedor](../database/sql/2026_11_09_020000_ingresos_sin_proveedor.sql). Ver [comportamiento y migración](./relaciones-inventario.md).

1. Preparar una publicación desde GitHub como en el paso 3.
2. Respaldar la base y las fotos/archivos del hosting desde DirectAdmin o phpMyAdmin.
3. Revisar si la versión incluye migraciones nuevas. Si las incluye, preparar y probar un SQL incremental equivalente para phpMyAdmin. Planificar el orden entre SQL y archivos; detener temporalmente las operaciones de venta si el cambio no permite convivencia entre ambas versiones.
4. En DirectAdmin → Git, ejecutar **Fetch**, comprobar que se eligió **deploy** y pulsar **Deploy**.
5. Iniciar sesión y comprobar la versión, `/version-info`, catálogo, POS, inventario y los módulos modificados.

No volver a copiar `.env.example` encima del `.env` existente. No reemplazar `APP_KEY`. No importar otra vez la base inicial. No ejecutar `composer run setup`: contiene `migrate:fresh`, que borra las tablas.

Cada entrega incluye código, estilos, JavaScript, fuentes, manifiesto y dependencias PHP. No subir los estilos por separado. La rama `main` mantiene ignorados los compilados y `vendor`; solo `deploy` los incluye. No mezclar `deploy` hacia `main` ni desarrollar manualmente en ella.

No se incluyen `.env`, contraseñas, `auth.json`, `node_modules`, sesiones, logs, bases SQLite, SQL de prueba, fotos de productos ni cachés de configuración, rutas o vistas. Las cachés de Laravel que dependen del entorno no se preparan en el PC ni en GitHub. Si una instalación anterior dejó `bootstrap/cache/config.php`, `routes-*.php`, `packages.php`, `services.php` o vistas compiladas, retirarlos una vez desde File Manager para que Laravel regenere lo necesario. No borrar las carpetas ni los archivos del negocio.

El cambio de archivos de DirectAdmin no es una sustitución atómica de directorios: programar un período breve de mantenimiento cuando el tamaño de la entrega o los cambios lo requieran.

## 6. Volver a una entrega anterior

1. Confirmar que el código anterior es compatible con la base actual. Si hubo cambios incompatibles en tablas, preparar la recuperación de la base antes de continuar. Restaurar un respaldo puede descartar operaciones hechas después del respaldo.
2. GitHub → **Actions → Restaurar entrega DirectAdmin → Run workflow**.
3. Elegir **main** e introducir la versión que ya se preparó, por ejemplo **1.2.1**.
4. El workflow recupera `deploy-v1.2.1` y agrega un nuevo commit a `deploy` con ese contenido exacto. No recompila, no reescribe el historial y no cambia etiquetas existentes.
5. En DirectAdmin, ejecutar **Fetch** y **Deploy** otra vez.

La web vuelve a informar la versión restaurada. No se restauran automáticamente bases de datos, fotos ni archivos privados. Las publicaciones posteriores vuelven a generar notas a partir de la versión que quedó en `deploy`.

## 7. Problemas frecuentes

| Problema | Qué revisar |
| --- | --- |
| No aparece Run workflow | Los workflows deben estar integrados en la rama predeterminada y Actions habilitado. |
| El trabajo aparece omitido | Seleccionar `main`, no una rama de trabajo ni `deploy`. |
| Error al crear etiquetas o actualizar deploy | Permisos de Actions y reglas de protección de ramas/etiquetas. |
| La versión ya existe | Usar un número nuevo; para volver atrás utilizar Restaurar entrega. |
| Falta manifest.json o los estilos | Se desplegó código fuente en lugar de `deploy`, o el destino no coincide con la carpeta pública elegida. |
| Error 500 al instalar | Revisar PHP/extensiones, APP_KEY, permisos, tablas y `storage/logs/`. Mantener APP_DEBUG=false. |
| No aparecen los cambios al pulsar Deploy | Ejecutar Fetch antes y comprobar la rama y el directorio de destino. Si PHP mantiene código antiguo en OPcache, pedir al proveedor su reinicio o la revisión de su política de actualización. |
| Se ve una versión vieja | Revisar `release.json` en el destino y la rama seleccionada. La tabla `versiones` solo se usa cuando no hay metadatos de entrega. |
| Falló únicamente Crear Release de GitHub | La rama y las etiquetas ya pueden haberse publicado. Comprobarlas antes de reintentar: se puede crear la Release desde GitHub eligiendo la etiqueta `vX.Y.Z` existente y copiar las notas de `deploy/CHANGELOG.md`. No reutilizar etiquetas para otro código. |

La preparación de entregas y la restauración comparten un bloqueo para no escribir simultáneamente en `deploy`. La actualización de rama y etiquetas de una entrega nueva se publica en una operación Git atómica. Esto no convierte la copia de archivos del hosting ni la creación posterior de la Release en operaciones atómicas.
