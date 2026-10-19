# Estilos y temas

Documentación de la arquitectura CSS de Congelandia: cómo está organizada, cómo se reutilizan los componentes y cómo crear y activar **temas** para distintos clientes.

Relacionado: [convenciones.md](convenciones.md) (reglas de código), [modulos.md](modulos.md) (qué usa cada módulo).

## Objetivo

Todos los módulos del panel comparten el mismo lenguaje visual (cabecera con título/subtítulo, tarjetas de resumen, paneles, tablas, botones y badges). El estilo se define una sola vez en componentes y tokens, de modo que:

- Los módulos nuevos salen "gratis" con solo usar las clases comunes.
- Cambiar la identidad visual de un cliente = cambiar un archivo de tokens.
- No se repite CSS entre páginas.

## Estructura de carpetas

```
resources/css/
├── app.css                  # Punto de entrada global (importa todo lo de abajo)
├── theme/
│   ├── tokens.css           # Variables del tema por defecto (:root)
│   ├── base.css             # Reset suave, tipografía y marco (.app-main, .flash-error)
│   └── themes/              # Temas alternativos (solo sobrescriben tokens)
│       ├── aurora.css
│       ├── esmeralda.css
│       └── coral.css
├── components/              # Componentes compartidos (se importan en app.css)
│   ├── pages.css            # Estructura de página: header, stats, panel, data-table, vacíos
│   ├── layout.css           # Header superior, sidebar y contenedores de módulo
│   ├── cards.css            # Tarjetas/íconos de resumen
│   ├── tables.css           # Tablas con prefijo de módulo (clientes/proveedores/historial)
│   ├── buttons.css          # Botones (genéricos y de módulo)
│   ├── forms.css            # Labels y campos
│   ├── badges.css           # Etiquetas y pills
│   ├── modals.css           # Chrome de modales propios
│   ├── search.css           # Buscadores y filas de acciones
│   ├── states.css           # Estados vacíos / sin resultados
│   └── bootstrap-theme.css  # Alinea Bootstrap (card, table, btn, modal, alert) al tema
└── <modulo>.css             # Estilos propios de un módulo (clientes, pos, reportes, ...)
```

## Orden de importación (cascada)

`app.css` importa, en este orden:

1. Bootstrap (`bootstrap/dist/css/bootstrap.min.css`).
2. `theme/tokens.css` → define las variables.
3. `theme/base.css`.
4. `components/*.css` (incluido `bootstrap-theme.css` al final para poder pisar Bootstrap).

Los CSS de módulo **no** se importan en `app.css`: cada vista los carga con `@push('css')` + `@vite(...)`, por lo que entran **después** de los componentes y pueden sobrescribir.

## Tokens del tema

Viven en `resources/css/theme/tokens.css` dentro de `:root`. Son el único lugar con valores "crudos".

| Grupo | Ejemplos |
| --- | --- |
| Marca | `--color-primary`, `--color-primary-hover`, `--color-primary-soft` |
| Texto | `--color-text`, `--color-text-heading`, `--color-text-muted`, `--color-text-soft` |
| Superficies | `--color-surface`, `--color-surface-alt`, `--color-surface-input`, `--color-page-bg` |
| Bordes | `--color-border`, `--color-border-soft`, `--color-table-header` |
| Estados | `--color-success(-bg)`, `--color-warning(-bg)`, `--color-danger(-bg)`, `--color-info(-bg)`, `--color-purple(-bg)` |
| Shell | `--color-navy-dark`, `--color-navy-mid`, `--color-ice`, `--header-height`, `--sidebar-width` |
| Radios | `--radius-2xs` … `--radius-3xl`, `--radius-pill` |
| Sombras | `--shadow-card`, `--shadow-card-soft`, `--shadow-card-md`, `--shadow-modal` |
| Tipografía | `--font-family`, `--fs-xs` … `--fs-3xl` |
| Espaciado | `--space-xs` … `--space-3xl` |
| Medidas | `--control-height`, `--control-height-sm`, `--module-padding` |

**Regla:** en componentes y módulos usa siempre `var(--...)`; nunca colores/medidas "a mano".

## Componentes genéricos (`components/pages.css`)

Estructura estándar de una página del panel:

```blade
<div class="page">

    <div class="page-header">
        <div class="page-title">
            <h1>Inventario</h1>
            <p>Controla productos, stock, ingresos y mermas</p>
        </div>
        <div class="page-actions">
            <button class="btn-solid">+ Añadir</button>
        </div>
    </div>

    <div class="stats-grid">                     {{-- 4 columnas; .stats-grid--3 para 3 --}}
        <div class="stat-tile">
            <div class="stat-tile-info">
                <span>Total</span>
                <strong>{{ $total }}</strong>
            </div>
            <div class="card-icon proveedores-icon"><svg>…</svg></div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div><h3>Registros</h3><p>Subtítulo</p></div>
        </div>
        <div class="panel-body panel-body--flush">   {{-- --flush quita el padding --}}
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead><tr><th>…</th></tr></thead>
                    <tbody>…</tbody>
                </table>
            </div>
        </div>
    </div>

</div>
```

Clases clave:

| Clase | Uso |
| --- | --- |
| `.page` | Contenedor de la página (padding y color base). |
| `.page-header` / `.page-title` / `.page-actions` | Cabecera con título + subtítulo + acciones. |
| `.stats-grid` | Grilla de tarjetas de resumen (4 col; `--3` para 3). |
| `.stat-tile` / `.stat-tile-info` | Tarjeta de resumen (`.stat-tile--link` para hacerla clicable). |
| `.panel` / `.panel-head` / `.panel-body` | Tarjeta de contenido horizontal. |
| `.data-table-wrap` / `.data-table` | Tabla de datos con cabecera, hover y bordes suaves. |
| `.celda-principal` / `.celda-sub` | Texto principal + secundario en una celda. |
| `.col-acciones` / `.acciones` | Columna y grupo de acciones (botones de icono). |
| `.empty-state` | Estado vacío (ícono + mensaje). |

Íconos de tarjeta (colores): `.proveedores-icon`, `.productos-icon`, `.alerta-icon`, `.warning`, `.success`, `.stat-icon-ingreso`, `.stat-icon-salida`, `.stat-icon-total`.

### Botones (`components/buttons.css`)

- `.btn-solid` (acción principal), `.btn-solid--danger`, `.btn-solid--success`.
- `.btn-soft` (neutro/cancelar), `.btn-outline` (borde).
- `.btn-compact` (alto reducido).
- `.icon-btn` + variantes `.icon-btn--info/danger/success/warn/purple/expand` (acciones de tabla).

### Pills / badges (`components/badges.css`)

- `.pill` + `.pill--primary/success/warning/danger/info/purple/muted`.

### Bootstrap

Los módulos que todavía usan clases Bootstrap (POS, dashboard, modales) se alinean solos vía `components/bootstrap-theme.css`, que replica el estilo de los componentes sobre `.card`, `.table`, `.btn`, `.modal`, `.alert`, `.badge`, `.form-control`, etc. **No** hace falta reescribir su HTML.

## Sistema de temas

Un tema es un archivo que **solo redefine variables** de `:root`; todos los componentes heredan el cambio automáticamente.

### Temas incluidos

| `APP_TEMA` | Apariencia |
| --- | --- |
| `base` (por defecto) | Cian/celeste original |
| `aurora` | Violeta |
| `esmeralda` | Verde |
| `coral` | Rosa/rojo cálido |

### Cómo se activa

1. En `.env`: `APP_TEMA=aurora` (`base` deja el tema por defecto).
2. Limpiar caché de config si está activa: `php artisan config:clear`.
3. El layout `resources/views/layouts/app.blade.php` (y `layouts/public.blade.php`) lee `config('app.tema')` y, si no es `base` y el archivo existe, hace `@vite([...])` del tema **después** de `app.css`.

La clave `app.tema` se define en `config/app.php` (`'tema' => env('APP_TEMA', 'base')`).

### Cómo crear un tema nuevo

1. Copia `resources/css/theme/themes/aurora.css` a `resources/css/theme/themes/<nombre>.css`.
2. Cambia los valores de `:root` (solo los que quieras sobrescribir; hereda el resto).
3. Regístralo como input de Vite en `vite.config.js`:
   ```js
   'resources/css/theme/themes/<nombre>.css',
   ```
4. `npm run build` (o `npm run dev`) y activa con `APP_TEMA=<nombre>`.

> Al desplegar: `public/build` es una salida generada; hay que volver a compilar (`npm run build`) para que el tema exista en el manifest.

## Convenciones para CSS nuevo

1. Un archivo por módulo en `resources/css/<modulo>.css`; regístralo en `vite.config.js` y cárgalo con `@push('css')` (no `@vite` dentro de `@section('content')`).
2. Antes de escribir un estilo, revisa si ya existe un componente en `components/`. Si un patrón se repite en 3+ módulos, muévelo a un componente.
3. Usa `var(--...)`, nunca valores literales de color/tamaño (salvo casos muy puntuales).
4. No re-declarar estilos que ya aporta un componente dentro de la vista o el CSS del módulo.
5. Las vistas deben reutilizar la estructura `.page` / `.page-header` / `.stats-grid` / `.panel` / `.data-table`.

## Mapa de carga por módulo

| Módulo | CSS propio | Componente principal |
| --- | --- | --- |
| clientes | `clientes.css` | `clientes-container`, tablas y modales propios |
| proveedores | `proveedores.css` | `proveedores-*` |
| historial | `historial.css` | `historial-*` |
| inventario | `inventario.css` (vacío, usa genéricos) | `.page` + `.data-table` |
| categorias | `categorias.css` | `.page` + `.data-table` + panel de detalle |
| usuarios | — (usa genéricos) | `.page` + `.data-table` |
| dashboard | `dashboard.css` | `.page` + `.stats-grid` + paneles |
| reportes | `reportes.css` | `.page` + paneles + `.data-table` |
| POS | `pos.css` | `.page` + `.pos-grid` |
| login | `auth.css` | `.auth-card` |
| público (tienda) | `public.css` | clases propias del sitio público |
