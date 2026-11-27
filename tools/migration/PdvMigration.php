<?php

namespace Congelandia\Migration;

use DateTimeImmutable;
use RuntimeException;

/** Conversión puntual: sin conexión al destino ni dependencias de Laravel. */
final class PdvMigration
{
    /** Ejecuta el lote completo y revierte si MySQL rechaza cualquier sentencia. */
    public static function execute(\PDO $pdo, string $sql): array
    {
        try {
            $last = [];
            // Solo lotes generados aquí: datos en hex, una sentencia por línea,
            // sin rutinas ni delimitadores dentro de los literales. No requiere
            // habilitar múltiples sentencias en la conexión de Laravel.
            $sql = preg_replace('/^--.*$/m', '', str_replace("\r\n", "\n", $sql));
            foreach (explode(";\n", $sql) as $part) {
                if (trim($part) === '') {
                    continue;
                }
                $statement = $pdo->query($part);
                if ($statement->columnCount()) {
                    $last = $statement->fetchAll(\PDO::FETCH_ASSOC);
                }
                $statement->closeCursor();
            }

            return $last;
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            try {
                $pdo->exec('DO RELEASE_LOCK(@pdv_lock_name)');
            } catch (\Throwable) {
                // Conservar el error original si se perdió la conexión.
            }
            throw $error;
        }
    }

    public static function load(string $directory, ?string $cutoff = null): array
    {
        $manifest = self::json($directory.'/manifest.json');
        if (! preg_match('/^[a-f0-9]{64}$/', $manifest['source_sha256'] ?? '')) {
            throw new RuntimeException('Falta un SHA-256 de origen válido.');
        }
        foreach ($manifest['files'] as $file => $hash) {
            if (basename($file) !== $file || ! is_file($directory.'/'.$file)
                || ! hash_equals($hash, hash_file('sha256', $directory.'/'.$file))) {
                throw new RuntimeException("Exportación alterada o incompleta: $file");
            }
        }
        foreach (['PRODUCTOS', 'DEPARTAMENTOS', 'HISTORIAL_INVENTARIO'] as $table) {
            if (! isset($manifest['files'][$table.'.json'])) {
                throw new RuntimeException("Falta la tabla $table en el manifiesto.");
            }
        }
        foreach (['CLIENTES', 'ABONOS', 'PROV', 'VENTAS', 'VENTATICKETS', 'VENTATICKETS_ARTICULOS', 'FACTURAS', 'MOVIMIENTOS', 'OPERACIONES', 'PROMOCIONES_POR_CANTIDAD'] as $table) {
            if (! array_key_exists($table, $manifest['counts']) || $manifest['counts'][$table] !== 0) {
                throw new RuntimeException("$table no está vacía o no fue inventariada. Esta conversión solo cubre catálogo y apertura; revisar el alcance.");
            }
        }
        $cutoff ??= $manifest['source_modified_at'];
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $cutoff);
        if (! $date || $date->format('Y-m-d H:i:s') !== $cutoff) {
            throw new RuntimeException('Fecha de corte inválida: usar AAAA-MM-DD HH:MM:SS.');
        }
        $history = self::json($directory.'/HISTORIAL_INVENTARIO.json');
        usort($history, fn ($a, $b) => [$a['CUANDO_FUE'], $a['ID']] <=> [$b['CUANDO_FUE'], $b['ID']]);
        $last = [];
        $discontinuities = 0;
        foreach ($history as $row) {
            if (substr($row['CUANDO_FUE'], 0, 19) > $cutoff) {
                throw new RuntimeException('El historial contiene movimientos posteriores al corte.');
            }
            $code = trim($row['CODIGO_PRODUCTO']);
            if (isset($last[$code])) {
                $previous = self::after($last[$code]);
                if ($previous !== null && abs($previous - $row['HABIA']) > 0.001) {
                    $discontinuities++;
                }
            }
            $last[$code] = $row;
        }
        $departments = [];
        foreach (self::json($directory.'/DEPARTAMENTOS.json') as $row) {
            $departments[$row['ID']] = self::text($row['NOMBRE'], 100, 'departamento');
        }
        $products = [];
        $seen = [];
        $stockErrors = [];
        foreach (self::json($directory.'/PRODUCTOS.json') as $row) {
            $code = self::text($row['CODIGO'], 50, 'código');
            // Aproxima la comparación insensible a mayúsculas y acentos de MySQL.
            $key = mb_strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $code));
            if (isset($seen[$key])) {
                throw new RuntimeException("Código duplicado al normalizar: $code");
            }
            $seen[$key] = true;
            $missingName = trim((string) $row['DESCRIPCION']) === '';
            $name = self::text($missingName ? $code : $row['DESCRIPCION'], 150, "nombre de $code");
            $price = self::money($row['PVENTA'], $code);
            $cost = self::money($row['PCOSTO'], $code);
            if (! isset($departments[$row['DEPT']])) {
                throw new RuntimeException("Departamento inexistente para $code");
            }
            if (($row['PROVID'] ?? 0) != 0 || trim($row['COMPONENTES'] ?? '') !== '' || trim($row['IMPUESTOS'] ?? '') !== '') {
                throw new RuntimeException("$code tiene proveedor, componentes o impuestos que necesitan una regla adicional.");
            }
            $latest = $last[$code] ?? null;
            // DINVENTARIO=-1 no se interpreta como una existencia real.
            $stock = $row['DINVENTARIO'] >= 0 ? (float) $row['DINVENTARIO'] : ($latest ? self::after($latest) : null);
            if ($stock === null || ! is_finite($stock) || $stock < 0 || $stock != floor($stock) || $stock > 2147483647) {
                $stockErrors[] = $code;
                $stock = null;
            }
            $products[] = [
                'code' => $code, 'source_code' => $row['CODIGO'], 'name' => $name, 'name_from_code' => $missingName,
                'category' => $departments[$row['DEPT']], 'price' => $price, 'cost' => $cost,
                'active' => trim($row['TVENTA']) === 'U', 'sale_type' => trim($row['TVENTA']),
                'stock' => $stock === null ? null : (int) $stock, 'source_stock' => $row['DINVENTARIO'],
                'last' => $latest, 'wholesale_price' => $row['MAYOREO'], 'minimum_stock' => $row['DINVMINIMO'],
            ];
        }
        if (! $products || count($products) !== $manifest['counts']['PRODUCTOS'] || count($history) !== $manifest['counts']['HISTORIAL_INVENTARIO']) {
            throw new RuntimeException('La cantidad exportada no coincide con el manifiesto.');
        }

        return compact('manifest', 'cutoff', 'products', 'stockErrors', 'discontinuities');
    }

    private static function json(string $file): array
    {
        if (! is_file($file)) {
            throw new RuntimeException("Falta $file");
        }

        return json_decode(ltrim(file_get_contents($file), "\xEF\xBB\xBF"), true, 512, JSON_THROW_ON_ERROR);
    }

    private static function text(string $value, int $limit, string $field): string
    {
        $value = trim($value);
        if ($value === '' || ! mb_check_encoding($value, 'UTF-8') || str_contains($value, "\u{FFFD}")
            || preg_match('/[\x00-\x1F\x7F]/u', $value) || mb_strlen($value) > $limit) {
            throw new RuntimeException("Texto inválido o demasiado largo: $field");
        }

        return $value;
    }

    private static function money(mixed $value, string $code): string
    {
        if (! is_numeric($value) || ! is_finite((float) $value) || $value < 0 || $value > 99999999.99
            || abs((float) $value - round((float) $value, 2)) > 0.0001) {
            throw new RuntimeException("Importe no representable sin pérdida: $code");
        }

        return number_format((float) $value, 2, '.', '');
    }

    private static function after(array $row): ?float
    {
        return match (trim($row['TIPO'])) {
            'e', 'd' => (float) $row['HABIA'] + (float) $row['CANTIDAD'],
            's' => (float) $row['HABIA'] - (float) $row['CANTIDAD'],
            'a' => (float) $row['CANTIDAD'],
            default => null,
        };
    }

    /** Literales hexadecimales: independientes de NO_BACKSLASH_ESCAPES y de comillas. */
    public static function sql(string $value): string
    {
        return 'CONVERT(0x'.bin2hex($value).' USING utf8mb4) COLLATE utf8mb4_unicode_ci';
    }

    private static function begin(array $plan, string $stage, string $condition): string
    {
        $marker = self::sql('pdv:'.$plan['manifest']['source_sha256'].':'.$stage);

        return "-- Congelandia: importación única PDV. Ejecutar en una conexión, detenerse ante cualquier error.\n"
            ."-- Origen SHA-256: {$plan['manifest']['source_sha256']}\n"
            ."SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;\nSET @pdv_lock_name = CONCAT('pdv:', SHA2(DATABASE(), 256));\n"
            // GET_LOCK admite nombres de hasta 64 caracteres.
            ."SET @pdv_lock_name = LEFT(@pdv_lock_name, 64);\nSET @pdv_lock = GET_LOCK(@pdv_lock_name, 10);\n"
            ."START TRANSACTION;\nSET @pdv_user = (SELECT MIN(idUsuario) FROM usuario WHERE activo = 1 AND deleted_at IS NULL AND rol = 'Administrador');\n"
            ."SET @pdv_done = EXISTS(SELECT 1 FROM registro_acciones WHERE referencia = $marker);\n"
            ."SET @pdv_apply = (@pdv_lock = 1 AND @pdv_user IS NOT NULL AND NOT @pdv_done AND ($condition));\n";
    }

    private static function finish(array $plan, string $stage): string
    {
        $marker = self::sql('pdv:'.$plan['manifest']['source_sha256'].':'.$stage);

        return "INSERT INTO registro_acciones (idUsuario, usuarioNombre, accion, modulo, ruta, referencia, fecha)\n"
            ."SELECT idUsuario, nombre, 'Importación PDV $stage', 'Migracion', 'tools/migration/pdv-to-mysql.php', $marker, NOW() FROM usuario WHERE idUsuario = @pdv_user AND @pdv_apply = 1;\n"
            ."COMMIT;\nSELECT CASE WHEN @pdv_done THEN 'YA_IMPORTADO' WHEN @pdv_apply THEN 'IMPORTADO' ELSE 'BLOQUEADO: comprobar administrador, destino vacío y etapa anterior' END AS resultado;\n"
            ."DO RELEASE_LOCK(@pdv_lock_name);\n";
    }

    public static function catalogSql(array $plan): string
    {
        $sql = self::begin($plan, 'catalogo', '(SELECT COUNT(*) FROM producto) = 0');
        foreach (array_unique(array_column($plan['products'], 'category')) as $category) {
            $name = self::sql($category);
            $sql .= "INSERT INTO categoria (nombre) SELECT $name WHERE @pdv_apply = 1 AND NOT EXISTS (SELECT 1 FROM categoria WHERE nombre = $name AND deleted_at IS NULL);\n";
        }
        $cutoff = self::sql($plan['cutoff']);
        foreach ($plan['products'] as $p) {
            $code = self::sql($p['code']);
            $name = self::sql($p['name']);
            $category = self::sql($p['category']);
            $deleted = $p['active'] ? 'NULL' : $cutoff;
            $sql .= "INSERT INTO producto (codigo, nombre, idCategoria, deleted_at) SELECT $code, $name, (SELECT MIN(idCategoria) FROM categoria WHERE nombre = $category AND deleted_at IS NULL), $deleted WHERE @pdv_apply = 1;\n";
            $sql .= "INSERT INTO lista_precio (codigoProducto, precioVenta, fechaInicio) SELECT $code, {$p['price']}, $cutoff WHERE @pdv_apply = 1;\n";
        }

        return $sql.self::finish($plan, 'catalogo');
    }

    public static function stockSql(array $plan): string
    {
        if ($plan['stockErrors']) {
            throw new RuntimeException('Stock sin representación entera válida: '.implode(', ', $plan['stockErrors']));
        }
        $catalogMarker = self::sql('pdv:'.$plan['manifest']['source_sha256'].':catalogo');
        $count = count($plan['products']);
        $codes = implode(', ', array_map(fn ($p) => self::sql($p['code']), $plan['products']));
        $condition = "EXISTS(SELECT 1 FROM registro_acciones WHERE referencia = $catalogMarker)"
            ." AND (SELECT COUNT(*) FROM producto WHERE codigo IN ($codes)) = $count"
            ." AND NOT EXISTS(SELECT 1 FROM detalle_ingreso WHERE codigoProducto IN ($codes))"
            ." AND NOT EXISTS(SELECT 1 FROM detalle_salida WHERE codigoProducto IN ($codes))";
        $sql = "-- STOCK PROPUESTO: revisar stock_revision.csv antes de importar.\n"
            ."-- Los saldos con DINVENTARIO=-1 se infieren del último movimiento. No son un conteo físico.\n"
            .self::begin($plan, 'stock', $condition);
        $cutoff = self::sql($plan['cutoff']);
        $sql .= "INSERT INTO ingreso (fecha, idProveedor, idUsuario, totalCompra) SELECT $cutoff, NULL, @pdv_user, 0 WHERE @pdv_apply = 1;\nSET @pdv_ingreso = LAST_INSERT_ID();\n";
        foreach ($plan['products'] as $p) {
            $code = self::sql($p['code']);
            // Una sola apertura, sin simular compras ni facturas; cero conserva el costo.
            $sql .= "INSERT INTO detalle_ingreso (idIngreso, codigoProducto, cantidad, precioCompra, fechaVencimiento) SELECT @pdv_ingreso, $code, {$p['stock']}, {$p['cost']}, NULL WHERE @pdv_apply = 1;\n";
        }

        return $sql.self::finish($plan, 'stock');
    }

    public static function verificationSql(array $plan): string
    {
        $expected = [];
        foreach ($plan['products'] as $p) {
            $stock = $p['stock'] ?? 'NULL';
            $active = (int) $p['active'];
            $expected[] = 'SELECT '.self::sql($p['code'])." AS codigo, {$p['price']} AS precio, {$p['cost']} AS costo, $stock AS stock, $active AS activo";
        }

        return "-- Debe devolver cero diferencias después de 01 y 02, antes de operar.\nSET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;\n"
            ."SELECT e.*, p.nombre, p.deleted_at,\n"
            ."(SELECT lp.precioVenta FROM lista_precio lp WHERE lp.codigoProducto = e.codigo AND lp.deleted_at IS NULL AND lp.fechaInicio <= NOW() AND lp.fechaFin IS NULL ORDER BY lp.fechaInicio DESC, lp.idPrecio DESC LIMIT 1) AS precio_real,\n"
            ."(SELECT di.precioCompra FROM detalle_ingreso di JOIN ingreso i ON i.idIngreso = di.idIngreso WHERE di.codigoProducto = e.codigo AND di.deleted_at IS NULL AND i.deleted_at IS NULL ORDER BY i.fecha DESC, di.idDetalle DESC LIMIT 1) AS costo_real,\n"
            ."COALESCE((SELECT SUM(di.cantidad) FROM detalle_ingreso di JOIN ingreso i ON i.idIngreso = di.idIngreso WHERE di.codigoProducto = e.codigo AND di.deleted_at IS NULL AND i.deleted_at IS NULL),0) - COALESCE((SELECT SUM(ds.cantidad) FROM detalle_salida ds JOIN salida s ON s.idSalida = ds.idSalida WHERE ds.codigoProducto = e.codigo AND ds.deleted_at IS NULL AND s.deleted_at IS NULL),0) AS stock_real\n"
            .'FROM ('.implode("\nUNION ALL\n", $expected).") e LEFT JOIN producto p ON p.codigo = e.codigo\n"
            ."HAVING nombre IS NULL OR NOT (precio <=> precio_real) OR NOT (costo <=> costo_real) OR NOT (stock <=> stock_real) OR activo <> (deleted_at IS NULL);\n";
    }
}
