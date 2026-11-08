<?php

use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('migraciones y modelos usan nombres exactos en minusculas', function () {
    Artisan::call('migrate:fresh');
    if (DB::getDriverName() === 'mysql' && PHP_OS_FAMILY === 'Linux') {
        expect((int) DB::selectOne('SELECT @@lower_case_table_names AS value')->value)->toBe(0);
    }
    $names = Schema::getTableListing(schemaQualified: false);
    foreach (['usuario', 'cliente', 'categoria', 'producto', 'lista_precio', 'promocion', 'proveedor',
        'ingreso', 'detalle_ingreso', 'tipo_salida', 'salida', 'detalle_salida', 'metodo_pago',
        'pago_salida', 'tipo_devolucion', 'devolucion', 'imagen_producto', 'versiones'] as $name) {
        expect(in_array($name, $names, true))->toBeTrue($name);
    }
    foreach (['User', 'Cliente', 'Categoria', 'Producto', 'ListaPrecio', 'Promocion', 'Proveedor',
        'Ingreso', 'DetalleIngreso', 'TipoSalida', 'Salida', 'DetalleSalida', 'PagoSalida', 'ImagenProducto', 'Version'] as $name) {
        $class = 'App\\Models\\'.$name;
        $table = (new $class)->getTable();
        expect($table)->toBe(strtolower($table));
        expect(in_array($table, $names, true))->toBeTrue($table);
    }
});

test('normalizacion conserva filas y referencias de un esquema anterior', function () {
    Artisan::call('migrate:fresh');
    $user = User::create(['nombre' => 'Conservar', 'email' => 'conservar@test.cl', 'contrasena' => 'hash', 'rol' => 'Administrador', 'activo' => true]);
    $category = DB::table('categoria')->insertGetId(['nombre' => 'Congelados']);
    Producto::create(['codigo' => 'EXISTENTE', 'nombre' => 'Conservar', 'idCategoria' => $category]);
    foreach (['usuario' => 'Usuario', 'categoria' => 'Categoria', 'producto' => 'Producto'] as $lower => $legacy) {
        Schema::rename($lower, '_prueba_'.$lower);
        Schema::rename('_prueba_'.$lower, $legacy);
    }
    $migration = require database_path('migrations/2026_11_06_000000_normalize_business_table_names.php');
    $migration->up();
    $migration->up(); // Ejecutable de nuevo sin tocar los nombres ya normalizados.
    expect(User::findOrFail($user->idUsuario)->email)->toBe('conservar@test.cl')
        ->and(Producto::findOrFail('EXISTENTE')->categoria->nombre)->toBe('Congelados')
        ->and(in_array('producto', Schema::getTableListing(schemaQualified: false), true))->toBeTrue();
});

test('normalizacion detecta nombres intermedios antes de modificar tablas', function () {
    Artisan::call('migrate:fresh');
    Schema::rename('producto', '_prueba_producto');
    Schema::rename('_prueba_producto', 'Producto');
    Schema::create('_normalizando_producto', fn ($table) => $table->id());
    $migration = require database_path('migrations/2026_11_06_000000_normalize_business_table_names.php');
    expect(fn () => $migration->up())->toThrow(RuntimeException::class);
    expect(in_array('Producto', Schema::getTableListing(schemaQualified: false), true))->toBeTrue();
});
