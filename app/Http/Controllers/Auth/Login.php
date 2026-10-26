<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Login extends Controller
{
    public function __invoke(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Buscar usuario por correo en la tabla Usuario
        $user = \App\Models\User::where('email', $credentials['email'])
            ->where('activo', true)
            ->first();

        // Validar contraseña hasheada en la columna contrasena
        if ($user && Hash::check($credentials['password'], $user->contrasena)) {

            Auth::login($user, $request->boolean('remember'));

            $request->session()->regenerate();

            return redirect()
                ->intended('/')
                ->with('success', 'Bienvenido al sistema');
        }

        return back()
            ->withErrors([
                'email' => 'Las credenciales ingresadas no son correctas.'
            ])
            ->onlyInput('email');
    }
}