<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// La entrega declara su carpeta pública. No depender de carpetas antiguas que
// puedan haber quedado en el hosting tras una instalación manual.
$releasePath = dirname(__DIR__).'/release.json';
if (is_file($releasePath)) {
    $release = json_decode(file_get_contents($releasePath), true, flags: JSON_THROW_ON_ERROR);
    $publicDirectory = $release['public_directory'] ?? null;
    if (!in_array($publicDirectory, ['public', 'public_html'], true)) {
        throw new RuntimeException('La entrega no declara una carpeta pública válida.');
    }
    $app->usePublicPath(dirname(__DIR__).'/'.$publicDirectory);
}

return $app;
