<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\RegistroAccion;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->admin = User::create(['nombre' => 'Admin acciones', 'email' => 'admin@acciones.test', 'contrasena' => Hash::make('secreto'), 'rol' => 'Administrador', 'activo' => true]);
    $this->cajero = User::create(['nombre' => 'Cajero acciones', 'email' => 'cajero@acciones.test', 'contrasena' => Hash::make('secreto'), 'rol' => 'Cajero', 'activo' => true]);
});

test('administrador desactiva y reactiva sin borrar al usuario y ambas acciones quedan registradas', function () {
    $this->actingAs($this->admin)->patch(route('usuarios.estado', $this->cajero->idUsuario), ['activo' => false])->assertRedirect(route('usuarios.index'));
    expect($this->cajero->fresh()->activo)->toBeFalse();
    $this->assertDatabaseHas('registro_acciones', ['idUsuario' => $this->admin->idUsuario, 'accion' => 'Usuario desactivado', 'referencia' => 'idUsuario: '.$this->cajero->idUsuario]);
    $this->patch(route('usuarios.estado', $this->cajero->idUsuario), ['activo' => true])->assertRedirect();
    expect($this->cajero->fresh()->activo)->toBeTrue();
    $this->assertDatabaseHas('registro_acciones', ['accion' => 'Usuario activado']);
    $this->assertDatabaseCount('usuario', 2);
    $this->assertDatabaseCount('registro_acciones', 2);
});

test('no permite desactivar la cuenta propia ni registra una accion fallida', function () {
    $this->actingAs($this->admin)->patchJson(route('usuarios.estado', $this->admin->idUsuario), ['activo' => false])->assertUnprocessable()->assertJsonValidationErrors('activo');
    expect($this->admin->fresh()->activo)->toBeTrue();
    $this->assertDatabaseCount('registro_acciones', 0);
});

test('repetir un estado no fabrica otra modificacion', function () {
    $this->actingAs($this->admin)->patch(route('usuarios.estado', $this->cajero->idUsuario), ['activo' => true])->assertRedirect();
    $this->assertDatabaseCount('registro_acciones', 0);
});

test('usuario desactivado no inicia sesion ni conserva una sesion anterior', function () {
    $this->actingAs($this->cajero);
    DB::table('usuario')->where('idUsuario', $this->cajero->idUsuario)->update(['activo' => false]);
    $this->postJson(route('venta.registrar'), [])->assertUnauthorized();
    $this->assertGuest();
    $this->post('/login', ['email' => $this->cajero->email, 'password' => 'secreto'])->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->assertDatabaseCount('registro_acciones', 0);
});

test('una sesion web desactivada vuelve al login', function () {
    $this->actingAs($this->cajero);
    DB::table('usuario')->where('idUsuario', $this->cajero->idUsuario)->update(['activo' => false]);
    $this->get(route('pos.index'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('cajero no administra usuarios ni consulta acciones', function () {
    $this->actingAs($this->cajero)->get(route('acciones.index'))->assertNotFound();
    $this->patchJson(route('usuarios.estado', $this->admin->idUsuario), ['activo' => false])->assertNotFound();
    $this->get(route('pos.index'))->assertDontSee('href="'.route('acciones.index').'"', false);
    $this->assertDatabaseCount('registro_acciones', 0);
});

test('los botones llevan a acciones reales y el historial se filtra por usuario modulo y fecha', function () {
    $this->actingAs($this->admin)->get(route('usuarios.index'))->assertOk()
        ->assertSee('action="'.route('usuarios.estado', $this->cajero->idUsuario).'"', false)
        ->assertSee('href="'.route('acciones.index', ['usuario' => $this->cajero->idUsuario]).'"', false);
    foreach ([[$this->admin, 'Acción del administrador', 'Usuarios', '2026-11-08 10:00:00'], [$this->cajero, 'Venta del cajero', 'Ventas', '2026-11-08 11:00:00'], [$this->cajero, 'Acción antigua', 'Ventas', '2026-10-01 10:00:00']] as [$usuario, $accion, $modulo, $fecha]) {
        RegistroAccion::create(['idUsuario' => $usuario->idUsuario, 'usuarioNombre' => $usuario->nombre, 'accion' => $accion, 'modulo' => $modulo, 'ruta' => 'fixture', 'fecha' => $fecha]);
    }
    $this->get(route('acciones.index', ['usuario' => $this->cajero->idUsuario, 'modulo' => 'Ventas', 'desde' => '2026-11-08', 'hasta' => '2026-11-08']))
        ->assertOk()->assertSee('Venta del cajero')->assertDontSee('Acción del administrador')->assertDontSee('Acción antigua');
});

test('el registro conserva el nombre historico aunque el usuario se elimine', function () {
    RegistroAccion::create(['idUsuario' => $this->cajero->idUsuario, 'usuarioNombre' => $this->cajero->nombre, 'accion' => 'Venta histórica', 'modulo' => 'Ventas', 'ruta' => 'venta.registrar', 'fecha' => now()]);
    $this->cajero->delete();
    $this->actingAs($this->admin)->get(route('acciones.index', ['usuario' => $this->cajero->idUsuario]))->assertOk()->assertSee('Venta histórica')->assertSee('Cajero acciones');
});

test('la paginacion conserva filtros y muestra solo las acciones correspondientes', function () {
    for ($i = 0; $i < 26; $i++) {
        RegistroAccion::create(['idUsuario' => $this->cajero->idUsuario, 'usuarioNombre' => $this->cajero->nombre, 'accion' => 'Venta '.$i, 'modulo' => 'Ventas', 'ruta' => 'venta.registrar', 'fecha' => '2026-11-08 10:00:00']);
    }
    $filtros = ['usuario' => $this->cajero->idUsuario, 'modulo' => 'Ventas', 'desde' => '2026-11-08'];
    $this->actingAs($this->admin)->get(route('acciones.index', $filtros))->assertOk()
        ->assertSee('Siguiente')->assertDontSee('pagination.next')
        ->assertSee('usuario='.$this->cajero->idUsuario, false)->assertSee('page=2', false)
        ->assertViewHas('acciones', fn ($acciones) => $acciones->count() === 25 && $acciones->total() === 26);
    $this->get(route('acciones.index', [...$filtros, 'page' => 2]))->assertOk()
        ->assertViewHas('acciones', fn ($acciones) => $acciones->count() === 1 && $acciones->total() === 26);
});

test('accesos y cambios quedan registrados sin secretos ni contenido completo del formulario', function () {
    $this->post('/login', ['email' => $this->admin->email, 'password' => 'secreto'])->assertRedirect();
    $this->post(route('usuarios.store'), ['nombre' => 'Nueva persona', 'email' => 'nueva@acciones.test', 'contrasena' => 'CLAVE-NO-REGISTRAR', 'rol' => 'Cajero', 'token' => 'TOKEN-NO-REGISTRAR'])->assertRedirect();
    $this->post(route('logout'))->assertRedirect();
    expect(RegistroAccion::pluck('accion')->all())->toBe(['Inicio de sesión', 'Usuario creado', 'Cierre de sesión']);
    $contenido = RegistroAccion::all()->toJson();
    expect($contenido)->not->toContain('CLAVE-NO-REGISTRAR')->not->toContain('TOKEN-NO-REGISTRAR')->not->toContain('contrasena')->not->toContain('password');
});

test('el log omite validaciones y rechazos de negocio aunque el controlador redirija', function () {
    $categoria = Categoria::create(['nombre' => 'Categoría ocupada']);
    Producto::create(['codigo' => 'AUD-1', 'nombre' => 'Producto', 'idCategoria' => $categoria->idCategoria]);
    $this->actingAs($this->admin)->delete(route('categorias.destroy', $categoria->idCategoria))->assertSessionHas('error');
    $this->postJson(route('usuarios.store'), ['rol' => 'No existe'])->assertUnprocessable();
    $this->assertDatabaseCount('registro_acciones', 0);
});

test('un fallo al guardar auditoria revierte tambien el cambio de negocio', function () {
    $this->withoutExceptionHandling();
    RegistroAccion::creating(fn () => throw new RuntimeException('Fallo simulado de auditoría'));
    try {
        expect(fn () => $this->actingAs($this->admin)->post(route('categorias.store'), ['nombre' => 'No debe persistir']))->toThrow(RuntimeException::class);
        $this->assertDatabaseCount('categoria', 0);
        $this->assertDatabaseCount('registro_acciones', 0);
    } finally {
        RegistroAccion::flushEventListeners();
    }
});
