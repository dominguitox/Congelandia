# Documentación de Congelandia

Sistema de gestión para Congelandia: catálogo público, ventas en punto de venta (POS), inventario, clientes con deudas, proveedores, categorías, historial de ventas y reportes. Desarrollado en Laravel sobre MySQL para el portafolio de título.

## Índice de documentos

| Documento | Contenido |
| --- | --- |
| [instalacion.md](instalacion.md) | Requisitos, instalación completa, carga de SQL y solución de problemas |
| [arquitectura.md](arquitectura.md) | Stack tecnológico, estructura de carpetas, flujo de una petición y manejo de errores |
| [rutas.md](rutas.md) | Referencia completa de todas las rutas del sistema (método, URL, nombre, controlador, roles) |
| [modelos-datos.md](modelos-datos.md) | Esquema de base de datos, modelos Eloquent, relaciones y procedimientos almacenados |
| [migraciones.md](migraciones.md) | Migraciones Laravel del esquema completo (rama `migracion-sql-a-laravel`, pendiente de merge) |
| [modulos.md](modulos.md) | Funcionalidad de cada módulo del sistema, vista por vista |
| [estilos.md](estilos.md) | Arquitectura CSS, componentes compartidos y sistema de temas (APP_TEMA) |
| [responsividad.md](responsividad.md) | Alcance, breakpoints y cambios para que la app interna se vea bien en celular (rama `responsividad-movil`) |
| [convenciones.md](convenciones.md) | Convenciones de código vigentes y recomendadas para el equipo |
| [pendientes.md](pendientes.md) | Backlog de bugs y deuda técnica identificados en la auditoría, por prioridad |

## Orden de lectura sugerido

1. **instalacion.md** si vas a montar el proyecto por primera vez en tu máquina.
2. **arquitectura.md** para entender cómo está construido todo.
3. **rutas.md** y **modulos.md** como referencia al desarrollar una funcionalidad.
4. **modelos-datos.md** al tocar base de datos, modelos o consultas.
5. **estilos.md** al crear o modificar vistas, componentes o temas.
6. **convenciones.md** antes de abrir un pull request.
7. **pendientes.md** para saber qué está roto o pendiente antes de trabajar en un módulo.

## Estado del proyecto

Proyecto en desarrollo activo (portafolio de título). Los bugs y pendientes conocidos están centralizados en [pendientes.md](pendientes.md); revisa ese documento antes de reportar que "algo no funciona".
