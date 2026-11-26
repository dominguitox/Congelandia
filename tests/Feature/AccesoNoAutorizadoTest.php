<?php

use App\Models\DetalleIngreso;
use App\Models\Ingreso;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->producto = Producto::create(['codigo' => 'ACC-1', 'nombre' => 'Helado protegido']);
    $this->cajero = User::create([
        'nombre' => 'Cajero sin permiso',
        'email' => 'cajero@acceso.test',
        'contrasena' => bcrypt('secreto'),
        'rol' => 'Cajero',
        'activo' => true,
    ]);
});

test('invitado es redirigido al login y no ejecuta la operacion protegida', function () {
    $ingreso = [
        'codigoProducto' => 'ACC-1',
        'cantidad' => 5,
        'precioCompra' => 1000,
        'fechaVencimiento' => now()->addMonth()->toDateString(),
        '_modal' => 'modalRegistrarIngreso',
    ];

    $this->get(route('dashboard.index'))->assertRedirect(route('login'));
    $this->post(route('inventario.ingresos.store'), $ingreso)->assertRedirect(route('login'));
    $this->postJson(route('inventario.ingresos.store'), $ingreso)->assertUnauthorized();

    expect(Ingreso::withTrashed()->count())->toBe(0)
        ->and(DetalleIngreso::withTrashed()->count())->toBe(0);
});

test('cajero recibe 404 en rutas administrativas y no ejecuta la operacion', function () {
    $this->actingAs($this->cajero);

    $this->get(route('usuarios.index'))->assertNotFound();
    $this->post(route('usuarios.store'), [
        'nombre' => 'Intruso',
        'email' => 'intruso@acceso.test',
        'contrasena' => 'secreto',
        'rol' => 'Administrador',
    ])->assertNotFound();

    $this->put(route('inventario.editarProducto', 'ACC-1'), [
        'nombre' => 'Nombre no permitido',
        'precioVenta' => 9999,
    ])->assertNotFound();

    $this->get(route('reportes.index'))->assertNotFound();

    expect(User::where('email', 'intruso@acceso.test')->exists())->toBeFalse()
        ->and($this->producto->fresh()->nombre)->toBe('Helado protegido');
});
