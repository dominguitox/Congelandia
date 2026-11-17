# Códigos de barras internos para productos

## Objetivo y alcance

El modal **Registrar Nuevo Producto** permite generar un código numérico para
productos que llegan sin un código de barras de origen. El código se coloca en el
campo existente `codigo`, de modo que la persona pueda revisarlo, reemplazarlo o
editarlo antes de guardar.

Esta entrega **no imprime etiquetas ni genera imágenes de códigos de barras**.
Solo crea el valor numérico que más adelante podrá representarse como EAN-13.

## Formato elegido: EAN-13 de uso interno con fecha y secuencia

Los códigos generados tienen exactamente **13 dígitos numéricos**:

```text
20 + AAMMDD + secuencia diaria (0001-9999) + 1 dígito verificador
```

Ejemplo:

```text
20 261116 0001 2
│  │      │    └─ dígito verificador
│  │      └────── primer producto interno creado el 16-11-2026
│  └───────────── fecha de generación (año, mes y día)
└──────────────── prefijo interno
```

- `20` es el prefijo reservado por GS1 para códigos de circulación interna o
  restringida. Congelandia lo usa únicamente para identificar productos dentro de
  su propio catálogo.
- `AAMMDD` indica el día en que se generó el código, con la zona horaria de la
  aplicación (`America/Santiago` por defecto).
- La secuencia de cuatro dígitos es el número de producto interno de ese día.
  Se calcula a partir de los códigos ya registrados para esa misma fecha, no de
  forma aleatoria. Al día siguiente la secuencia vuelve a `0001`.
- El último dígito se calcula con el algoritmo estándar EAN-13.

Por qué no es aleatorio: un código aleatorio puede coincidir con el de otro
producto por azar. Con fecha + secuencia, dos días distintos nunca comparten
código, y dentro de un mismo día el número es consecutivo y verificable. Además,
todo el rango `20` está reservado para uso interno, por lo que la probabilidad de
chocar con el código de un producto comercial es prácticamente nula.

El valor se guarda como texto. **Los ceros iniciales son significativos** y no
deben convertirse a un tipo numérico.

### Cómo leer un código

| Código          | Significado                                             |
| --------------- | ------------------------------------------------------- |
| `2026111600012` | Producto interno n.º 1 del 16-11-2026                   |
| `2026111600371` | Producto interno n.º 37 del 16-11-2026                  |
| `2026111699993` | Producto interno n.º 9999 del 16-11-2026 (último del día) |

### Dígito verificador

Para una base de 12 dígitos se multiplica por 1 las posiciones impares y por 3
las pares (contando desde la izquierda). Luego:

```text
verificador = (10 - (suma % 10)) % 10
```

Ejemplo con la base `590123412345`:

```text
5×1 + 9×3 + 0×1 + 1×3 + 2×1 + 3×3 + 4×1 + 1×3 + 2×1 + 3×3 + 4×1 + 5×3
= 83
(10 - (83 % 10)) % 10 = 7
```

Resultado: `5901234123457`. La prueba automatizada usa este valor y otro caso
conocido para comprobar el cálculo.

## Códigos internos frente a códigos comerciales

- Un código generado por Congelandia **no es un código GS1 registrado**.
- No se promete que sea aceptado por sistemas externos, marketplaces, bancos de
  datos comerciales ni lectores de otras empresas.
- Los códigos de origen escaneados o escritos manualmente se conservan sin
  cambios. El formato EAN-13 solo se aplica a los códigos creados con el botón
  **Generar**.
- El campo mantiene el límite histórico de 50 caracteres para no romper
  productos existentes ni instalaciones ya desplegadas.

## Unicidad y solicitudes simultáneas

1. El endpoint `POST /productos/codigo-interno` consulta los códigos de **hoy**,
   **incluidos los productos eliminados lógicamente**, porque su código sigue
   ocupando la clave primaria.
2. Calcula la secuencia más alta ya usada para esa fecha y propone la siguiente.
   Si esa propuesta ya existe, avanza hasta encontrar un número libre (máximo 10
   intentos).
3. Al guardar el producto se vuelve a validar con la regla `unique` existente.
4. La clave primaria de `producto.codigo` es la garantía final. Si dos
   solicitudes eligen el mismo valor entre la validación y el `INSERT`, la
   segunda recibe el mensaje:
   **“Este código ya está registrado. Genera un código interno nuevo o ingresa
   otro distinto.”**
5. El formulario conserva los datos ingresados, vuelve a abrir el modal y deja
   disponible el botón **Generar** para reintentar.

No se reservan códigos antes de guardar: si dos personas generan al mismo tiempo
sin haber guardado todavía, pueden ver la misma propuesta. La unicidad definitiva
la impone la base de datos al registrar el producto, y el formulario invita a
regenerar si hubo choque.

### Cuando se agota el día

La secuencia diaria admite **9.999 productos por día**. Al intentar generar el
número 10.000, el endpoint responde con un aviso y no entrega un código fuera del
formato:

> Se agotó la secuencia diaria de códigos internos (9999). Ingresa un código
> manualmente.

El ingreso manual y el escaneo siguen disponibles sin restricciones.

## Límites y uso futuro como código imprimible

- Capacidad: **9.999 productos internos por día**. El contador se reinicia cada
  día y no se reutilizan números de fechas anteriores.
- El límite de 13 dígitos es el estándar EAN-13; no se puede ampliar sin cambiar
  de simbología.
- Para imprimir y escanear más adelante se debe usar un generador de simbología
  **EAN-13** que reciba los 13 dígitos tal cual, sin recalcular ni eliminar el
  dígito verificador.
- Al escanear, el código se lee completo; la fecha y la secuencia se pueden
  interpretar manualmente si se necesita rastrear cuándo se creó el producto.
- La etiqueta deberá respetar zonas de silencio, ancho de módulo, altura de
  barras y resolución de impresión. Conviene validar la etiqueta física con los
  lectores que se usarán en el local.
- No se debe afirmar que el código funciona en sistemas externos sin una prueba
  previa con esos sistemas.

## Despliegue

Este cambio **no requiere migraciones ni cambios de esquema**. El campo
`producto.codigo` ya existe como `string(50)` y la clave primaria sigue siendo la
misma. No hay SQL incremental que aplicar en DirectAdmin.

Archivos principales:

- `app/Support/CodigoBarrasInterno.php`: formato y dígito verificador.
- `app/Http/Controllers/ProductoController.php`: generación, comprobación de
  unicidad y mensaje de duplicado al guardar.
- `routes/web.php`: ruta administrativa de generación.
- `resources/views/inventario/index.blade.php`: botón, ayuda y estado accesible.
- `resources/js/producto.js`: solicitud, estados de carga/error y foco posterior.
- `tests/Feature/CodigoBarrasProductoTest.php`: formato, endpoint y duplicados.
