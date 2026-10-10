<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
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

        $exceptions->render(function (Throwable $e, Request $request) {

            // 1. Ignorar excepciones que no son errores del sistema
            if (
                $e instanceof AuthenticationException ||
                $e instanceof ValidationException ||
                $e instanceof NotFoundHttpException
            ) {
                return null;
            }

            // 2. Solo actuar si no es una petición de API
            if (!$request->is('api/*') && !$request->expectsJson()) {

                // Guardar en base de datos usando una nueva instancia
                $log = new \App\Models\LogError();
                $log->mensaje = substr($e->getMessage(), 0, 500) ?: 'Error sin mensaje';
                $log->archivo = $e->getFile();
                $log->linea = $e->getLine();
                $log->save();    
                // Redirigir atrás con la variable de sesión 'error'
                return redirect()->back()->with('error', 'Ocurrió un error en el sistema.');
            }
        });
    })->create();