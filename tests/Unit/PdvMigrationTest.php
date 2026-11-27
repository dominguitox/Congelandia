<?php

use Congelandia\Migration\PdvMigration;

require_once __DIR__.'/../../tools/migration/PdvMigration.php';

function pdvFixture(array $changes = []): string
{
    $dir = sys_get_temp_dir().'/congelandia-pdv-'.bin2hex(random_bytes(8));
    mkdir($dir);
    $data = [
        'PRODUCTOS' => [['CODIGO' => '00123', 'DESCRIPCION' => "Piñón d'oro", 'PVENTA' => 1500, 'PCOSTO' => 1000, 'DEPT' => 2, 'PROVID' => 0, 'COMPONENTES' => '', 'IMPUESTOS' => '', 'DINVENTARIO' => -1, 'TVENTA' => 'U', 'MAYOREO' => 1200, 'DINVMINIMO' => 3]],
        'DEPARTAMENTOS' => [['ID' => 2, 'NOMBRE' => 'Congelados']],
        'HISTORIAL_INVENTARIO' => [
            ['ID' => 4, 'CUANDO_FUE' => '2026-11-25 10:00:00', 'TIPO' => 'd', 'HABIA' => 5, 'CANTIDAD' => 1, 'CODIGO_PRODUCTO' => '00123'],
            ['ID' => 1, 'CUANDO_FUE' => '2026-11-24 10:00:00', 'TIPO' => 'e', 'HABIA' => 0, 'CANTIDAD' => 10, 'CODIGO_PRODUCTO' => '00123'],
            ['ID' => 2, 'CUANDO_FUE' => '2026-11-24 11:00:00', 'TIPO' => 's', 'HABIA' => 10, 'CANTIDAD' => 2, 'CODIGO_PRODUCTO' => '00123'],
            ['ID' => 3, 'CUANDO_FUE' => '2026-11-24 12:00:00', 'TIPO' => 'a', 'HABIA' => 8, 'CANTIDAD' => 5, 'CODIGO_PRODUCTO' => '00123'],
        ],
    ];
    $data = array_replace($data, $changes);
    $manifest = ['source_sha256' => hash('sha256', 'pdv-fixture'), 'source_modified_at' => '2026-11-26 17:35:08', 'counts' => array_fill_keys(['CLIENTES', 'ABONOS', 'PROV', 'VENTAS', 'VENTATICKETS', 'VENTATICKETS_ARTICULOS', 'FACTURAS', 'MOVIMIENTOS', 'OPERACIONES', 'PROMOCIONES_POR_CANTIDAD'], 0), 'files' => []];
    foreach ($data as $table => $rows) {
        file_put_contents($dir.'/'.$table.'.json', json_encode($rows, JSON_THROW_ON_ERROR));
        $manifest['files'][$table.'.json'] = hash_file('sha256', $dir.'/'.$table.'.json');
        $manifest['counts'][$table] = count($rows);
    }
    file_put_contents($dir.'/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));

    return $dir;
}

afterEach(function () {
    foreach ($this->pdvDirs ?? [] as $dir) {
        foreach (glob($dir.'/*.json') as $file) {
            unlink($file);
        }
        rmdir($dir);
    }
});

test('reconstruye entradas salidas ajustes absolutos y devoluciones sin sumar todo el historial', function () {
    $this->pdvDirs[] = $dir = pdvFixture();
    $plan = PdvMigration::load($dir);
    expect($plan['products'][0]['stock'])->toBe(6)
        ->and($plan['discontinuities'])->toBe(0)
        ->and($plan['products'][0]['code'])->toBe('00123');
    expect(PdvMigration::catalogSql($plan))->toContain(PdvMigration::sql("Piñón d'oro"))
        ->and(PdvMigration::stockSql($plan))->not->toContain('INSERT INTO salida');
});

test('rechaza un archivo alterado aunque siga siendo JSON valido', function () {
    $this->pdvDirs[] = $dir = pdvFixture();
    file_put_contents($dir.'/PRODUCTOS.json', '[]');
    expect(fn () => PdvMigration::load($dir))->toThrow(RuntimeException::class, 'alterada');
});

test('detiene la conversion si aparecen ventas o deudas fuera de su alcance', function () {
    $this->pdvDirs[] = $dir = pdvFixture(['CLIENTES' => [['ID' => 1]]]);
    expect(fn () => PdvMigration::load($dir))->toThrow(RuntimeException::class, 'CLIENTES no está vacía');
});

test('no trunca existencias fraccionarias ni convierte stocks desconocidos en cero', function (array $history) {
    $this->pdvDirs[] = $dir = pdvFixture(['HISTORIAL_INVENTARIO' => $history]);
    $plan = PdvMigration::load($dir);
    expect($plan['stockErrors'])->toBe(['00123']);
    expect(fn () => PdvMigration::stockSql($plan))->toThrow(RuntimeException::class, 'Stock sin representación');
})->with([
    'fraccionario' => [[['ID' => 1, 'CUANDO_FUE' => '2026-11-25 10:00:00', 'TIPO' => 'a', 'HABIA' => 0, 'CANTIDAD' => 0.5, 'CODIGO_PRODUCTO' => '00123']]],
    'sin historial' => [[]],
    'tipo desconocido' => [[['ID' => 1, 'CUANDO_FUE' => '2026-11-25 10:00:00', 'TIPO' => '?', 'HABIA' => 0, 'CANTIDAD' => 4, 'CODIGO_PRODUCTO' => '00123']]],
]);

test('rechaza un corte anterior al historial', function () {
    $this->pdvDirs[] = $dir = pdvFixture();
    expect(fn () => PdvMigration::load($dir, '2026-11-24 10:00:00'))->toThrow(RuntimeException::class, 'posteriores al corte');
});

test('conserva productos sin nombre y deja inactivos tipos de venta incompatibles', function () {
    $this->pdvDirs[] = $dir = pdvFixture();
    $row = PdvMigration::load($dir)['products'][0];
    $source = json_decode(file_get_contents($dir.'/PRODUCTOS.json'), true);
    $source[0]['DESCRIPCION'] = '';
    $source[0]['TVENTA'] = 'D';
    $this->pdvDirs[] = $dir2 = pdvFixture(['PRODUCTOS' => $source]);
    $product = PdvMigration::load($dir2)['products'][0];
    expect($product['name'])->toBe($row['code'])->and($product['name_from_code'])->toBeTrue()->and($product['active'])->toBeFalse();
});
