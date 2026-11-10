# Temas del panel

Los administradores eligen el sistema visual y la paleta en **Ajustes → Apariencia**. Al guardar, la selección se aplica al panel y al inicio de sesión de ese navegador. La preferencia pertenece al dispositivo/navegador: no se asocia a una cuenta ni cambia otros dispositivos.

Se guarda durante un año en una cookie persistente, cifrada y validada por Laravel, independiente de la cookie de sesión. Cerrar sesión o iniciar con otra cuenta conserva la apariencia local. Borrar las cookies del sitio elimina esa preferencia. Cada perfil o navegador tiene su propia elección.

Las rutas `GET /ajustes` y `PUT /ajustes` requieren autenticación y rol Administrador; el menú oculta el módulo al cajero. El guardado valida las listas de temas y colores permitidos y no modifica `.env`, la configuración global ni la base de datos.

`.env` define únicamente los valores iniciales para navegadores sin una preferencia guardada:

```dotenv
APP_TEMA=md3
APP_COLOR=esmeralda
```

| Variable | Valores | Predeterminado |
| --- | --- | --- |
| `APP_TEMA` | `congelandia`, `md3`, `win7` | `congelandia` |
| `APP_COLOR` | `base` (celeste), `esmeralda`, `aurora`, `coral` | `base` |

Las doce combinaciones están incluidas en el mismo build. Guardar desde Ajustes no requiere recompilar ni limpiar caché. Si se cambian los valores iniciales en `.env` y Laravel tiene configuración en caché, ejecutar `php artisan config:cache`; en desarrollo se puede usar `php artisan config:clear`.

`APP_TEMA=congelandia` conserva el diseño original. Por ejemplo, con `APP_COLOR=aurora` utiliza la paleta violeta. `APP_TEMA=md3` activa el diseño Material Design 3 con la paleta elegida: barra superior, navegación, tarjetas, tablas, botones, campos, modales, estados y tipografía Roboto.

**El sitio público mantiene sus temas propios e independientes** (`PUBLIC_ESTILO` y `PUBLIC_SELECTOR_ESTILOS`, más la elección guardada en sesión). Estas variables del panel no modifican el sitio público.

## Compatibilidad

Las instalaciones anteriores que usen `APP_TEMA=base`, `esmeralda`, `aurora` o `coral` siguen mostrando Congelandia con esa paleta si no se define `APP_COLOR`. Un color explícito en `APP_COLOR` prevalece. Los valores desconocidos se resuelven a `congelandia` o `base`; nunca se construyen rutas a archivos con texto arbitrario del entorno.

Los valores iniciales y los assets permitidos se definen en [config/theme.php](../config/theme.php). [DeviceTheme](../app/Support/DeviceTheme.php) resuelve la cookie de cada petición sin mutar la configuración compartida. El [layout del panel](../resources/views/layouts/app.blade.php) carga los estilos de la paleta y del sistema visual después del CSS de cada módulo mediante [partials/theme.blade.php](../resources/views/partials/theme.blade.php).

## Paletas Material

MD3 usa el esquema claro **Tonal Spot** de [Material Color Utilities](https://github.com/material-foundation/material-color-utilities), con contraste estándar. El color principal de cada paleta Congelandia es la semilla HCT. Los colores resultantes se adaptan a roles Material (`primary`, `on-primary`, contenedores, superficies, bordes, etc.), por lo que un botón MD3 puede ser más oscuro que la semilla para mantener el contraste. No se aplica un tinte uniforme a la interfaz.

Los estados de éxito y advertencia tienen pares tonales propios; el error usa los roles oficiales. El adaptador incluye foco de teclado visible, capas de estado hover/pressed, controles deshabilitados y reducción de movimiento. Bootstrap conserva la lógica de sus componentes.

Los roles CSS se generan en [md3-colors.css](../resources/css/theme/md3-colors.css) y se versionan. No hay generación de colores en el navegador. Para regenerarlos tras cambiar una semilla:

```bash
npm run theme:generate
npm run theme:test
npm run build
```

`npm run dev` y `npm run build` regeneran las paletas automáticamente. Para validar configuración, permisos, persistencia y aislamiento: `php artisan test --compact tests/Feature/TemasTest.php tests/Feature/AjustesTest.php`.

## Windows 7 / Aero

```dotenv
APP_TEMA=win7
APP_COLOR=aurora
```

El tema `win7` usa [7.css](https://khang-nd.github.io/7.css/), instalado y fijado como dependencia npm. Su hoja `7.scoped.css` se importa desde [win7.css](../resources/css/theme/win7.css) y solo se activa en el panel con la clase `win7`. Los estilos se compilan localmente con Vite; no requieren un CDN.

La paleta seleccionada aporta el acento a los marcos Aero, la navegación y los estados de los controles. Los botones conservan el relieve de Windows 7, los campos tienen bordes hundidos y los modales usan marcos de ventana. Se mantiene Segoe UI y se amplían los controles en móvil. El adaptador conserva las casillas y radios nativos visibles para admitir las diferentes estructuras de etiquetas de los formularios existentes.

Las acciones destructivas usan tonos rojos propios en reposo, hover, pulsación y foco, incluso en los botones de icono y al quitar productos de la boleta. Estos estados son independientes de `APP_COLOR`.

7.css aporta estilos sin JavaScript. Bootstrap y los scripts actuales siguen controlando los modales, el menú y el POS. No se agregan controles decorativos de ventana sin funcionalidad.
