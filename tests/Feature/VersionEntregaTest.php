<?php

use App\Models\User;
use App\Models\Version;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->releaseFile = tempnam(sys_get_temp_dir(), 'congelandia-release-');
    config(['app.release_file' => $this->releaseFile]);
});

afterEach(function () {
    if (is_file($this->releaseFile)) {
        unlink($this->releaseFile);
    }
});

test('la entrega informa su version sin consultar la tabla versiones', function () {
    file_put_contents($this->releaseFile, json_encode([
        'numero' => '1.3.0',
        'comentarios' => 'Nuevos reportes',
        'fecha_creacion' => '2026-11-08T12:00:00Z',
        'commit' => str_repeat('a', 40),
    ]));

    // No hay migraciones: debe resolver la versión sin usar la base de datos.
    $version = Version::actual();
    expect($version->numero)->toBe('1.3.0')
        ->and($version->comentarios)->toBe('Nuevos reportes')
        ->and($version->exists)->toBeFalse();
});

test('sin archivo de entrega conserva la version activa de instalaciones anteriores', function () {
    unlink($this->releaseFile);
    Artisan::call('migrate:fresh');
    Version::create(['numero' => '1.0.0', 'activa' => true]);
    Version::create(['numero' => '2.0.0', 'activa' => false]);

    expect(Version::actual()->numero)->toBe('1.0.0');
});

test('los detalles y la pagina publica muestran la version del paquete en lugar de la base', function () {
    Artisan::call('migrate:fresh');
    Version::create(['numero' => '0.1.0', 'activa' => true]);
    file_put_contents($this->releaseFile, json_encode([
        'numero' => '1.3.0',
        'comentarios' => 'Nuevos reportes',
        'fecha_creacion' => '2026-11-08T12:00:00Z',
        'commit' => str_repeat('b', 40),
    ]));
    $user = User::create([
        'nombre' => 'Admin', 'email' => 'release@example.com', 'contrasena' => 'secret',
        'rol' => 'Administrador', 'activo' => true,
    ]);

    $this->get('/')->assertOk()->assertSee('Versión 1.3.0')->assertDontSee('Versión 0.1.0');
    $this->actingAs($user)->get('/version-info')->assertOk()
        ->assertSee('VERSION: 1.3.0')->assertSee('Nuevos reportes')->assertSee(str_repeat('b', 40));
});

test('rechaza metadatos dañados para no mostrar una version antigua como si fuera la actual', function () {
    file_put_contents($this->releaseFile, '{invalid');
    expect(fn () => Version::actual())->toThrow(JsonException::class);
});
