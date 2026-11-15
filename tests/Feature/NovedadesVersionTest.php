<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->releaseFile = tempnam(sys_get_temp_dir(), 'congelandia-novedades-');
    config(['app.release_file' => $this->releaseFile]);
    $this->usuario = User::create([
        'nombre' => 'Prueba novedades', 'email' => 'novedades@example.test',
        'contrasena' => 'secret', 'rol' => 'Administrador', 'activo' => true,
    ]);
});

afterEach(function () {
    if (is_file($this->releaseFile)) unlink($this->releaseFile);
});

function releaseNovedades(array $overrides = []): array
{
    return array_replace([
        'numero' => '1.7.0',
        'comentarios' => "# Congelandia 1.7.0\n\nCambios editoriales",
        'novedades' => [
            'cambios' => [
                ['tipo' => 'Nueva función', 'descripcion' => 'Puedes consultar el calendario de ofertas.'],
                ['tipo' => 'Mejora', 'descripcion' => 'Los productos próximos a vencer se distinguen mejor.'],
            ],
            'resumen_cliente' => 'Ahora encuentras las fechas de ofertas y vencimientos con más facilidad.',
        ],
        'fecha_creacion' => '2026-11-08T12:00:00Z',
        'commit' => str_repeat('f', 40),
    ], $overrides);
}

test('el layout autenticado comparte el aviso en varios módulos sin exponer el commit', function (string $url) {
    file_put_contents($this->releaseFile, json_encode(releaseNovedades(), JSON_THROW_ON_ERROR));

    $this->actingAs($this->usuario)->get($url)->assertOk()
        ->assertSee('data-version-notice', false)
        ->assertSee('alert-success m-3', false)
        ->assertSee('Congelandia se actualizó')
        ->assertSee('Novedades de Congelandia 1.7.0')
        ->assertSee('Cerrar novedades de la versión')
        ->assertSee('Puedes consultar el calendario de ofertas.')
        ->assertDontSee(str_repeat('f', 40));
})->with(['/inventario', '/clientes', '/dashboard']);

test('versiones antiguas con solo comentarios conservan detalles técnicos y no se presentan como novedades', function () {
    $hash = str_repeat('a', 40);
    file_put_contents($this->releaseFile, json_encode([
        'numero' => '1.6.2', 'comentarios' => "Cambios del código\n- Refactoriza el servicio\nCommit: {$hash}",
        'fecha_creacion' => '2026-11-01T12:00:00Z', 'commit' => $hash,
    ], JSON_THROW_ON_ERROR));

    $this->actingAs($this->usuario)->get('/inventario')->assertOk()
        ->assertDontSee('data-version-notice', false)
        ->assertDontSee('Novedades de Congelandia');
    $this->actingAs($this->usuario)->get('/version-info')->assertOk()
        ->assertSee('VERSION: 1.6.2')->assertSee($hash);
});

test('no renderiza el aviso cuando faltan notas o la versión no es válida', function (array $release) {
    file_put_contents($this->releaseFile, json_encode(releaseNovedades($release), JSON_THROW_ON_ERROR));

    $this->actingAs($this->usuario)->get('/inventario')->assertOk()
        ->assertDontSee('data-version-notice', false);
})->with([
    'sin novedades' => [['novedades' => null]],
    'sin cambios' => [['novedades' => ['cambios' => [], 'resumen_cliente' => 'Texto final.']]],
    'versión inválida' => [['numero' => 'versión-actual']],
    'fecha inválida' => [['fecha_creacion' => 'sin fecha']],
]);

test('las notas se imprimen como texto escapado y no como html editorial', function () {
    $release = releaseNovedades();
    $release['novedades']['cambios'][0]['descripcion'] = '<script>alert("x")</script>';
    file_put_contents($this->releaseFile, json_encode($release, JSON_THROW_ON_ERROR));

    $this->actingAs($this->usuario)->get('/inventario')->assertOk()
        ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert("x")</script>', false);
});
