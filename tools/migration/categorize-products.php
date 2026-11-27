<?php

use Congelandia\Migration\PdvMigration;
use Congelandia\Migration\ProductCategories;

require __DIR__.'/PdvMigration.php';
require __DIR__.'/ProductCategories.php';
$options = getopt('', ['input:', 'output:']);
if (! isset($options['input'], $options['output'])) {
    fwrite(STDERR, "Uso: php tools/migration/categorize-products.php --input=output/pdv-migration/origen --output=output/pdv-categories\n");
    exit(1);
}
try {
    $plan = ProductCategories::apply(PdvMigration::load($options['input']));
    $out = $options['output'];
    if (file_exists($out.'/05_categorias.sql')) {
        throw new RuntimeException('La salida ya existe. Usa otro directorio.');
    }
    if (! is_dir($out) && ! mkdir($out, 0700, true)) {
        throw new RuntimeException('No se pudo crear el directorio.');
    }
    file_put_contents($out.'/05_categorias.sql', ProductCategories::sql($plan));
    $csv = fopen($out.'/categorias_revision.csv', 'wb');
    fputcsv($csv, ['codigo', 'producto', 'categoria_original', 'categoria_propuesta', 'revisar', 'motivo'], ',', '"', '');
    foreach ($plan['products'] as $product) {
        fputcsv($csv, [$product['code'], $product['name'], $product['original_category'], $product['category'], (int) $product['category_review'], $product['category_reason']], ',', '"', '');
    }
    fclose($csv);
    $counts = array_count_values(array_column($plan['products'], 'category'));
    ksort($counts);
    echo json_encode(['categorias' => $counts, 'revisar' => array_values(array_map(fn ($p) => ['codigo' => $p['code'], 'nombre' => $p['name'], 'categoria' => $p['category']], array_filter($plan['products'], fn ($p) => $p['category_review'])))], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), "\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}
