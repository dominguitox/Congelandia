<?php

use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Support\Facades\Artisan;

test('la portada destaca las dos mayores ofertas en el hero y mantiene productos debajo de categorias', function () {
    Artisan::call('migrate:fresh');

    $crearProducto = function (string $codigo, string $nombre, ?float $descuento = null, bool $vigente = true): void {
        Producto::create(['codigo' => $codigo, 'nombre' => $nombre]);

        if ($descuento === null) {
            return;
        }

        Promocion::create([
            'codigoProducto' => $codigo,
            'porcentajeDescuento' => $descuento,
            'fechaInicio' => $vigente ? now()->subDay() : now()->subDays(4),
            'fechaFin' => $vigente ? now()->addDay() : now()->subDay(),
            'activa' => true,
        ]);
    };

    $crearProducto('OFERTA-10', 'Oferta 10', 10);
    $crearProducto('OFERTA-35', 'Oferta 35', 35);
    $crearProducto('OFERTA-20', 'Oferta 20', 20);
    $crearProducto('SIN-OFERTA', 'Producto sin oferta');
    $crearProducto('VENCIDA-90', 'Oferta vencida', 90, false);

    $this->get(route('public.index'))
        ->assertOk()
        ->assertViewHas('ofertasHero', fn ($productos) => $productos->pluck('codigo')->all() === ['OFERTA-35', 'OFERTA-20'])
        ->assertViewHas('productos', fn ($productos) => $productos->count() === 5)
        ->assertSee('class="pub-hero__photo"', false)
        ->assertSee('class="pub-hero__float"', false)
        ->assertSeeInOrder(['Explora por categoría', 'Para llenar tu congelador', 'pub-products-grid'])
        ->assertSee('Oferta 35')
        ->assertSee('Oferta 20')
        ->assertSee('Oferta 10')
        ->assertSee('Producto sin oferta')
        ->assertDontSee('Tomates frescos')
        ->assertDontSee('Empanadas de queso');
});
