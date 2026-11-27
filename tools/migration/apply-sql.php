<?php

require __DIR__.'/PdvMigration.php';

use Congelandia\Migration\PdvMigration;

$options = getopt('', ['file:', 'accept-proposed-stock', 'accept-history-assumptions']);
$file = $options['file'] ?? '';
$name = basename($file);
$dsn = getenv('PDV_DSN');
if (! in_array($name, ['01_catalogo.sql', '02_stock_propuesto.sql', '02_historial.sql', '03_verificar.sql', '04_verificar_historial.sql'], true)
    || ! is_file($file) || ! $dsn || ! str_starts_with($dsn, 'mysql:') || ! str_contains($dsn, 'dbname=') || getenv('PDV_USER') === false) {
    fwrite(STDERR, "Uso: definir PDV_DSN (mysql:host=...;port=...;dbname=...;charset=utf8mb4), PDV_USER y PDV_PASSWORD; luego php tools/migration/apply-sql.php --file=output/pdv-migration/sql/01_catalogo.sql\n");
    exit(1);
}
if ($name === '02_historial.sql' && ! array_key_exists('accept-history-assumptions', $options)) {
    fwrite(STDERR, "Revisa movimientos pendientes, precios de referencia y ajustes calculados. Agrega --accept-history-assumptions para el ensayo.\n");
    exit(1);
}
if ($name === '02_stock_propuesto.sql' && ! array_key_exists('accept-proposed-stock', $options)) {
    fwrite(STDERR, "Revisa stock_revision.csv. Para aplicar el stock inferido, agrega --accept-proposed-stock. Editar el CSV no modifica el SQL.\n");
    exit(1);
}
try {
    // No lee .env ni reutiliza la conexión de la aplicación.
    $pdo = new PDO($dsn, getenv('PDV_USER'), getenv('PDV_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $result = PdvMigration::execute($pdo, file_get_contents($file));
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), "\n";
    if (in_array($name, ['03_verificar.sql', '04_verificar_historial.sql'], true)) {
        exit($result ? 2 : 0);
    }
    exit(str_starts_with($result[0]['resultado'] ?? 'BLOQUEADO', 'BLOQUEADO') ? 2 : 0);
} catch (Throwable $error) {
    $message = in_array($name, ['03_verificar.sql', '04_verificar_historial.sql'], true) ? 'Verificación fallida: ' : 'Importación revertida: ';
    fwrite(STDERR, $message.$error->getMessage()."\n");
    exit(1);
}
