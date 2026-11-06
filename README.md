# Congelandia

Sistema de gestión de productos congelados: catálogo público, POS, inventario, clientes con crédito, proveedores, categorías, historial y reportes.

Laravel 13 · PHP >= 8.4 · MySQL 8 · Blade/Bootstrap 5/Tailwind 4 · Vite 8 · Pest.

## Instalación

1. Instalar PHP >= 8.4, Composer, Node compatible con Vite 8 y MySQL 8.
2. Ejecutar `composer install`, copiar `.env.example` a `.env` y configurar una base de datos vacía.
3. Ejecutar `php artisan key:generate`, `php artisan migrate --seed`, `npm ci --ignore-scripts` y `npm run build`.
4. Iniciar desarrollo con `composer run dev`.

Las migraciones ya cubren el negocio y el framework. No importar los SQL de legado sobre la base migrada. `composer run setup` usa migrate:fresh y borra tablas: reservarlo para un entorno nuevo/desechable, después de configurar su conexión.

## Verificación

`composer run test` ejecuta Pest con SQLite en memoria según phpunit.xml. La base tiene pruebas de esquema/FK, páginas públicas y promociones/POS. Falta cobertura de stock en la base (ver ramas de revisión). `npm run build` compila assets; no sustituye revisar la interacción en navegador. SQLite no valida el casing de tablas en MySQL Linux.

## Documentación

Leer [docs/index.md](docs/index.md), [instalación](docs/instalacion.md), [arquitectura](docs/arquitectura.md), [rutas](docs/rutas.md), [modelos](docs/modelos-datos.md), [migraciones](docs/migraciones.md), [módulos](docs/modulos.md) y [convenciones](docs/convenciones.md).

Los pendientes contrastados con código están en [docs/pendientes.md](docs/pendientes.md) y la [auditoría de noviembre](docs/revision-documentacion.md), con grupos, ramas y decisiones abiertas. Los temas y componentes visuales se describen en [estilos](docs/estilos.md).
