# Temas del panel

El panel interno y el inicio de sesión permiten elegir por separado el sistema visual y la paleta en `.env`:

```dotenv
APP_TEMA=md3
APP_COLOR=esmeralda
```

| Variable | Valores | Predeterminado |
| --- | --- | --- |
| `APP_TEMA` | `congelandia`, `md3` | `congelandia` |
| `APP_COLOR` | `base` (celeste), `esmeralda`, `aurora`, `coral` | `base` |

Las ocho combinaciones están incluidas en el mismo build. Cambiar estas variables no requiere recompilar. Si Laravel tiene configuración en caché, ejecutar `php artisan config:cache` después del cambio; en desarrollo se puede usar `php artisan config:clear`. Recargar la página.

`APP_TEMA=congelandia` conserva el diseño original. Por ejemplo, con `APP_COLOR=aurora` utiliza la paleta violeta. `APP_TEMA=md3` activa el diseño Material Design 3 con la paleta elegida: barra superior, navegación, tarjetas, tablas, botones, campos, modales, estados y tipografía Roboto.

**El sitio público mantiene sus temas propios e independientes** (`PUBLIC_ESTILO` y `PUBLIC_SELECTOR_ESTILOS`, más la elección guardada en sesión). Estas variables del panel no modifican el sitio público.

## Compatibilidad

Las instalaciones anteriores que usen `APP_TEMA=base`, `esmeralda`, `aurora` o `coral` siguen mostrando Congelandia con esa paleta si no se define `APP_COLOR`. Un color explícito en `APP_COLOR` prevalece. Los valores desconocidos se resuelven a `congelandia` o `base`; nunca se construyen rutas a archivos con texto arbitrario del entorno.

La selección se resuelve en [config/theme.php](../config/theme.php). El [layout del panel](../resources/views/layouts/app.blade.php) carga los estilos de la paleta y del sistema visual después del CSS de cada módulo mediante [partials/theme.blade.php](../resources/views/partials/theme.blade.php). Se puede cambiar entre temas sin cambiar plantillas ni controladores.

## Paletas Material

MD3 usa el esquema claro **Tonal Spot** de [Material Color Utilities](https://github.com/material-foundation/material-color-utilities), con contraste estándar. El color principal de cada paleta Congelandia es la semilla HCT. Los colores resultantes se adaptan a roles Material (`primary`, `on-primary`, contenedores, superficies, bordes, etc.), por lo que un botón MD3 puede ser más oscuro que la semilla para mantener el contraste. No se aplica un tinte uniforme a la interfaz.

Los estados de éxito y advertencia tienen pares tonales propios; el error usa los roles oficiales. El adaptador incluye foco de teclado visible, capas de estado hover/pressed, controles deshabilitados y reducción de movimiento. Bootstrap conserva la lógica de sus componentes.

Los roles CSS se generan en [md3-colors.css](../resources/css/theme/md3-colors.css) y se versionan. No hay generación de colores en el navegador. Para regenerarlos tras cambiar una semilla:

```bash
npm run theme:generate
npm run theme:test
npm run build
```

`npm run dev` y `npm run build` regeneran las paletas automáticamente. Para validar la configuración y la independencia del sitio público: `php artisan test --compact tests/Feature/TemasTest.php`.
