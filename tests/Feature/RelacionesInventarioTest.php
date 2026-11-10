<?php

use App\Models\Categoria;
use App\Models\DetalleIngreso;
use App\Models\Ingreso;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->admin = User::create(['nombre' => 'Admin', 'email' => 'relaciones@test.cl', 'contrasena' => 'secret', 'rol' => 'Administrador', 'activo' => true]);
    $this->actingAs($this->admin);
    $this->producto = Producto::create(['codigo' => 'REL-1', 'nombre' => 'Helado']);
    $this->nuevoProducto = ['codigo' => 'REL-2', 'nombre' => 'Paleta', 'precioVenta' => 1000, '_modal' => 'modalCrearProducto'];
    $this->ingreso = ['codigoProducto' => 'REL-1', 'cantidad' => 3, 'precioCompra' => 400, 'fechaVencimiento' => now()->addMonth()->toDateString(), '_modal' => 'modalRegistrarIngreso'];
});

test('permite crear productos sin categoria y abastecerlos sin proveedor', function (int $stock) {
    $this->post(route('productos.crearProducto'), $this->nuevoProducto + [
        'idCategoria' => '', 'idProveedor' => '', 'stockInicial' => $stock,
        'precioCompra' => 400, 'fechaVencimiento' => now()->addMonth()->toDateString(),
    ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');

    $producto = Producto::conDetalle()->where('codigo', 'REL-2')->firstOrFail();
    expect($producto->idCategoria)->toBeNull()->and((int) $producto->stock)->toBe($stock)
        ->and(Categoria::count())->toBe(0)->and(Proveedor::count())->toBe(0)->and(Ingreso::count())->toBe($stock ? 1 : 0);
    if ($stock) {
        expect(Ingreso::firstOrFail()->idProveedor)->toBeNull();
    }
})->with([0, 3]);

test('crea categoria y proveedor junto con el producto y su stock inicial', function () {
    $this->post(route('productos.crearProducto'), $this->nuevoProducto + [
        'idCategoria' => 'nuevo', 'nuevaCategoria' => '  Paletas  ',
        'idProveedor' => 'nuevo', 'nuevoProveedor' => '  Distribuidora  ',
        'stockInicial' => 3, 'precioCompra' => 400, 'fechaVencimiento' => now()->addMonth()->toDateString(),
    ])->assertSessionHasNoErrors()->assertSessionHas('success');

    $producto = Producto::findOrFail('REL-2');
    $ingreso = Ingreso::firstOrFail();
    expect($producto->categoria->nombre)->toBe('Paletas')->and($ingreso->proveedor->nombre)->toBe('Distribuidora')
        ->and((float) $ingreso->totalCompra)->toBe(1200.0)->and(DetalleIngreso::firstOrFail()->cantidad)->toBe(3);
    $this->assertDatabaseHas('proveedor', ['nombre' => 'Distribuidora', 'telefono' => null, 'correo' => null]);
});

test('no crea un proveedor si no se registra abastecimiento inicial', function () {
    $this->post(route('productos.crearProducto'), $this->nuevoProducto + [
        'stockInicial' => 0, 'idProveedor' => 'nuevo', 'nuevoProveedor' => 'Sin compra todavía',
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    expect(Proveedor::count())->toBe(0)->and(Ingreso::count())->toBe(0)->and(Producto::count())->toBe(2);
});

test('editar permite crear una categoria y luego dejar el producto sin categoria', function () {
    $this->put(route('inventario.editarProducto', 'REL-1'), [
        'nombre' => 'Helado', 'precioVenta' => 1000, 'idCategoria' => 'nuevo', 'nuevaCategoria' => 'Helados',
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    expect($this->producto->fresh()->categoria->nombre)->toBe('Helados');

    $this->put(route('inventario.editarProducto', 'REL-1'), [
        'nombre' => 'Helado', 'precioVenta' => 1000, 'idCategoria' => '',
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    expect($this->producto->fresh()->idCategoria)->toBeNull()->and(Categoria::count())->toBe(1);
});

test('reutiliza entidades activas con el mismo nombre sin importar mayusculas o espacios', function () {
    $categoria = Categoria::create(['nombre' => 'Helados']);
    $proveedor = Proveedor::create(['nombre' => 'Distribuidora']);
    $this->post(route('productos.crearProducto'), $this->nuevoProducto + [
        'idCategoria' => 'nuevo', 'nuevaCategoria' => ' helados ', 'idProveedor' => 'nuevo', 'nuevoProveedor' => ' DISTRIBUIDORA ',
        'stockInicial' => 1, 'precioCompra' => 400, 'fechaVencimiento' => now()->addMonth()->toDateString(),
    ])->assertSessionHas('success');
    expect(Producto::findOrFail('REL-2')->idCategoria)->toBe($categoria->idCategoria)
        ->and(Ingreso::firstOrFail()->idProveedor)->toBe($proveedor->idProveedor)
        ->and(Categoria::count())->toBe(1)->and(Proveedor::count())->toBe(1);
});

test('los ingresos permiten elegir proveedor existente crearlo o dejarlo sin asignar', function (string $opcion) {
    $proveedor = Proveedor::create(['nombre' => 'Existente']);
    $seleccion = match ($opcion) {
        'existente' => $proveedor->idProveedor,
        'nuevo' => 'nuevo',
        default => '',
    };
    $this->post(route('inventario.ingresos.store'), $this->ingreso + [
        'idProveedor' => $seleccion, 'nuevoProveedor' => 'Nuevo proveedor',
    ])->assertRedirect(route('inventario.index'))->assertSessionHasNoErrors()->assertSessionHas('success');
    $ingreso = Ingreso::firstOrFail();
    expect($ingreso->proveedor?->nombre)->toBe(match ($opcion) {
        'existente' => 'Existente', 'nuevo' => 'Nuevo proveedor', default => null,
    })->and((int) Producto::conDetalle()->firstOrFail()->stock)->toBe(3)
        ->and(Proveedor::count())->toBe($opcion === 'nuevo' ? 2 : 1);
})->with(['existente', 'nuevo', 'sin']);

test('un ingreso sin proveedor sigue visible y no hereda el proveedor del ingreso anterior', function () {
    $proveedor = Proveedor::create(['nombre' => 'Anterior']);
    $this->post(route('inventario.ingresos.store'), $this->ingreso + ['idProveedor' => $proveedor->idProveedor])->assertSessionHas('success');
    $this->post(route('inventario.ingresos.store'), array_replace($this->ingreso, ['idProveedor' => '', 'precioCompra' => 600]))->assertSessionHas('success');
    $producto = Producto::conDetalle()->firstOrFail();
    expect($producto->proveedor)->toBeNull()->and((float) $producto->costo)->toBe(600.0)->and((int) $producto->stock)->toBe(6);
    $this->get(route('historial.index'))->assertOk()->assertViewHas('movimientos', function ($movimientos) {
        return $movimientos->count() === 2 && $movimientos->contains(fn ($movimiento) => $movimiento['tipo'] === 'Ingreso' && $movimiento['proveedor'] === 'Sin proveedor');
    });
});

test('no guarda nuevas entidades si los nombres o el resto del formulario son invalidos', function (mixed $nombre) {
    $this->postJson(route('productos.crearProducto'), $this->nuevoProducto + [
        'idCategoria' => 'nuevo', 'nuevaCategoria' => $nombre,
        'idProveedor' => 'nuevo', 'nuevoProveedor' => $nombre, 'stockInicial' => 3,
        'precioCompra' => 400, 'fechaVencimiento' => now()->addMonth()->toDateString(),
    ])->assertUnprocessable()->assertJsonValidationErrors(['nuevaCategoria', 'nuevoProveedor']);
    expect(Producto::count())->toBe(1)->and(Categoria::count())->toBe(0)->and(Proveedor::count())->toBe(0)->and(Ingreso::count())->toBe(0);
})->with(['vacio' => '', 'largo' => str_repeat('a', 101), 'array' => [['nombre']]]);

test('rechaza referencias inexistentes o eliminadas sin crear movimientos', function (bool $eliminados) {
    $categoria = Categoria::create(['nombre' => 'Eliminada']);
    $proveedor = Proveedor::create(['nombre' => 'Eliminado']);
    $categoria->delete();
    $proveedor->delete();
    $this->postJson(route('productos.crearProducto'), $this->nuevoProducto + [
        'idCategoria' => $eliminados ? $categoria->idCategoria : 9999,
        'idProveedor' => $eliminados ? $proveedor->idProveedor : 9999,
        'stockInicial' => 1, 'precioCompra' => 400, 'fechaVencimiento' => now()->addMonth()->toDateString(),
    ])->assertUnprocessable()->assertJsonValidationErrors(['idCategoria', 'idProveedor']);
    expect(Producto::count())->toBe(1)->and(Ingreso::count())->toBe(0);
})->with([true, false]);

test('un fallo al guardar stock revierte tambien las entidades creadas desde el formulario', function () {
    DetalleIngreso::creating(fn () => throw new RuntimeException('Fallo de prueba'));
    try {
        $this->post(route('productos.crearProducto'), $this->nuevoProducto + [
            'idCategoria' => 'nuevo', 'nuevaCategoria' => 'Helados', 'idProveedor' => 'nuevo', 'nuevoProveedor' => 'Distribuidora',
            'stockInicial' => 3, 'precioCompra' => 400, 'fechaVencimiento' => now()->addMonth()->toDateString(),
        ])->assertRedirect()->assertSessionHas('error');
        expect(Producto::count())->toBe(1)->and(Categoria::count())->toBe(0)->and(Proveedor::count())->toBe(0)
            ->and(Ingreso::count())->toBe(0)->and(DB::table('lista_precio')->count())->toBe(0);
    } finally {
        DetalleIngreso::flushEventListeners();
    }
});

test('el cajero puede ingresar sin proveedor pero no crear proveedores desde inventario', function () {
    $cajero = User::create(['nombre' => 'Cajero', 'email' => 'cajero.relaciones@test.cl', 'contrasena' => 'secret', 'rol' => 'Cajero', 'activo' => true]);
    $this->actingAs($cajero)->post(route('inventario.ingresos.store'), $this->ingreso + [
        'idProveedor' => 'nuevo', 'nuevoProveedor' => 'Sin permiso',
    ])->assertNotFound();
    expect(Ingreso::count())->toBe(0)->and(Proveedor::count())->toBe(0);
    $this->post(route('inventario.ingresos.store'), $this->ingreso + ['idProveedor' => ''])->assertSessionHas('success');
    $this->get(route('inventario.index'))->assertOk()->assertDontSee('id="ingreso_idProveedor_nombre"', false);
});

test('la validacion recupera el formulario y los nombres nuevos sin guardarlos', function () {
    $this->from(route('inventario.index'))->post(route('productos.crearProducto'), array_replace($this->nuevoProducto, [
        'precioVenta' => -1, 'idCategoria' => 'nuevo', 'nuevaCategoria' => 'Categoría conservada',
        'idProveedor' => 'nuevo', 'nuevoProveedor' => 'Proveedor conservado', 'stockInicial' => 2,
    ]))->assertSessionHasErrors(['precioVenta', 'precioCompra', 'fechaVencimiento']);
    $this->get(route('inventario.index'))->assertOk()->assertSee('Categoría conservada')->assertSee('Proveedor conservado')
        ->assertSee('data-modal-error="modalCrearProducto"', false)->assertSee('collapse show', false);
    expect(Categoria::count())->toBe(0)->and(Proveedor::count())->toBe(0);
});

test('hacer opcional el proveedor conserva su clave foranea', function () {
    expect(fn () => Ingreso::create([
        'fecha' => now(), 'idUsuario' => $this->admin->idUsuario, 'idProveedor' => 99999, 'totalCompra' => 0,
    ]))->toThrow(QueryException::class);
});

test('revertir la migracion no borra ni sustituye ingresos sin proveedor', function () {
    $this->post(route('inventario.ingresos.store'), $this->ingreso)->assertSessionHas('success');
    $migracion = require database_path('migrations/2026_11_09_020000_allow_ingresos_without_proveedor.php');
    expect(fn () => $migracion->down())->toThrow(RuntimeException::class, 'existen ingresos sin proveedor');
    expect(Ingreso::firstOrFail()->idProveedor)->toBeNull()->and(DetalleIngreso::firstOrFail()->cantidad)->toBe(3);
});
