<?php

use Illuminate\Support\Facades\Artisan;

test('the application returns a successful response', function () {
    Artisan::call('migrate:fresh');

    $response = $this->get('/');

    $response->assertStatus(200);
});
