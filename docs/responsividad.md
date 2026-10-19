# Responsividad de la app del sistema

Documentación del trabajo realizado en la rama `responsividad-movil` para que el panel interno de Congelandia se vea y se use correctamente en celulares.

Relacionado: [estilos.md](estilos.md) (arquitectura CSS), [convenciones.md](convenciones.md) (reglas de código).

## Alcance

| Incluido | Excluido |
| --- | --- |
| Layout interno (`layout.app`), header, sidebar, footer y todas las vistas que lo extienden. | Sitio público completo (`resources/views/public/`, `resources/views/layouts/public.blade.php`, `resources/css/public.css`) y todo `public/`. |
| Componentes compartidos de `resources/css/components/`. | Cambios de lógica, rutas, controladores, modelos, base de datos o validaciones. |
| CSS propio de cada módulo interno y el JS global del menú. | Corrección de funciones existentes que pudieran estar rotas o ser contradictorias. |

No se modificó ningún archivo dentro de `public/` ni del sitio público. El trabajo es exclusivamente visual y de interacción del menú en pantallas pequeñas.

## Breakpoints usados

| Rango | Uso |
| --- | --- |
| `<= 991.98px` | El sidebar pasa a panel deslizante, el contenido ocupa todo el ancho y aparece el botón de menú en el header. |
| `<= 768px` | Se reduce el padding general (`--module-padding`), se ajustan paneles, tipografía, tablas y controles. |
| `<= 640px` | Las tarjetas de resumen pasan a una columna y las acciones de página ocupan todo el ancho. |
| `<= 576px` | Se compactan los modales propios y los modales Bootstrap. |
| `<= 480px` | Ajustes finos de POS, dashboard, reportes y login. |
| `<= 420px` | Ajustes extremos de modales de clientes y botones de confirmación. |

## Shell: header y sidebar

### Header (`resources/views/partials/header.blade.php`)

- Se agregó un botón hamburguesa (`#btnSidebar`) visible solo en pantallas menores a `992px`.
- El botón solo se renderiza si hay un usuario autenticado, para no mostrarlo en el login.
- Se agregaron `aria-label`, `aria-controls` y `aria-expanded` para accesibilidad.
- Se añadieron clases al bloque del usuario (`.app-user`, `.app-user-name`) para permitir truncado con elipsis sin romper el ancho.

### Layout (`resources/views/layouts/app.blade.php`)

- Se agregó el elemento `.sidebar-overlay` (`#sidebarOverlay`), el fondo oscuro que permite cerrar el menú al tocar fuera.

### Menú lateral (`resources/css/components/layout.css`)

- El botón `.app-menu-btn` permanece oculto en escritorio y se muestra desde `991.98px` hacia abajo.
- En móvil el sidebar se saca de pantalla con `transform: translateX(-100%)` y se muestra con la clase `.sidebar--abierto`.
- El ancho del panel es `min(280px, 82vw)` para dejar siempre una franja visible del contenido.
- Se agregó transición suave, sombra lateral y `100dvh` (con respaldo en `100vh`) para el alto.
- El overlay queda entre el contenido y el sidebar (`z-index: 999`) y el sidebar mantiene su `z-index: 1000`.
- Al abrir el menú se bloquea el scroll del `body` con `body.sidebar-abierto`.
- El contenido principal (`.app-main`) pierde el margen izquierdo de `250px` por debajo de `992px` y pasa a ocupar el 100 % del ancho.
- En móvil se reduce el padding del header, el tamaño del logo y el ancho máximo del bloque de usuario.

### JavaScript (`resources/js/app.js`)

Se agregó un bloque de inicialización que:

1. Abre y cierra el sidebar desde el botón del header.
2. Sincroniza `aria-expanded`.
3. Cierra el menú al tocar el overlay, al presionar `Escape` o al hacer clic en cualquier enlace del menú.
4. Cierra el menú automáticamente si la ventana vuelve a un ancho de escritorio (`>= 992px`).

No se modificó ninguna función existente del archivo (`toggleDetalle`, `toggleFormularioCliente`).

## Componentes compartidos

### `theme/base.css`

- `html` pasó de `width: 100vw` a `width: 100%` para evitar desbordes horizontales por la barra de scroll.
- El margen izquierdo del contenido se elimina en móvil.
- El padding de los módulos se reduce a `--space-md` en pantallas pequeñas.
- El mensaje flash de error ahora se ajusta a los laterales y al ancho completo en móvil.

### `components/pages.css`

- Títulos de página más pequeños y cabeceras con menos margen en móvil.
- Grillas de tarjetas con menor separación y tarjetas más compactas.
- Paneles con padding reducido.
- Las tablas de datos tienen un ancho mínimo de `640px` dentro de su contenedor con scroll horizontal, para que las columnas no se aplasten.
- En `<= 640px` las tarjetas quedan en una columna y los botones de `.page-actions` ocupan todo el ancho.
- El estado vacío reduce su padding.

### `components/tables.css`

- En móvil se reduce el padding de las celdas de las tablas de clientes, proveedores e historial.

### `components/modals.css`

- Los modales propios (proveedores y confirmaciones) limitan su alto al viewport y activan scroll interno.
- Cabecera y pie reducen padding.
- Los botones se reparten el ancho y, en pantallas muy pequeñas, se apilan en columna invertida.

### `components/bootstrap-theme.css`

- Los controles de formulario usan `16px` en móvil para evitar el zoom automático de iOS.
- Los modales Bootstrap reducen márgenes, radios y padding.
- El pie de los modales permite varias líneas y reparte el ancho de los botones.
- Las tarjetas Bootstrap también reducen su padding.

### `components/forms.css` y `components/search.css`

- Las filas de búsqueda y filtros permiten `flex-wrap`.
- Los campos de búsqueda no se desbordan (`min-width: 0`, `max-width: 100%`).
- Los inputs de formularios propios usan `16px` en móvil.

## Módulos

| Módulo | Ajustes realizados |
| --- | --- |
| Dashboard | Lista de ventas y bloques de alertas con menos padding; las ventas se apilan en una columna bajo `480px`. |
| POS | Grilla de productos de dos columnas en móvil, tarjetas compactas, boleta sin altura fija y buscador a ancho completo. |
| Inventario | Usa los ajustes globales de página, tarjetas y tablas; modales Bootstrap adaptados. |
| Clientes | Acciones y formulario a una columna; tablas con scroll interno; botones de pago/edición apilados; modal de historial y de pago con scroll y padding reducido. |
| Proveedores | Cabecera y acciones apiladas; tabla con scroll interno; detalle de productos a una columna; formulario y modales adaptados. |
| Historial | Filtros apilados; tabla con scroll interno; detalle de movimiento con filas que se envuelven y precios alineados a la derecha. |
| Categorías | Grilla principal a una columna; detalle de productos con padding reducido y filas que pueden envolver. |
| Usuarios | Usa los ajustes globales de página, tarjetas, tablas y modales Bootstrap. |
| Reportes | Filtros a una columna; valor numérico con tamaño reducido en pantallas pequeñas. |
| Login | Tarjeta con menos padding en móvil; el shell ya no reserva el ancho del sidebar. |

## Comportamiento esperado en móvil

- El contenido nunca debe generar scroll horizontal en el `body`; las tablas grandes hacen scroll dentro de su propio contenedor.
- El menú lateral se abre con el botón hamburguesa y se cierra tocando fuera, con `Escape` o al elegir una opción.
- Los botones principales de cada página se ven a ancho completo.
- Los modales caben en el ancho disponible y su contenido puede desplazarse verticalmente cuando es más alto que la pantalla.
- El header mantiene logo, usuario y rol sin desbordar; el nombre largo se trunca con elipsis.

## Verificación realizada

1. `npm run build` compila todos los assets sin errores.
2. Se levantó `php artisan serve` y se navegó con un navegador headless autenticado.
3. Se recorrieron las rutas internas `/dashboard`, `/pos`, `/inventario`, `/clientes`, `/proveedores`, `/historial`, `/categorias`, `/usuarios` y `/reportes` a `390px` de ancho.
4. En todas las rutas el `scrollWidth` del documento fue igual al ancho del viewport; las tablas conservaron su scroll interno.
5. Se probaron anchos de `320`, `360`, `390`, `768`, `992` y `1280px` sin desborde del documento ni del header.
6. Se verificó que el menú abre y cierra correctamente, que bloquea el scroll al abrirse y que en escritorio no aparece el botón hamburguesa (el sidebar sigue visible con su margen de `250px`).
7. Se abrieron los modales de inventario, proveedores, categorías e historial de clientes a `390px`, verificando que caben en el ancho y que el contenido alto se puede desplazar.
8. Se revisaron capturas de dashboard, inventario, POS, clientes, login y modales en vista móvil.

Las capturas y scripts de verificación se generaron en la carpeta temporal del entorno y no forman parte del repositorio.

### Observaciones no corregidas

- Durante la verificación aparecieron `404` de `/images/empanadas.jpg` y `/images/default.png` originados por el sitio público. Pertenecen a un alcance excluido de esta rama y no se modificaron.
- Las tablas anchas siguen requiriendo scroll horizontal en celular; es una decisión deliberada para no perder columnas ni reescribir el HTML de cada módulo.
- El nombre del usuario en el header puede truncarse en pantallas de `320px`; el rol se mantiene visible.

## Ajuste posterior: manifiesto de Vite de Usuarios

La vista `resources/views/usuarios/index.blade.php` carga `@vite(['resources/js/usuarios.js'])`, pero ese archivo no estaba registrado como input en `vite.config.js`. Al compilar, Vite no lo incluía en el manifiesto y la vista fallaba con:

```
Unable to locate file in Vite manifest: resources/js/usuarios.js.
```

Se agregó `resources/js/usuarios.js` a los inputs de `vite.config.js`. El archivo existe (está vacío) y ahora se genera su entrada en el manifiesto, por lo que `/usuarios` carga sin ese error.

## Cómo revertir o ajustar

- Todos los cambios de layout están en los bloques `RESPONSIVE` al final de cada archivo CSS.
- Para cambiar el punto de quiebre del menú móvil hay que actualizar el valor `991.98px` en `components/layout.css`, `theme/base.css` y la comparación `992` de `resources/js/app.js`.
- El resto de ajustes por módulo vive en su propio CSS, sin alterar la estructura HTML de las vistas.
