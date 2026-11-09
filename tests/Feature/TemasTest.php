<?php

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Artisan;

function configurationForTheme(?string $theme, ?string $color): array
{
    $repository = Env::getRepository();
    $original = ['APP_TEMA' => $repository->get('APP_TEMA'), 'APP_COLOR' => $repository->get('APP_COLOR')];

    try {
        foreach (['APP_TEMA' => $theme, 'APP_COLOR' => $color] as $key => $value) {
            $value === null ? $repository->clear($key) : $repository->set($key, $value);
        }

        return require config_path('theme.php');
    } finally {
        foreach ($original as $key => $value) {
            $value === null ? $repository->clear($key) : $repository->set($key, $value);
        }
    }
}

test('resuelve tema y color con compatibilidad y valores seguros', function ($theme, $color, $expectedTheme, $expectedColor) {
    $config = configurationForTheme($theme, $color);

    expect($config['style'])->toBe($expectedTheme)
        ->and($config['color'])->toBe($expectedColor);

    foreach (['style_asset', 'color_asset'] as $key) {
        if ($config[$key] !== null) {
            expect(is_file(base_path($config[$key])))->toBeTrue();
        }
    }
})->with([
    'por defecto' => [null, null, 'congelandia', 'base'],
    'legacy base' => ['base', null, 'congelandia', 'base'],
    'legacy esmeralda' => ['esmeralda', null, 'congelandia', 'esmeralda'],
    'legacy aurora' => ['aurora', null, 'congelandia', 'aurora'],
    'legacy coral' => ['coral', null, 'congelandia', 'coral'],
    'color explicito prevalece' => ['aurora', 'esmeralda', 'congelandia', 'esmeralda'],
    'normaliza' => [' MD3 ', ' ESMERALDA ', 'md3', 'esmeralda'],
    'tema desconocido' => ['inexistente', 'coral', 'congelandia', 'coral'],
    'color desconocido' => ['md3', 'inexistente', 'md3', 'base'],
    'rutas no permitidas' => ['../../app', '../../public', 'congelandia', 'base'],
    'vacios' => ['', '', 'congelandia', 'base'],
]);

test('el acceso aplica las ocho combinaciones sin alterar los temas del sitio publico', function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    config(['public.estilo_default' => 'hielo', 'public.selector_estilos' => true]);

    foreach (['congelandia', 'md3'] as $style) {
        foreach (['base', 'esmeralda', 'aurora', 'coral'] as $color) {
            config(['theme' => configurationForTheme($style, $color)]);
            $this->get('/login')->assertOk()
                ->assertSee('data-theme="'.$style.'"', false)
                ->assertSee('data-color="'.$color.'"', false);

            $this->get('/contacto?estilo=feria')->assertOk()
                ->assertSee('data-estilo="feria"', false)
                ->assertSee('has-stylebar')
                ->assertDontSee('data-theme=', false)
                ->assertDontSee('data-color=', false);
        }
    }
});
