<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

test('historial pagina y filtra en toda la base incluyendo codigos y productos', function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $user = User::create(['nombre' => 'Administrador', 'email' => 'historial@example.test', 'contrasena' => 'secret', 'rol' => 'Administrador', 'activo' => true]);
    DB::table('producto')->insert(['codigo' => 'BUSCAR-001', 'nombre' => 'Producto antiguo']);
    $type = DB::table('tipo_salida')->insertGetId(['nombre' => 'Salida histórica'], 'idTipo');
    for ($i = 0; $i < 65; $i++) {
        $id = DB::table('salida')->insertGetId(['fecha' => '2025-06-01 10:00:00', 'idTipo' => $type, 'idUsuario' => $user->idUsuario, 'totalSalida' => 500], 'idSalida');
        DB::table('detalle_salida')->insert(['idSalida' => $id, 'codigoProducto' => 'BUSCAR-001', 'cantidad' => 1, 'precioCobrado' => 500]);
    }
    $this->actingAs($user)->get('/historial')->assertOk()
        ->assertViewHas('movimientos', fn ($rows) => $rows->total() === 65 && $rows->count() === 50)
        ->assertSee('Siguiente');
    $this->get('/historial?buscar=BUSCAR-001&tipo=salida:salida%20hist%C3%B3rica&page=2')->assertOk()
        ->assertViewHas('movimientos', fn ($rows) => $rows->total() === 65 && $rows->count() === 15);
    $this->get('/historial?buscar=inexistente')->assertOk()->assertViewHas('movimientos', fn ($rows) => $rows->total() === 0);
});
