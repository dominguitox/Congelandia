# Instrucciones del proyecto para agentes y desarrolladores

## Flujo de ramas

- Crea cada funcionalidad o corrección en una rama nueva basada en `main`.
- Abre un Pull Request hacia `main` y espera que CI termine correctamente antes de fusionarlo.
- No desarrolles directamente en `deploy`.
- No mezcles `deploy` de vuelta hacia `main`: esa rama contiene una distribución preparada, no código fuente.
- No subas manualmente `public/build/` ni `vendor/` a `main`; el workflow de despliegue los genera en la rama `deploy`.

## Flujo de publicación

Después de fusionar el Pull Request, la publicación se prepara desde GitHub Actions:

1. Ejecuta **Preparar despliegue DirectAdmin** usando la rama `main`.
2. Indica una versión SemVer nueva (`1.0.0`, `1.1.0` o `1.1.1`) y un resumen de cambios.
3. Mantén `public_html` si ese es el directorio público configurado en DirectAdmin; no lo cambies entre publicaciones.
4. Espera que terminen las pruebas, la compilación, el empaquetado, la actualización de `deploy` y la creación de la Release.
5. Revisa la Release y el `CHANGELOG.md` antes de actualizar el hosting.
6. En DirectAdmin ejecuta **Fetch** y luego **Deploy** sobre la rama `deploy`.

El workflow ya incluye `vendor/`, los archivos compilados de Vite, fuentes, `manifest.json`, `release.json` y `CHANGELOG.md`. No se compila Node ni se ejecuta Composer en el hosting.

## Información que siempre debe recibir el desarrollador

Antes de dar una tarea de despliegue por terminada, informa explícitamente:

- La versión que debe introducir en GitHub Actions.
- Que el workflow debe iniciarse desde `main`.
- Si debe elegir `public_html` o `public` y que esa elección debe mantenerse.
- Que debe comprobar la Release y la rama `deploy`.
- Que debe hacer **Fetch → Deploy** en DirectAdmin.
- Que `.env`, `APP_KEY`, credenciales de MySQL, `storage/` y fotos de productos se conservan manualmente en el servidor.
- Si hay migraciones o cambios de esquema, que Git no actualiza la base de datos: debe respaldar e importar el SQL incremental mediante phpMyAdmin, en el orden indicado.
- Qué páginas y módulos debe probar después del despliegue, incluyendo `/version-info` y el módulo afectado.
- Que `composer run setup` no se usa en producción porque ejecuta `migrate:fresh`.

Para restaurar una versión, usa **Restaurar entrega DirectAdmin** con una etiqueta `deploy-vX.Y.Z`; nunca reutilices una etiqueta para código diferente. Restaurar archivos no revierte automáticamente la base de datos.

La guía operativa completa está en [docs/despliegue-directadmin.md](docs/despliegue-directadmin.md).
