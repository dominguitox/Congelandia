# Selección y copia de texto (#35)

Base: `0c10668`. Rama independiente, cambios sin commit.

Se elimina la regla universal user-select: none del CSS base (el pendiente señalaba app.css, pero se había trasladado a theme/base.css). Se conserva el diseño y se usa la selección nativa del navegador.

Prueba: `npm run build`; seleccionar y copiar un código, RUT, nombre y texto de un input en inventario/clientes/POS. Comprobar selección con teclado y mouse en escritorio y pulsación prolongada en móvil. Sin dependencias con otras ramas.

## Resultados ejecutados (2026-11-06)

- Pest con PHP 8.4.23 y SQLite en memoria: **14 tests, 69 aserciones, todos pasan**.
- `php artisan route:list --json` y `php artisan view:cache`: correctos.
- `npm run build` con Vite 8.2.1: correcto. Advertencia no bloqueante del plugin de fuentes: fontaine opcional ausente.
- No se realizó prueba visual ni interacción real en navegador. Los pasos manuales indicados arriba quedan pendientes para revisión.
- Dependencias existentes reutilizadas localmente; no se modificaron manifiestos/lockfiles ni el .env original. En un checkout limpio instalar con Composer/npm antes de repetir pruebas.
