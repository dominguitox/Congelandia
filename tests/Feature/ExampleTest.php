<?php

use Illuminate\Support\Facades\Artisan;

test('the application returns a successful response', function () {
    Artisan::call('migrate:fresh');

    $response = $this->get('/');

    $response->assertStatus(200);
});

test('las paginas publicas de catalogo y contacto responden', function () {
    Artisan::call('migrate:fresh');

    $this->get('/catalogo')->assertOk()->assertSee('Catálogo')
        ->assertSeeHtml('&copy; '.date('Y').' Congelandia. Todos los derechos reservados.')
        ->assertSee('Desarrollado por <a href="https://www.paginaswebtalca.com/" target="_blank" rel="noopener noreferrer">PaginasWebTalca</a>', false)
        ->assertSee('href="https://www.paginaswebtalca.com/" target="_blank" rel="noopener noreferrer"', false);
    $this->get('/contacto')->assertOk()->assertSee('Contacto');
});
