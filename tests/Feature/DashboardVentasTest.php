<?php

use App\Models\Salida;
use App\Models\TipoSalida;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->usuario = User::create([
        'nombre' => 'Cajero de prueba',
        'email' => 'dashboard@test.cl',
        'contrasena' => bcrypt('secreto'),
        'rol' => 'Cajero',
        'activo' => true,
    ]);
    $this->actingAs($this->usuario);
});

test('las ventas recientes se consultan en páginas de diez y mantienen el orden', function () {
    $tipo = TipoSalida::create(['nombre' => 'Venta']);
    $ventas = collect(range(1, 23))->map(fn ($numero) => Salida::create([
        'fecha' => now()->startOfDay(),
        'idTipo' => $tipo->idTipo,
        'idUsuario' => $this->usuario->idUsuario,
        'totalSalida' => $numero * 100,
    ]));
    $ids = $ventas->pluck('idSalida')->reverse()->values()->all();

    $this->get(route('dashboard.index'))->assertOk()
        ->assertViewHas('salidasRecientes', fn ($pagina) => $pagina->pluck('idSalida')->all() === array_slice($ids, 0, 10))
        ->assertSee('Mostrando 1–10 de 23 ventas')
        ->assertSee('Ventas anteriores')
        ->assertDontSee('Ventas más recientes');

    $this->get(route('dashboard.index', ['pagina_ventas' => 2]))->assertOk()
        ->assertViewHas('salidasRecientes', fn ($pagina) => $pagina->pluck('idSalida')->all() === array_slice($ids, 10, 10))
        ->assertSee('Mostrando 11–20 de 23 ventas')
        ->assertSee('Ventas más recientes')
        ->assertSee('Ventas anteriores');

    $this->get(route('dashboard.index', ['pagina_ventas' => 3]))->assertOk()
        ->assertViewHas('salidasRecientes', fn ($pagina) => $pagina->pluck('idSalida')->all() === array_slice($ids, 20))
        ->assertSee('Mostrando 21–23 de 23 ventas')
        ->assertDontSee('Ventas anteriores');
});

test('una lista vacía o con diez ventas no muestra controles de paginación', function () {
    $this->get(route('dashboard.index'))->assertOk()
        ->assertSee('No existen ventas registradas.')
        ->assertDontSee('Ventas anteriores');

    $tipo = TipoSalida::create(['nombre' => 'Venta']);
    foreach (range(1, 10) as $numero) {
        Salida::create([
            'fecha' => now(),
            'idTipo' => $tipo->idTipo,
            'idUsuario' => $this->usuario->idUsuario,
            'totalSalida' => $numero * 100,
        ]);
    }

    $this->get(route('dashboard.index'))->assertOk()
        ->assertViewHas('salidasRecientes', fn ($pagina) => $pagina->count() === 10)
        ->assertDontSee('Ventas anteriores');
});
