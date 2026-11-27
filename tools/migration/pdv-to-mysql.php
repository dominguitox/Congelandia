<?php

require __DIR__.'/PdvMigration.php';

use Congelandia\Migration\PdvMigration;

$options = getopt('', ['input:', 'output:', 'cutoff::']);
if (! isset($options['input'], $options['output'])) {
    fwrite(STDERR, "Uso: php tools/migration/pdv-to-mysql.php --input=output/pdv-migration/origen --output=output/pdv-migration/sql [--cutoff=\"2026-11-26 17:35:08\"]\n");
    exit(1);
}
try {
    $plan = PdvMigration::load($options['input'], $options['cutoff'] ?? null);
    $out = $options['output'];
    if (! is_dir($out) && ! mkdir($out, 0700, true)) {
        throw new RuntimeException('No se pudo crear la salida.');
    }
    if (file_exists($out.'/01_catalogo.sql') || file_exists($out.'/02_stock_propuesto.sql')) {
        throw new RuntimeException('La salida ya contiene SQL. Usa otro directorio para conservarlo.');
    }
    file_put_contents($out.'/01_catalogo.sql', PdvMigration::catalogSql($plan));
    $csv = fopen($out.'/stock_revision.csv', 'wb');
    fputcsv($csv, ['codigo', 'nombre', 'activo', 'tipo_venta_origen', 'stock_en_producto', 'ultimo_movimiento', 'fecha', 'tipo', 'habia', 'cantidad', 'stock_propuesto', 'costo', 'precio', 'precio_mayorista_no_importado', 'stock_minimo_no_importado'], ',', '"', '');
    foreach ($plan['products'] as $p) {
        $last = $p['last'];
        fputcsv($csv, [$p['code'], $p['name'], (int) $p['active'], $p['sale_type'], $p['source_stock'], $last['ID'] ?? '', $last['CUANDO_FUE'] ?? '', $last['TIPO'] ?? '', $last['HABIA'] ?? '', $last['CANTIDAD'] ?? '', $p['stock'], $p['cost'], $p['price'], $p['wholesale_price'], $p['minimum_stock']], ',', '"', '');
    }
    fclose($csv);
    $summary = [
        'source_sha256' => $plan['manifest']['source_sha256'], 'cutoff' => $plan['cutoff'],
        'products' => count($plan['products']), 'active_products' => count(array_filter($plan['products'], fn ($p) => $p['active'])),
        'inactive_sale_types_to_review' => array_values(array_map(fn ($p) => ['code' => $p['code'], 'sale_type' => $p['sale_type']], array_filter($plan['products'], fn ($p) => ! $p['active']))),
        'missing_names_replaced_by_code' => array_values(array_column(array_filter($plan['products'], fn ($p) => $p['name_from_code']), 'code')),
        'proposed_total_stock' => array_sum(array_column($plan['products'], 'stock')),
        'stock_errors' => $plan['stockErrors'], 'historical_discontinuities' => $plan['discontinuities'],
        'source_counts' => $plan['manifest']['counts'],
        'not_imported' => ['Historial antiguo: se conserva como JSON, no se inventan ventas sin comprobantes ni importes.', 'PRODUCTOS_BASE: catálogo auxiliar, no productos del negocio.', 'FACTURACION_CLIENTES: identificadores RFC, no se convierten automáticamente a RUT.', 'Precios mayoristas, mínimos/máximos y medidas sin equivalencia: conservados en origen y CSV.', 'Claves del sistema anterior: no se importan.'],
        'stock_notice' => 'Stock propuesto, inferido cuando DINVENTARIO=-1. Revisar antes de aplicar 02; editar el CSV NO modifica el SQL.',
    ];
    file_put_contents($out.'/resumen.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
    if (! $plan['stockErrors']) {
        file_put_contents($out.'/02_stock_propuesto.sql', PdvMigration::stockSql($plan));
        file_put_contents($out.'/03_verificar.sql', PdvMigration::verificationSql($plan));
    }
    echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), "\nSalida: $out\n";
    exit($plan['stockErrors'] ? 2 : 0);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}
