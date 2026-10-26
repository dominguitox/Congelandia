# Congelandia

Sistema de gestión para la tienda Congelandia (productos congelados): catálogo público, punto de venta (POS), inventario con stock calculado, clientes con compras a crédito, proveedores, categorías, historial de ventas y reportes.

**Estado:** en desarrollo activo (portafolio de título). Bugs conocidos y pendientes: [docs/pendientes.md](docs/pendientes.md)

## Stack

| Capa | Tecnología |
| --- | --- |
| Backend | Laravel 13, PHP >= 8.4 |
| Base de datos | MySQL 8 |
| Frontend | Blade + Bootstrap 5 + Tailwind 4 |
| Assets | Vite 8 |
| Tests | Pest (solo stubs, sin cobertura de negocio) |

## Requisitos

- PHP >= 8.4
- Composer
- Node.js (con npm)
- MySQL 8
- Git

> Opcional: Laravel Herd instala PHP, Composer y Node.js automáticamente (con PHP reciente).

## Instalación

```bash
git clone https://github.com/dominguitox/Congelandia
cd Congelandia
composer run setup
```

Después:

1. Configurar `.env` con credenciales MySQL (`DB_CONNECTION=mysql`, host/puerto/bd/usuario/password).
2. **Importar los SQL del negocio** (las migraciones solo cubren tablas de framework):

```bash
mysql -u root -p < "create table.sql"      # esquema real de negocio
mysql -u root -p < "inserts.sql"           # datos semilla
mysql -u root -p < "procedimientos.sql"    # stored procedures (opcional)
```

> **En la rama `migracion-sql-a-laravel` este paso ya no es necesario:** el esquema completo vive en migraciones Laravel y `composer run setup` lo crea todo. Ver [docs/migraciones.md](docs/migraciones.md) (rama pendiente de merge).

Guía completa y solución de problemas: [docs/instalacion.md](docs/instalacion.md)

## Desarrollo

```bash
composer run dev
```

Levanta en paralelo: `php artisan serve` (app en http://localhost:8000), worker de colas y Vite con hot reload.

Otros scripts:

| Comando | Qué hace |
| --- | --- |
| `composer run setup` | instalación inicial completa (dependencias, `.env`, key, migraciones, build) |
| `composer run dev` | entorno de desarrollo (serve + queue + vite) |
| `composer run test` | limpia config y corre la suite Pest |
| `npm run build` | compila assets de producción |

## Estructura del proyecto

```
app/Http/Controllers   # Controladores (lógica de cada módulo)
app/Http/Middleware    # CheckRole (roles caseros sobre Usuario.rol)
app/Models             # 19 modelos Eloquent
bootstrap/app.php      # Registro: rutas, alias middleware, handler global de excepciones
docs/                  # Documentación del proyecto
database/              # Migraciones de framework (negocio va en SQL raíz), seeders
resources/views        # Vistas Blade por módulo + layouts + partials
resources/css, /js     # Assets por módulo (algunos vacíos — ver docs/pendientes.md)
routes/web.php         # Todas las rutas
create table.sql       # Esquema real de negocio (fuente de verdad de la BD)
inserts.sql            # Datos semilla
procedimientos.sql     # Stored procedures
MWB.mwb                # Diagrama ER (Workbench)
```

## Documentación

La documentación detallada vive en [`docs/`](docs/) (leer primero [docs/index.md](docs/index.md)):

| Documento | Tema |
| --- | --- |
| [docs/instalacion.md](docs/instalacion.md) | Instalación paso a paso, SQL y troubleshooting |
| [docs/arquitectura.md](docs/arquitectura.md) | Stack, estructura, flujo de petición, errores |
| [docs/rutas.md](docs/rutas.md) | Tabla de las 30+ rutas con controlador y roles |
| [docs/modelos-datos.md](docs/modelos-datos.md) | Esquema, modelos, relaciones y SPs |
| [docs/migraciones.md](docs/migraciones.md) | Migraciones del esquema completo (rama `migracion-sql-a-laravel`) |
| [docs/modulos.md](docs/modulos.md) | Funcionalidad por módulo y estado actual |
| [docs/convenciones.md](docs/convenciones.md) | Convenciones del código y del equipo |
| [docs/pendientes.md](docs/pendientes.md) | Backlog de bugs y refactor (60+ ítems priorizados) |

## Registro de errores

El sistema guarda excepciones no catalogadas en la tabla `log_errors` (definido en `bootstrap/app.php`) y redirige con un flash "Ocurrió un error en el sistema". Para depurar, revisa esa tabla o `storage/logs/laravel.log`.
