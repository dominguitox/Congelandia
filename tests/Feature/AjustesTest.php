<?php

use App\Models\User;
use App\Support\DeviceTheme;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    config(['theme.style' => 'congelandia', 'theme.color' => 'base']);
});

function usuarioParaAjustes(string $rol = 'Administrador'): User
{
    return User::create(['nombre' => 'Ajustes', 'email' => strtolower($rol).'@ajustes.test',
        'contrasena' => bcrypt('secreto'), 'rol' => $rol, 'activo' => true]);
}

test('ajustes exige autenticacion para consultar y guardar', function () {
    $this->get(route('ajustes.index'))->assertRedirect(route('login'));
    $this->put(route('ajustes.update'), ['theme' => 'win7', 'color' => 'coral'])
        ->assertRedirect(route('login'))->assertCookieMissing(DeviceTheme::COOKIE);
});

test('cajero no ve ajustes ni puede cambiar la preferencia del dispositivo', function () {
    $this->actingAs(usuarioParaAjustes('Cajero'));
    $this->get(route('ajustes.index'))->assertNotFound();
    $this->put(route('ajustes.update'), ['theme' => 'win7', 'color' => 'coral'])
        ->assertNotFound()->assertCookieMissing(DeviceTheme::COOKIE);
    $this->get(route('pos.index'))->assertOk()
        ->assertDontSee('href="'.route('ajustes.index').'"', false);
});

test('admin guarda todas las combinaciones sin cambiar la configuracion global', function () {
    $this->actingAs(usuarioParaAjustes());
    $original = config('theme');
    foreach (['congelandia', 'md3', 'win7'] as $style) {
        foreach (['base', 'esmeralda', 'aurora', 'coral'] as $color) {
            $preferences = json_encode(['theme' => $style, 'color' => $color]);
            $response = $this->put(route('ajustes.update'), ['theme' => $style, 'color' => $color])
                ->assertRedirect(route('ajustes.index'))
                ->assertCookie(DeviceTheme::COOKIE, $preferences);
            $cookie = $response->getCookie(DeviceTheme::COOKIE);
            expect($cookie->getExpiresTime())->toBeGreaterThan(now()->addDays(300)->timestamp)
                ->and($cookie->isHttpOnly())->toBeTrue()
                ->and($cookie->getDomain())->toBeNull()
                ->and($cookie->getPath())->toBe('/')
                ->and($cookie->getSameSite())->toBe('lax');
            $this->withCookie(DeviceTheme::COOKIE, $preferences)
                ->get(route('ajustes.index'))->assertOk()
                ->assertSee('data-theme="'.$style.'"', false)
                ->assertSee('data-color="'.$color.'"', false)
                ->assertSee('value="'.$style.'" selected', false)
                ->assertSee('value="'.$color.'" selected', false)
                ->assertSee('href="'.route('ajustes.index').'"', false);
            expect(config('theme'))->toBe($original);
        }
    }

    // A different browser has no valid preference, even for the same account.
    $this->withCookie(DeviceTheme::COOKIE, '')->get(route('ajustes.index'))->assertOk()
        ->assertSee('data-theme="congelandia"', false)->assertSee('data-color="base"', false);
    expect(User::count())->toBe(1);
});

test('preferencia del dispositivo permanece al cerrar sesion y cambiar de cuenta', function () {
    $preferences = json_encode(['theme' => 'win7', 'color' => 'esmeralda']);
    $this->actingAs(usuarioParaAjustes())->withCookie(DeviceTheme::COOKIE, $preferences);
    $this->post(route('logout'))->assertRedirect('/')->assertCookieMissing(DeviceTheme::COOKIE);
    $this->get(route('login'))->assertOk()->assertSee('data-theme="win7"', false)
        ->assertSee('data-color="esmeralda"', false)->assertSee('class="bg-light win7"', false);
    $this->actingAs(usuarioParaAjustes('Cajero'))->get(route('pos.index'))->assertOk()
        ->assertSee('data-theme="win7"', false)->assertSee('data-color="esmeralda"', false);
    $this->get('/contacto?estilo=feria')->assertOk()->assertSee('data-estilo="feria"', false)
        ->assertDontSee('data-theme=', false)->assertDontSee('data-color=', false)
        ->assertDontSee('class="bg-light win7"', false);
});

test('valores de preferencias invalidos no se guardan', function (array $preferences) {
    $this->actingAs(usuarioParaAjustes())->putJson(route('ajustes.update'), $preferences)
        ->assertUnprocessable()->assertCookieMissing(DeviceTheme::COOKIE);
})->with([
    [['theme' => '../../public', 'color' => 'base']],
    [['theme' => 'win7', 'color' => 'inexistente']],
    [['theme' => ['win7'], 'color' => 'base']],
    [[]],
]);

test('cookies antiguas o mal formadas usan valores seguros', function (string $cookie, string $style, string $color) {
    $this->withCookie(DeviceTheme::COOKIE, $cookie)->get(route('login'))->assertOk()
        ->assertSee('data-theme="'.$style.'"', false)->assertSee('data-color="'.$color.'"', false);
})->with([
    ['{malformado', 'congelandia', 'base'],
    ['null', 'congelandia', 'base'],
    ['{"theme":["win7"],"color":"coral"}', 'congelandia', 'coral'],
    ['{"theme":"../../public","color":{}}', 'congelandia', 'base'],
    ['{"theme":"md3","color":"eliminado"}', 'md3', 'base'],
]);
