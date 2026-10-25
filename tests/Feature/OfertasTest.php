<?php

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

function prepararGestorOfertas(): User
{
    Artisan::call('migrate:fresh');

    $user = User::create([
        'nombre' => 'Admin ofertas',
        'email' => 'ofertas@congelandia.test',
        'contrasena' => 'secret',
        'rol' => 'Administrador',
        'activo' => true,
    ]);

    Producto::create([
        'codigo' => 'OFERTA-001',
        'nombre' => 'Producto oferta',
    ]);

    return $user;
}

test('administrador puede crear editar pausar y reactivar una oferta', function () {
    $user = prepararGestorOfertas();
    $inicio = now()->addDay()->startOfMinute();
    $fin = $inicio->copy()->addDays(3);

    $this->actingAs($user)->post(route('ofertas.store'), [
        'codigoProducto' => 'OFERTA-001',
        'porcentajeDescuento' => 15,
        'fechaInicio' => $inicio->format('Y-m-d\TH:i'),
        'fechaFin' => $fin->format('Y-m-d\TH:i'),
    ])->assertRedirect(route('inventario.index') . '#gestionar-ofertas');

    $oferta = Promocion::firstOrFail();
    expect($oferta->activa)->toBeTruthy();

    $this->actingAs($user)->put(route('ofertas.update', $oferta->idPromocion), [
        'codigoProducto' => 'OFERTA-001',
        'porcentajeDescuento' => 20,
        'fechaInicio' => $inicio->format('Y-m-d\TH:i'),
        'fechaFin' => $fin->format('Y-m-d\TH:i'),
    ])->assertSessionHasNoErrors();
    expect((float) $oferta->fresh()->porcentajeDescuento)->toBe(20.0);

    $this->actingAs($user)->patch(route('ofertas.toggle', $oferta->idPromocion));
    expect($oferta->fresh()->activa)->toBeFalsy();
    $this->actingAs($user)->patch(route('ofertas.toggle', $oferta->idPromocion));
    expect($oferta->fresh()->activa)->toBeTruthy();
});

test('rechaza ofertas con fechas invalidas o cruces activos del mismo producto', function () {
    $user = prepararGestorOfertas();
    $inicio = now()->addDay()->startOfMinute();
    $fin = $inicio->copy()->addDays(3);
    $datos = [
        'codigoProducto' => 'OFERTA-001',
        'porcentajeDescuento' => 15,
        'fechaInicio' => $inicio->format('Y-m-d\TH:i'),
        'fechaFin' => $fin->format('Y-m-d\TH:i'),
    ];

    $this->actingAs($user)->post(route('ofertas.store'), $datos)->assertSessionHasNoErrors();
    $this->actingAs($user)->post(route('ofertas.store'), $datos)->assertSessionHasErrors('codigoProducto');

    $datos['porcentajeDescuento'] = 101;
    $datos['fechaInicio'] = $fin->format('Y-m-d\TH:i');
    $datos['fechaFin'] = $inicio->format('Y-m-d\TH:i');
    $this->actingAs($user)->post(route('ofertas.store'), $datos)
        ->assertSessionHasErrors(['porcentajeDescuento', 'fechaFin']);
});

test('no permite reactivar una oferta pausada si otra ocupa el mismo período', function () {
    $user = prepararGestorOfertas();
    $inicio = now()->addDay()->startOfMinute();
    $fin = $inicio->copy()->addDays(2);
    $ofertaPausada = Promocion::create([
        'codigoProducto' => 'OFERTA-001',
        'porcentajeDescuento' => 10,
        'fechaInicio' => $inicio,
        'fechaFin' => $fin,
        'activa' => false,
    ]);
    Promocion::create([
        'codigoProducto' => 'OFERTA-001',
        'porcentajeDescuento' => 20,
        'fechaInicio' => $inicio,
        'fechaFin' => $fin,
        'activa' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('ofertas.toggle', $ofertaPausada->idPromocion))
        ->assertRedirect(route('inventario.index') . '#gestionar-ofertas')
        ->assertSessionHas('error');

    expect($ofertaPausada->fresh()->activa)->toBeFalse();
});

test('usuarios que no son administradores no pueden gestionar ofertas', function () {
    $user = prepararGestorOfertas();
    $user->update(['rol' => 'Cajero']);

    $this->actingAs($user)->post(route('ofertas.store'), [
        'codigoProducto' => 'OFERTA-001',
        'porcentajeDescuento' => 10,
        'fechaInicio' => now()->addDay()->format('Y-m-d\TH:i'),
        'fechaFin' => now()->addDays(2)->format('Y-m-d\TH:i'),
    ])->assertNotFound();
});

test('el gestor se presenta en Inventario y el enlace de ofertas del dashboard sigue intacto', function () {
    $user = prepararGestorOfertas();

    $this->actingAs($user)
        ->get(route('inventario.index'))
        ->assertOk()
        ->assertSee('Gestionar ofertas')
        ->assertSee('Crear oferta')
        ->assertSee('modalOfertas');
});
