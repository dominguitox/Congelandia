<?php

use App\Models\DetalleIngreso;
use App\Models\DetalleSalida;
use App\Models\ImagenProducto;
use App\Models\Ingreso;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Salida;
use App\Models\TipoSalida;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->user = User::create(['nombre' => 'Stock', 'email' => 'stock@test.cl', 'contrasena' => 'secret', 'rol' => 'Administrador', 'activo' => true]);
    $this->producto = Producto::create(['codigo' => 'STOCK-1', 'nombre' => 'Helado']);
    $this->proveedor = Proveedor::create(['nombre' => 'Proveedor stock']);
    $this->tipo = TipoSalida::create(['nombre' => 'Venta']);
});

function ingresoStock($test, int $cantidad): DetalleIngreso
{
    $ingreso = Ingreso::create(['fecha' => now(), 'idProveedor' => $test->proveedor->idProveedor, 'idUsuario' => $test->user->idUsuario, 'totalCompra' => $cantidad * 100]);

    return DetalleIngreso::create(['idIngreso' => $ingreso->idIngreso, 'codigoProducto' => 'STOCK-1', 'cantidad' => $cantidad, 'precioCompra' => 100, 'fechaVencimiento' => now()->addMonth()->toDateString()]);
}

function salidaStock($test, int $cantidad): DetalleSalida
{
    $salida = Salida::create(['fecha' => now(), 'idTipo' => $test->tipo->idTipo, 'idUsuario' => $test->user->idUsuario, 'totalSalida' => $cantidad * 200]);

    return DetalleSalida::create(['idSalida' => $salida->idSalida, 'codigoProducto' => 'STOCK-1', 'cantidad' => $cantidad, 'precioCobrado' => 200]);
}

test('stock resta salidas e ignora padres y detalles eliminados', function () {
    ingresoStock($this, 20);
    ingresoStock($this, 5)->delete();
    ingresoStock($this, 8)->ingreso->delete();
    salidaStock($this, 3);
    salidaStock($this, 4)->delete();
    $detalle = salidaStock($this, 7);
    Salida::findOrFail($detalle->idSalida)->delete();
    expect((int) Producto::conDetalle()->firstOrFail()->stock)->toBe(17);
    $this->producto->delete();
    expect(Producto::conDetalle()->count())->toBe(0);
});

test('producto sin movimientos tiene stock cero y no inventa precio ni proveedor', function () {
    $producto = Producto::conDetalle()->firstOrFail();
    expect((int) $producto->stock)->toBe(0)->and($producto->precio)->toBeNull()
        ->and($producto->proveedor)->toBeNull()->and($producto->costo)->toBeNull();
});

test('costo y vencimiento corresponden al ultimo ingreso activo', function () {
    ingresoStock($this, 2)->update(['precioCompra' => 50]);
    $ultimo = ingresoStock($this, 3);
    $ultimo->update(['precioCompra' => 150, 'fechaVencimiento' => now()->addDays(5)->toDateString()]);
    ingresoStock($this, 1)->ingreso->delete();

    $producto = Producto::conDetalle()->firstOrFail();
    expect((float) $producto->costo)->toBe(150.0)
        ->and($producto->fechaVencimiento)->toBe(now()->addDays(5)->toDateString())
        ->and($producto->proveedor)->toBe('Proveedor stock');
});

test('precio actual descarta precios futuros finalizados y borrados y no duplica la consulta', function () {
    foreach ([[100, now()->subDays(4), null, null], [200, now()->subDays(3), null, null],
        [900, now()->addDay(), null, null], [800, now()->subDays(2), now()->subDay(), null],
        [700, now()->subDay(), null, now()]] as [$precio, $inicio, $fin, $borrado]) {
        DB::table('lista_precio')->insert(['codigoProducto' => 'STOCK-1', 'precioVenta' => $precio, 'fechaInicio' => $inicio, 'fechaFin' => $fin, 'deleted_at' => $borrado]);
    }
    $producto = Producto::conDetalle()->firstOrFail();
    expect((float) $producto->precio)->toBe(200.0)->and($producto->precioVenta)->toBe(200.0);
});

test('la relacion inversa de imagen usa codigo de producto', function () {
    $imagen = ImagenProducto::create(['codigoProducto' => 'STOCK-1', 'rutaImagen' => 'imagen.jpg', 'alt' => 'Helado', 'descripcion' => 'Prueba']);
    expect($imagen->producto->codigo)->toBe('STOCK-1');
});

test('merma descuenta stock y rechaza exceso sin crear movimientos', function () {
    ingresoStock($this, 10);
    $this->actingAs($this->user)->post(route('inventario.salidas.store'), ['codigoProducto' => 'STOCK-1', 'motivo' => 'Merma por Daño', 'cantidad' => 3, '_modal' => 'modalRegistrarSalida'])->assertRedirect();
    expect((int) Producto::conDetalle()->firstOrFail()->stock)->toBe(7);
    $this->postJson(route('inventario.salidas.store'), ['codigoProducto' => 'STOCK-1', 'motivo' => 'Merma por Daño', 'cantidad' => 8, '_modal' => 'modalRegistrarSalida'])->assertUnprocessable();
    expect(Salida::count())->toBe(1);
});

test('inventario dashboard y POS usan los datos compartidos', function () {
    ingresoStock($this, 12);
    salidaStock($this, 2);
    $this->actingAs($this->user);
    foreach (['inventario.index', 'dashboard.index', 'pos.index'] as $route) {
        $this->get(route($route))->assertOk()->assertViewHas('productos', fn ($productos) => (int) $productos->first()->stock === 10);
    }
});

test('las pantallas y el cobro eligen el mismo precio vigente incluso con fechas iguales', function () {
    $this->freezeTime();
    ingresoStock($this, 12);
    salidaStock($this, 2);

    foreach ([[100, now()->subDays(4), null, null], [200, now()->subDays(3), null, null],
        [250, now()->subDays(3), null, null], [900, now()->addDay(), null, null],
        [800, now()->subDays(2), now()->subDay(), null], [700, now()->subDay(), null, now()]] as [$precio, $inicio, $fin, $borrado]) {
        DB::table('lista_precio')->insert(['codigoProducto' => 'STOCK-1', 'precioVenta' => $precio,
            'fechaInicio' => $inicio, 'fechaFin' => $fin, 'deleted_at' => $borrado]);
    }

    $this->actingAs($this->user);
    foreach (['inventario.index', 'dashboard.index', 'pos.index'] as $route) {
        $this->get(route($route))->assertOk()->assertViewHas('productos', function ($productos) {
            $producto = $productos->firstWhere('codigo', 'STOCK-1');

            return (int) $producto->stock === 10
                && (float) $producto->precio === 250.0
                && $producto->precioVenta === 250.0;
        });
    }

    $metodo = DB::table('metodo_pago')->insertGetId(['nombre' => 'Efectivo']);
    $this->postJson(route('venta.registrar'), [
        'idTipo' => $this->tipo->idTipo, 'metodoPago' => $metodo,
        'productos' => [['id' => 'STOCK-1', 'cantidad' => 1, 'precio' => 250]],
    ])->assertOk()->assertJsonPath('success', true);
    expect((float) DetalleSalida::latest('idDetalle')->firstOrFail()->precioCobrado)->toBe(250.0);
});

test('errores de creacion conservan datos y muestran el modal correcto', function () {
    $this->actingAs($this->user)->from(route('inventario.index'))->post(route('productos.crearProducto'), [
        'codigo' => 'NUEVO', 'nombre' => 'Nombre conservado', 'precioVenta' => -1, '_modal' => 'modalCrearProducto',
    ])->assertRedirect(route('inventario.index'))->assertSessionHasErrors(['precioVenta', 'idCategoria']);
    $this->get(route('inventario.index'))->assertOk()->assertSee('data-modal-error="modalCrearProducto"', false)->assertSee('Nombre conservado');
    expect(Producto::count())->toBe(1);
});

test('abastecimiento inicial incompleto se rechaza antes de crear producto', function () {
    $categoria = DB::table('categoria')->insertGetId(['nombre' => 'Helados']);
    $this->actingAs($this->user)->postJson(route('productos.crearProducto'), [
        'codigo' => 'NUEVO', 'nombre' => 'Prueba', 'precioVenta' => 500, 'idCategoria' => $categoria, 'stockInicial' => 3,
    ])->assertUnprocessable()->assertJsonValidationErrors(['idProveedor', 'precioCompra', 'fechaVencimiento']);
    expect(Producto::count())->toBe(1);
});

test('errores de edicion recuperan formulario y accion sin modificar el producto', function () {
    $this->actingAs($this->user)->from(route('inventario.index'))->put(route('inventario.editarProducto', 'STOCK-1'), [
        'codigo' => 'STOCK-1', 'nombre' => 'Nombre editado', 'precioVenta' => -1, '_modal' => 'modalEditarProducto',
    ])->assertRedirect(route('inventario.index'))->assertSessionHasErrors('precioVenta');

    $this->get(route('inventario.index'))->assertOk()
        ->assertSee('data-modal-error="modalEditarProducto"', false)
        ->assertSee('data-old-action="'.route('inventario.editarProducto', 'STOCK-1').'"', false)
        ->assertSee('Nombre editado');
    expect($this->producto->fresh()->nombre)->toBe('Helado');
});
