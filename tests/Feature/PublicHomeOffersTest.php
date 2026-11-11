<?php

use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Support\Facades\Artisan;

test('la portada muestra solo los dos productos con mayores ofertas vigentes', function () {
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
        ->assertViewHas('productos', fn ($productos) => $productos->pluck('codigo')->all() === ['OFERTA-35', 'OFERTA-20'])
        ->assertSee('Oferta 35')
        ->assertSee('Oferta 20')
        ->assertDontSee('Oferta 10')
        ->assertDontSee('Producto sin oferta')
        ->assertDontSee('Oferta vencida')
        ->assertDontSee('Tomates frescos')
        ->assertDontSee('Empanadas de queso');
});
