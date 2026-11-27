<?php

use Congelandia\Migration\PdvHistory;
use Congelandia\Migration\PdvMigration;

require __DIR__.'/PdvMigration.php';
require __DIR__.'/PdvHistory.php';
ini_set('memory_limit', '512M');
$options = getopt('', ['input:', 'output:', 'cutoff::']);
if (! isset($options['input'], $options['output'])) {
    fwrite(STDERR, "Uso: php tools/migration/history-to-mysql.php --input=output/pdv-migration/origen --output=output/pdv-history/sql\n");
    exit(1);
}
try {
    $plan = PdvHistory::load($options['input'], $options['cutoff'] ?? null);
    $out = $options['output'];
    if (is_dir($out) && glob($out.'/*.sql')) {
        throw new RuntimeException('La salida ya contiene SQL. Usa otro directorio.');
    }
    if (! is_dir($out) && ! mkdir($out, 0700, true)) {
        throw new RuntimeException('No se pudo crear la salida.');
    }
    file_put_contents($out.'/01_catalogo.sql', PdvMigration::catalogSql($plan));
    file_put_contents($out.'/02_historial.sql', PdvHistory::sql($plan));
    file_put_contents($out.'/03_verificar.sql', PdvMigration::verificationSql($plan));
    file_put_contents($out.'/04_verificar_historial.sql', PdvHistory::verificationSql($plan));
    foreach (['pending' => 'movimientos_pendientes.csv', 'calculated' => 'ajustes_calculados.csv'] as $key => $name) {
        $file = fopen($out.'/'.$name, 'wb');
        if ($plan[$key]) {
            fputcsv($file, array_keys($plan[$key][0]), ',', '"', '');
            foreach ($plan[$key] as $row) {
                fputcsv($file, array_values($row), ',', '"', '');
            }
        }
        fclose($file);
    }
    $summary = [
        'source_sha256' => $plan['manifest']['source_sha256'], 'products' => count($plan['products']),
        'original_movements_imported' => $plan['original_count'], 'pending_movements' => count($plan['pending']),
        'pending_product_codes' => count(array_unique(array_column($plan['pending'], 'CODIGO_PRODUCTO'))),
        'initial_balances' => count(array_filter($plan['calculated'], fn ($e) => $e['kind'] === 'saldo_inicial')),
        'calculated_reconciliations' => count(array_filter($plan['calculated'], fn ($e) => $e['kind'] === 'conciliacion')),
        'native_movements' => count($plan['events']), 'proposed_stock' => array_sum(array_column($plan['products'], 'stock')),
        'notice' => 'Se usa precio de venta y costo de la ficha actual, no importes históricos acreditados. Los saldos iniciales y conciliaciones son calculados; revisar el CSV. No hay compras documentadas, pagos ni ventas de productos ausentes. No aplicar la apertura de stock anterior.',
    ];
    file_put_contents($out.'/resumen.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
    echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), "\nSalida: $out\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}
