<?php

namespace Congelandia\Migration;

use RuntimeException;

final class PdvHistory
{
    public static function load(string $directory, ?string $cutoff = null): array
    {
        $plan = PdvMigration::load($directory, $cutoff);
        if ($plan['stockErrors']) {
            throw new RuntimeException('Resolver primero los saldos no representables.');
        }
        $history = json_decode(ltrim(file_get_contents($directory.'/HISTORIAL_INVENTARIO.json'), "\xEF\xBB\xBF"), true, 512, JSON_THROW_ON_ERROR);
        usort($history, fn ($a, $b) => [$a['CUANDO_FUE'], $a['ID']] <=> [$b['CUANDO_FUE'], $b['ID']]);
        $products = array_column($plan['products'], null, 'code');
        $events = $pending = $calculated = $balances = $ids = [];
        foreach ($history as $row) {
            if (isset($ids[$row['ID']])) {
                throw new RuntimeException('ID original duplicado: '.$row['ID']);
            }
            $ids[$row['ID']] = true;
            $code = trim($row['CODIGO_PRODUCTO']);
            if (! isset($products[$code])) {
                $pending[] = $row + ['MOTIVO' => 'Producto ausente del catálogo; no se inventa ficha ni precio.'];

                continue;
            }
            $before = self::integer($row['HABIA']);
            $quantity = self::integer($row['CANTIDAD']);
            if ($quantity < 0 || ! in_array($row['TIPO'], ['e', 's', 'a', 'd'], true)) {
                throw new RuntimeException('Tipo o cantidad no compatible en movimiento '.$row['ID']);
            }
            $kind = array_key_exists($code, $balances) ? 'conciliacion' : 'saldo_inicial';
            $difference = $before - ($balances[$code] ?? 0);
            if ($difference !== 0) {
                $event = self::event($row, $products[$code], $difference, $kind);
                $events[] = $event;
                $calculated[] = $event;
            }
            $delta = match ($row['TIPO']) {
                'e', 'd' => $quantity,
                's' => -$quantity,
                'a' => $quantity - $before,
            };
            // Los ajustes sin cambio conservan un detalle de cantidad cero.
            $events[] = self::event($row, $products[$code], $delta, 'original');
            $balances[$code] = $before + $delta;
        }
        foreach ($products as $code => $product) {
            if (! array_key_exists($code, $balances) || $balances[$code] !== $product['stock']) {
                throw new RuntimeException("El historial no concilia con el saldo final de $code.");
            }
        }
        $plan['events'] = $events;
        $plan['pending'] = $pending;
        $plan['calculated'] = $calculated;
        $plan['original_count'] = count($history) - count($pending);

        return $plan;
    }

    private static function integer(mixed $value): int
    {
        if (! is_numeric($value) || ! is_finite((float) $value) || floor((float) $value) != $value || abs((float) $value) > 2147483647) {
            throw new RuntimeException('Cantidad histórica no representable sin pérdida.');
        }

        return (int) $value;
    }

    private static function event(array $row, array $product, int $delta, string $kind): array
    {
        $sale = $kind === 'original' && $row['TIPO'] === 's';
        $direction = $delta < 0 || $sale ? 'salida' : 'ingreso';
        $quantity = abs($delta);
        $unit = $direction === 'ingreso' ? $product['cost'] : ($sale ? $product['price'] : '0.00');
        // Los importes históricos son estimaciones con la ficha disponible.
        $valued = $sale || ($kind === 'original' && $row['TIPO'] === 'e');
        $amount = $valued ? bcmul((string) $quantity, $unit, 2) : '0.00';
        if (bccomp($amount, '9999999999.99', 2) > 0) {
            throw new RuntimeException('Importe fuera del rango del destino: '.$row['ID']);
        }

        return [
            'source_id' => (int) $row['ID'], 'source_type' => $row['TIPO'], 'source_user' => (int) $row['USUARIO_ID'],
            'date' => substr($row['CUANDO_FUE'], 0, 19), 'code' => $product['code'], 'before' => $row['HABIA'],
            'kind' => $kind, 'direction' => $direction, 'quantity' => $quantity, 'delta' => $delta,
            'unit' => $unit, 'amount' => $amount,
            'type' => $sale ? 'Salida histórica' : ($kind === 'conciliacion' ? 'Conciliación histórica' : 'Ajuste histórico'),
        ];
    }

    /** Batches pequeños, compatibles con MySQL 8 y sin desactivar claves foráneas. */
    private static function insert(string $table, array $columns, array $rows): string
    {
        $sql = '';
        foreach (array_chunk($rows, 250) as $chunk) {
            $selects = [];
            foreach ($chunk as $i => $row) {
                $values = [];
                foreach ($row as $j => $literal) {
                    $values[] = $literal.($i === 0 ? " AS c$j" : '');
                }
                $selects[] = 'SELECT '.implode(', ', $values);
            }
            $sql .= "INSERT INTO `$table` (".implode(', ', $columns).') SELECT * FROM ('.implode(' UNION ALL ', $selects).") AS datos WHERE @pdv_apply = 1;\n";
        }

        return $sql;
    }

    public static function sql(array $plan): string
    {
        $q = PdvMigration::sql(...);
        $catalogMarker = $q('pdv:'.$plan['manifest']['source_sha256'].':catalogo');
        $email = $q('historico@congelandia.invalid');
        $codes = implode(', ', array_map(fn ($p) => $q($p['code']), $plan['products']));
        $count = count($plan['products']);
        $condition = "EXISTS(SELECT 1 FROM registro_acciones WHERE referencia = $catalogMarker)"
            ." AND (SELECT COUNT(*) FROM producto WHERE codigo IN ($codes)) = $count"
            .' AND (SELECT COUNT(*) FROM ingreso) = 0 AND (SELECT COUNT(*) FROM salida) = 0'
            .' AND (SELECT COUNT(*) FROM detalle_ingreso) = 0 AND (SELECT COUNT(*) FROM detalle_salida) = 0'
            ." AND NOT EXISTS(SELECT 1 FROM usuario WHERE email = $email)";
        $sql = "-- HISTORIAL: precios y costos de referencia; revisar pendientes y ajustes calculados.\n"
            .PdvMigration::begin($plan, 'historial', $condition);
        $password = $q(password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT));
        $sql .= 'INSERT INTO usuario (nombre, email, contrasena, rol, activo) SELECT '.$q('Usuario histórico').", $email, $password, 'Cajero', 0 WHERE @pdv_apply = 1;\n"
            ."SET @pdv_user = (SELECT idUsuario FROM usuario WHERE email = $email);\n";
        foreach (['Salida histórica' => '@pdv_tipo_salida', 'Ajuste histórico' => '@pdv_tipo_ajuste', 'Conciliación histórica' => '@pdv_tipo_conciliacion'] as $name => $variable) {
            $name = $q($name);
            $sql .= "INSERT INTO tipo_salida (nombre) SELECT $name WHERE @pdv_apply = 1 AND NOT EXISTS(SELECT 1 FROM tipo_salida WHERE nombre = $name AND deleted_at IS NULL);\n"
                ."SET $variable = (SELECT MIN(idTipo) FROM tipo_salida WHERE nombre = $name AND deleted_at IS NULL);\n";
        }
        $incomes = $incomeDetails = $outgoings = $outgoingDetails = $audits = [];
        $incomeId = $outgoingId = 0;
        foreach ($plan['events'] as $event) {
            $date = $q($event['date']);
            $code = $q($event['code']);
            if ($event['direction'] === 'ingreso') {
                $id = ++$incomeId;
                $incomes[] = [$id, $date, 'NULL', '@pdv_user', $event['amount']];
                $incomeDetails[] = [$id, $code, $event['quantity'], $event['unit'], 'NULL'];
            } else {
                $id = ++$outgoingId;
                $type = match ($event['type']) {
                    'Salida histórica' => '@pdv_tipo_salida',
                    'Conciliación histórica' => '@pdv_tipo_conciliacion',
                    default => '@pdv_tipo_ajuste',
                };
                $outgoings[] = [$id, $date, $type, '@pdv_user', $event['amount']];
                $outgoingDetails[] = [$id, $code, $event['quantity'], $event['unit']];
            }
            $reference = json_encode(['origen' => $plan['manifest']['source_sha256'], 'id_original' => $event['source_id'], 'tipo' => $event['source_type'], 'usuario_original' => $event['source_user'], 'tabla' => $event['direction'], 'id_nuevo' => $id, 'clase' => $event['kind']], JSON_THROW_ON_ERROR);
            $action = match ($event['kind']) {
                'saldo_inicial' => 'Saldo inicial histórico calculado',
                'conciliacion' => 'Conciliación histórica calculada',
                default => 'Movimiento histórico',
            };
            $audits[] = ['@pdv_user', $q('Usuario histórico'), $q($action), $q('Historial'), $q('tools/migration/history-to-mysql.php'), $q($reference), $date];
        }
        $sql .= self::insert('ingreso', ['idIngreso', 'fecha', 'idProveedor', 'idUsuario', 'totalCompra'], $incomes)
            .self::insert('detalle_ingreso', ['idIngreso', 'codigoProducto', 'cantidad', 'precioCompra', 'fechaVencimiento'], $incomeDetails)
            .self::insert('salida', ['idSalida', 'fecha', 'idTipo', 'idUsuario', 'totalSalida'], $outgoings)
            .self::insert('detalle_salida', ['idSalida', 'codigoProducto', 'cantidad', 'precioCobrado'], $outgoingDetails)
            .self::insert('registro_acciones', ['idUsuario', 'usuarioNombre', 'accion', 'modulo', 'ruta', 'referencia', 'fecha'], $audits);

        return $sql.PdvMigration::finish($plan, 'historial');
    }

    public static function verificationSql(array $plan): string
    {
        $incomes = count(array_filter($plan['events'], fn ($e) => $e['direction'] === 'ingreso'));
        $outgoings = count($plan['events']) - $incomes;
        $checks = [
            ['ingresos', $incomes, '(SELECT COUNT(*) FROM ingreso)'],
            ['salidas', $outgoings, '(SELECT COUNT(*) FROM salida)'],
            ['detalles_ingreso', $incomes, '(SELECT COUNT(*) FROM detalle_ingreso)'],
            ['detalles_salida', $outgoings, '(SELECT COUNT(*) FROM detalle_salida)'],
            ['movimientos_originales', $plan['original_count'], "(SELECT COUNT(*) FROM registro_acciones WHERE accion = 'Movimiento histórico' AND ruta = 'tools/migration/history-to-mysql.php')"],
            ['usuario_historico_sin_acceso', 1, "(SELECT COUNT(*) FROM usuario WHERE email = 'historico@congelandia.invalid' AND activo = 0 AND deleted_at IS NULL)"],
        ];

        return "-- Debe devolver cero diferencias.\n".implode(' UNION ALL ', array_map(fn ($c) => "SELECT '".$c[0]."' AS dato, ".$c[1].' AS esperado, '.$c[2].' AS obtenido HAVING esperado <> obtenido', $checks)).";\n";
    }
}
