<?php

use App\Models\Producto;
use App\Models\User;
use App\Support\CodigoBarrasInterno;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->admin = User::create([
        'nombre' => 'Admin códigos',
        'email' => 'codigos@test.cl',
        'contrasena' => 'secret',
        'rol' => 'Administrador',
        'activo' => true,
    ]);
    $this->cajero = User::create([
        'nombre' => 'Cajero códigos',
        'email' => 'cajero-codigos@test.cl',
        'contrasena' => 'secret',
        'rol' => 'Cajero',
        'activo' => true,
    ]);
});

test('el formato EAN-13 interno calcula el dígito verificador', function () {
    expect(CodigoBarrasInterno::conDigitoVerificador('590123412345'))->toBe('5901234123457')
        ->and(CodigoBarrasInterno::digitoVerificador('400638133393'))->toBe(1)
        ->and(CodigoBarrasInterno::esValido('5901234123457'))->toBeTrue()
        ->and(CodigoBarrasInterno::esValido('5901234123458'))->toBeFalse()
        ->and(CodigoBarrasInterno::esValido('590123412345'))->toBeFalse();
});

test('el código generado codifica la fecha y la secuencia diaria', function () {
    $fecha = new DateTimeImmutable('2026-11-16');

    expect(CodigoBarrasInterno::generar($fecha, 1))->toBe('2026111600012')
        ->and(CodigoBarrasInterno::generar($fecha, 37))->toBe('2026111600371')
        ->and(CodigoBarrasInterno::generar($fecha, 9999))->toBe('2026111699993')
        ->and(CodigoBarrasInterno::generar($fecha, 9999))->toHaveLength(CodigoBarrasInterno::LONGITUD)
        ->and(CodigoBarrasInterno::esValido(CodigoBarrasInterno::generar($fecha, 500)))->toBeTrue()
        ->and(CodigoBarrasInterno::secuenciaDe('2026111600012', $fecha))->toBe(1)
        ->and(CodigoBarrasInterno::secuenciaDe('2026111600012', new DateTimeImmutable('2026-11-17')))->toBeNull()
        ->and(CodigoBarrasInterno::secuenciaDe('ORIGEN-1'))->toBeNull();
});

test('la secuencia debe estar dentro del rango diario permitido', function () {
    $fecha = new DateTimeImmutable('2026-11-16');

    expect(fn () => CodigoBarrasInterno::generar($fecha, 0))->toThrow(InvalidArgumentException::class)
        ->and(fn () => CodigoBarrasInterno::generar($fecha, CodigoBarrasInterno::MAX_SECUENCIA + 1))->toThrow(InvalidArgumentException::class);
});

test('el endpoint genera un código válido con la fecha de hoy y sin repetir los registrados', function () {
    $this->travelTo('2026-11-16 10:00:00');

    Producto::create(['codigo' => 'ORIGEN-1', 'nombre' => 'Producto con código de origen']);

    $respuesta = $this->actingAs($this->admin)
        ->postJson(route('productos.generarCodigoInterno'))
        ->assertOk()
        ->assertJsonPath('formato', 'EAN-13')
        ->assertJsonStructure(['codigo', 'formato', 'mensaje']);

    $codigo = $respuesta->json('codigo');

    expect($codigo)->toBe('2026111600012')
        ->and(CodigoBarrasInterno::esValido($codigo))->toBeTrue()
        ->and(Producto::withTrashed()->whereKey($codigo)->exists())->toBeFalse();
});

test('el endpoint continúa la secuencia del día e incluye los productos eliminados', function () {
    $this->travelTo('2026-11-16 10:00:00');

    Producto::create(['codigo' => CodigoBarrasInterno::formatear(now(), 1), 'nombre' => 'Interno 1']);
    $eliminado = Producto::create(['codigo' => CodigoBarrasInterno::formatear(now(), 2), 'nombre' => 'Interno 2']);
    $eliminado->delete();
    Producto::create(['codigo' => CodigoBarrasInterno::formatear(now()->subDay(), 50), 'nombre' => 'Interno de ayer']);

    $this->actingAs($this->admin)
        ->postJson(route('productos.generarCodigoInterno'))
        ->assertOk()
        ->assertJsonPath('codigo', CodigoBarrasInterno::formatear(now(), 3));
});

test('el endpoint avisa cuando se agota la secuencia diaria', function () {
    $this->travelTo('2026-11-16 10:00:00');

    Producto::create([
        'codigo' => CodigoBarrasInterno::formatear(now(), CodigoBarrasInterno::MAX_SECUENCIA),
        'nombre' => 'Último producto interno del día',
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('productos.generarCodigoInterno'))
        ->assertStatus(503)
        ->assertJsonPath('message', 'Se agotó la secuencia diaria de códigos internos (9999). Ingresa un código manualmente.');
});

test('un código duplicado o eliminado no se vuelve a guardar y ofrece regenerarlo', function () {
    $producto = Producto::create(['codigo' => '1234567890128', 'nombre' => 'Producto existente']);
    $producto->delete();

    $this->actingAs($this->admin)
        ->from(route('inventario.index'))
        ->post(route('productos.crearProducto'), [
            'codigo' => '1234567890128',
            'nombre' => 'Producto duplicado',
            'precioVenta' => 1000,
            '_modal' => 'modalCrearProducto',
        ])
        ->assertRedirect(route('inventario.index'))
        ->assertSessionHasErrors([
            'codigo' => 'Este código ya está registrado. Genera un código interno nuevo o ingresa otro distinto.',
        ]);

    expect(Producto::withTrashed()->whereKey('1234567890128')->count())->toBe(1);
});

test('los códigos de origen manuales siguen siendo válidos', function () {
    $this->actingAs($this->admin)
        ->from(route('inventario.index'))
        ->post(route('productos.crearProducto'), [
            'codigo' => 'ORIGEN-123',
            'nombre' => 'Producto escaneado',
            'precioVenta' => 1500,
            '_modal' => 'modalCrearProducto',
        ])
        ->assertRedirect(route('inventario.index'))
        ->assertSessionHasNoErrors();

    expect(Producto::whereKey('ORIGEN-123')->exists())->toBeTrue();
});

test('solo un administrador puede solicitar un código interno', function () {
    $this->actingAs($this->cajero)
        ->postJson(route('productos.generarCodigoInterno'))
        ->assertNotFound();
});
