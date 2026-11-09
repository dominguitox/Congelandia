<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarUsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($usuario = $request->user()) {
            $actual = User::find($usuario->idUsuario);
            if (! $actual || ! $actual->activo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return $request->expectsJson()
                    ? response()->json(['message' => 'Tu usuario fue desactivado. Inicia sesión con una cuenta activa.'], 401)
                    : redirect()->route('login')->withErrors(['email' => 'Tu usuario fue desactivado.']);
            }
            Auth::setUser($actual);
        }

        return $next($request);
    }
}
