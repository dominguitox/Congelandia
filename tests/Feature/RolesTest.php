<?php

use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
});

function usuarioConRol(string $rol): User
{
    return User::create(['nombre' => 'Prueba permisos', 'email' => strtolower($rol).'@congelandia.test',
        'contrasena' => bcrypt('secreto'), 'rol' => $rol, 'activo' => true]);
}

test('cajero no alcanza rutas administrativas ni escribe datos', function (string $method, string $name, array $params) {
    $user = usuarioConRol('Cajero');
    $this->actingAs($user)->json($method, route($name, $params), [
        'nombre' => 'Intruso', 'email' => 'intruso@test.cl', 'contrasena' => 'secreto', 'rol' => 'Administrador',
    ])->assertNotFound();
    expect(User::count())->toBe(1);
})->with([
    ['GET', 'usuarios.index', []], ['POST', 'usuarios.store', []],
    ['GET', 'categorias.index', []], ['POST', 'categorias.store', []],
    ['PUT', 'categorias.update', [1]], ['DELETE', 'categorias.destroy', [1]],
    ['GET', 'proveedores.index', []], ['POST', 'proveedores.store', []],
    ['PUT', 'proveedores.update', [1]], ['DELETE', 'proveedores.destroy', [1]],
    ['PUT', 'inventario.editarProducto', ['PRUEBA']], ['POST', 'productos.crearProducto', []],
    ['POST', 'ofertas.store', []], ['PUT', 'ofertas.update', [1]],
    ['PATCH', 'ofertas.toggle', [1]], ['DELETE', 'ofertas.destroy', [1]],
    ['GET', 'reportes.index', []],
]);

test('administrador puede asignar cada rol canonico y autenticar el usuario creado', function (string $rol) {
    $admin = usuarioConRol('Administrador');
    $this->actingAs($admin)->post(route('usuarios.store'), [
        'nombre' => 'Nuevo usuario', 'email' => 'nuevo@test.cl', 'contrasena' => 'secreto', 'rol' => $rol,
    ])->assertRedirect(route('usuarios.index'));
    $nuevo = User::where('email', 'nuevo@test.cl')->firstOrFail();
    expect($nuevo->rol)->toBe($rol)->and(Hash::check('secreto', $nuevo->contrasena))->toBeTrue();
    auth()->logout();
    $this->post('/login', ['email' => 'nuevo@test.cl', 'password' => 'secreto'])->assertRedirect('/');
    $this->assertAuthenticatedAs($nuevo);
})->with(User::ROLES);

test('administrador rechaza roles no definidos', function (string $rol) {
    $this->actingAs(usuarioConRol('Administrador'))->postJson(route('usuarios.store'), [
        'nombre' => 'Nuevo', 'email' => 'nuevo@test.cl', 'contrasena' => 'secreto', 'rol' => $rol,
    ])->assertUnprocessable()->assertJsonValidationErrors('rol');
    expect(User::count())->toBe(1);
})->with(['Jefe', 'administrador', 'SuperAdmin']);

test('cajero conserva el flujo operativo y no ve enlaces administrativos', function () {
    $this->actingAs(usuarioConRol('Cajero'));
    foreach (['pos.index', 'inventario.index', 'clientes.index', 'historial.index'] as $route) {
        $this->get(route($route))->assertOk();
    }
    $this->get(route('pos.index'))->assertDontSee('href="'.route('usuarios.index').'"', false)
        ->assertDontSee('href="'.route('proveedores.index').'"', false);
    $this->postJson(route('inventario.ingresos.store'), [])->assertUnprocessable();
    $this->postJson(route('inventario.salidas.store'), [])->assertUnprocessable();
    $this->postJson(route('venta.registrar'), [])->assertUnprocessable();
});

test('invitado es redirigido al login antes de comprobar el rol', function () {
    $this->get(route('usuarios.index'))->assertRedirect(route('login'));
});

test('administrador conserva el CRUD de categorias y proveedores', function () {
    $this->actingAs(usuarioConRol('Administrador'));
    $this->get(route('categorias.index'))->assertOk();
    $this->post(route('categorias.store'), ['nombre' => 'Categoria permisos', 'descripcion' => 'Prueba'])->assertRedirect();
    $categoria = Categoria::firstOrFail();
    $this->put(route('categorias.update', $categoria->idCategoria), ['nombre' => 'Categoria editada'])->assertRedirect();
    expect($categoria->fresh()->nombre)->toBe('Categoria editada');
    $this->delete(route('categorias.destroy', $categoria->idCategoria))->assertRedirect();
    expect(Categoria::count())->toBe(0);

    $this->get(route('proveedores.index'))->assertOk();
    $this->post(route('proveedores.store'), ['nombre' => 'Proveedor permisos', 'rutProveedor' => '11111111-1', 'telefono' => '123456789', 'email' => 'proveedor@test.cl'])->assertRedirect();
    $proveedor = Proveedor::firstOrFail();
    $this->put(route('proveedores.update', $proveedor->idProveedor), ['nombre' => 'Proveedor editado', 'telefono' => '123456789', 'email' => 'proveedor@test.cl'])->assertRedirect();
    expect($proveedor->fresh()->nombre)->toBe('Proveedor editado');
    $this->delete(route('proveedores.destroy', $proveedor->idProveedor))->assertRedirect();
    expect(Proveedor::count())->toBe(0);
});
