# Auditoría de pendientes — 6 de noviembre de 2026

Base: `0c10668`. Rama independiente, cambios sin commit.

Se contrasta el árbol versionado en `0c10668`, no los números de línea de octubre. El checkout original tiene trabajo sin commit de reportes (controlador, servicio, CSS, vistas, SQL de prueba y ReportesTest); se conserva y no se incorpora a estas ramas. Una implementación sin commit en otro worktree todavía no cuenta como resuelta en esta base.

## Estado de todos los ítems

| Estado en la base | Ítems | Evidencia / alcance |
| --- | --- | --- |
| Resueltos y presentes | 1, 2, 4–10, 14–25, 27, 28, 30, 39, 42, 46, 51, 61, 65, 67 | Vistas/rutas públicas, login con Hash::check, reportes sin tablas inventario/lote, movimientos, pagos, métodos de pago, contadores, nombres de archivos, estilos, ofertas, migraciones y pruebas ya versionados. #42 reporta «Error al cargar el dashboard». #61 ya está en main. |
| Resuelto aunque figuraba abierto | 3 | show ya contiene where('Producto.codigo', $codigo); la ruta sigue desactivada y su vista no existe, por decisión documentada. No activarla. |
| Parcial | 26 | Ingreso/salida y ofertas muestran @error y recuperan el modal; creación/edición del catálogo aún no muestran sus errores. |
| Vigentes: autorización | 11, 12, 13, 64 | Varias rutas administrativas solo exigen auth; rol acepta cualquier string; selector ofrece Jefe sin política. #13 es una política deliberada, no una vulnerabilidad: conservar y documentar el 404. |
| Vigente: portabilidad | 66 | Migraciones con PascalCase, modelos y queries mezclados; SQLite/Windows no detectan el riesgo de MySQL Linux. |
| Vigentes: datos de producto | 36, 37, 55, 63 | Consulta copiada; precio y precioVenta divergen; relación inversa de imagen usa id en vez de codigo; faltan regresiones de stock. #55 no es código muerto: el modelo se usa al guardar y cargar imágenes. |
| Vigentes: interacción y rutas | 38, 43, 44 | Clientes conserva script inline y toggle duplicado; JS fabrica URLs; sidebar mezcla url/is y route/routeIs. Proveedores ya genera la URL de eliminación y clientes la del pago. |
| Vigentes: configuración y uso | 34, 35 | UTC/en y regla user-select universal en resources/css/theme/base.css (se trasladó desde app.css). |
| Vigentes: limpieza confirmada | 33, 45, 47, 49, 50, 52, 53, 54, 56, 59 | Assets vacíos/placeholder, vistas sin include, controladores sin rutas y modelos sin relaciones consumidoras. Búsqueda global antes de retirar. |
| No eliminar | 48 | partials/footer está vacío pero layouts/app lo incluye: es un punto de extensión activo. |
| Sin trabajo versionado | 57 | viejo/ no aparece en git ls-files. Git no conserva carpetas vacías. |
| Aclarado | 31, 60 | laravel/pao es real: su manifiesto lo describe como salida optimizada para agentes de herramientas de pruebas; no es Pail. CheckRole sí se usa con FQCN en las rutas de ofertas y creación; solo VersionController es import huérfano. |
| Decisiones y deuda aún abiertas | 29, 32, 40, 41, 58, 62 | Procedimientos SQL sin llamadas desde app; Spatie instalado sin uso; filtros redundantes de SoftDeletes y política de cascada sin definir; overlays y Bootstrap conviven; respaldo SQL conservado. |

## Unidades de corrección independientes

Todas parten de `0c10668`, sin commits ni dependencias entre ramas. Cada worktree contiene su informe docs/revision-*.md, alcance, pruebas y limitaciones.

| Worktree bajo .worktrees/ | Rama | Ítems |
| --- | --- | --- |
| [documentacion](revision-documentacion.md) | revision/documentacion | Auditoría de los 67 ítems y corrección de guías obsoletas |
| [roles](../../roles/docs/revision-roles.md) | fix/autorizacion-roles | 11, 12, 13, 64 y coherencia del acceso a reportes |
| [tablas](../../tablas/docs/revision-tablas.md) | fix/tablas-linux | 66; incluye migración conservadora para instalaciones existentes |
| [productos](../../productos/docs/revision-productos.md) | fix/consulta-productos-stock | 26, 36, 37, 55, 63 |
| [frontend](../../frontend/docs/revision-frontend.md) | refactor/rutas-frontend | 38, 43, 44 |
| [chile](../../chile/docs/revision-chile.md) | fix/fecha-idioma-chile | 34 |
| [seleccion](../../seleccion/docs/revision-seleccion.md) | fix/seleccion-texto | 35 |
| [limpieza](../../limpieza/docs/revision-limpieza.md) | chore/codigo-sin-uso | 33, 45, 47, 49, 50, 52–54, 56, 59 y parte de 60 |

Independiente significa que cada una se puede probar sobre la base; no significa ausencia de conflictos al integrar. Roles/frontend comparten sidebar y algunas vistas; productos/frontend comparten el formulario y JS de inventario; tablas/productos/roles comparten modelos/controladores y tablas referidas por pruebas. Al integrar, conservar las rutas nombradas, la autorización, el scope y los nombres de tablas minúsculos. Aplicar también esa normalización a las queries de reportes de la sesión previa. Los informes de ramas no deben marcar como integrado un cambio aún sin commit.

## Decisiones conservadas

- #29/#62: decidir si los procedimientos se mantienen como herramienta soportada o solo como legado. No importarlos como parte de la instalación; SP_RegistrarDetalleVenta todavía usa idVenta en lugar de idSalida y el archivo incluye delimitadores/debug impropios. No se altera una interfaz SQL externa sin decidir su soporte.
- #32: Spatie no participa en la autorización actual. Adoptarlo exigiría migración de permisos; retirarlo exigiría actualizar Composer/lock y validar instalación. No es necesario para cerrar el hueco de permisos de #11.
- #40: SoftDeletes ya excluye modelos principales, pero las queries crudas y los joins deben conservar sus filtros. La cascada lógica puede borrar historia comercial: definirla antes de modificarla. Los whereNull redundantes de Eloquent siguen siendo deuda menor.
- #41: estandarizar modales requiere acordar el contrato de foco, Escape, cierre, formularios y estilos; no se cambia el sistema visual durante una corrección de URLs.
- #48/#58: conservar el footer incluido y el respaldo histórico; una eliminación del respaldo requiere decidir su política de conservación.

## Revisión y pruebas

La inspección estática usa git show/status/worktree, rg y lectura de rutas, controladores, modelos, vistas, assets, migraciones, seeders, manifiestos y pruebas. La suite base tiene 14 tests (migraciones/FK, páginas públicas y ofertas/POS), no solo stubs. Las pruebas automatizadas usan SQLite en memoria y una clave de prueba, sin leer el .env original ni ejecutar SQL contra la BD de trabajo. Ver resultados finales más abajo.

Para revisar cambios nuevos aún sin commit: `git -C .worktrees/<grupo> status --short`, `git -C .worktrees/<grupo> diff` y abrir los archivos no rastreados del informe y pruebas. Un diff entre ramas todavía será vacío porque todas apuntan al mismo commit. No cambiar de rama dentro de otro worktree ni borrar los worktrees: contienen el trabajo pendiente de revisión.

## Resultados ejecutados (2026-11-06)

- Pest con PHP 8.4.23 y SQLite en memoria: **14 tests, 69 aserciones, todos pasan**.
- `php artisan route:list --json` y `php artisan view:cache`: correctos.
- Build de frontend no repetido: esta rama no modifica entradas JS/CSS de Vite.
- No se realizó prueba visual ni interacción real en navegador. Los pasos manuales indicados arriba quedan pendientes para revisión.
- Dependencias existentes reutilizadas localmente; no se modificaron manifiestos/lockfiles ni el .env original. En un checkout limpio instalar con Composer/npm antes de repetir pruebas.

## Entrega por rama

| Rama | Resumen | Pest (tests/aserciones) | Verificaciones adicionales |
| --- | --- | --- | --- |
| revision/documentacion | Backlog contrastado y guías actuales de instalación, datos, módulos y arquitectura | 14 / 69 | Rutas y compilación Blade; enlaces relativos revisados |
| fix/autorizacion-roles | Administración protegida, roles canónicos, 404 documentado, menú coherente | 39 / 156 | Sintaxis PHP; Pint de tests; rutas y Blade; CRUD permitido al administrador y denegado al cajero |
| fix/tablas-linux | Tablas canónicas y migración conservando datos/FK | 17 / 122 | Sintaxis PHP; Pint de tests/migración; rutas y Blade; MySQL Linux pendiente |
| fix/consulta-productos-stock | Scope compartido, precio coherente, imagen inversa, validación y regresión de stock | 24 / 115 | Sintaxis PHP; Pint de tests; rutas/Blade; build Vite |
| refactor/rutas-frontend | Clientes extraído a JS, URLs nombradas y sidebar; alias dashboard y duplicado de reportes corregidos | 14 / 69 | node --check; rutas/Blade; route:cache; build Vite |
| fix/fecha-idioma-chile | America/Santiago y es configurables; entorno ejemplo actualizado | 14 / 69 | Sintaxis PHP; rutas/Blade; bootstrap confirmó America/Santiago / es |
| fix/seleccion-texto | Retiro de user-select universal | 14 / 69 | Rutas/Blade; build Vite |
| chore/codigo-sin-uso | Retiro de consumidores inexistentes, assets vacíos e imports huérfanos | 14 / 69 | Búsqueda global; sintaxis PHP; rutas/Blade; build Vite |

Los ocho worktrees tienen el mismo HEAD 0c10668 y sus cambios están sin stage/commit. Se comprobó con ReflectionClass que cada uno carga su propio Producto.php, sin incorporar el código de otro grupo ni los cambios previos de reportes. Para ejecutar pruebas en estos worktrees basta PHP de Herd con `php artisan test`; se reutilizaron paquetes existentes y se copiaron localmente los lanzadores/autoload de pruebas. Para una instalación independiente, usar composer install y npm ci según la guía. No compartir una conexión de desarrollo para tests.

No se hicieron commits, push, merges ni PR. La lista de cambios del checkout main conserva exactamente los tres archivos modificados y los archivos/carpetas sin seguimiento de reportes encontrados al comenzar. Se añadió únicamente una exclusión Git local para que .worktrees/ no aparezca como contenido del proyecto.

La comprobación estática y los tests no cubren interacción real en navegador. Los cuatro builds relevantes pasaron con la advertencia opcional de fontaine; no se instaló esa dependencia. MySQL Linux no se ejecutó porque Docker no tiene motor accesible. No se modificó el .env original ni se ejecutaron migraciones sobre la BD de trabajo.

Las ramas son independientes respecto de la base, pero comparten algunos archivos: docs/pendientes.md en todas; rutas y sidebar entre roles/frontend/limpieza; ProductoController entre tablas/productos/limpieza; modelos y reglas de validación entre tablas/roles/productos. Al integrar, combinar los estados del backlog, conservar los permisos y rutas nombradas y aplicar minúsculas a todo SQL nuevo. La guía de auditoría describe main y no debe sobreescribir informes de ramas ya integradas.
