<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        // Listamos a los usuarios internos del sistema
        $usuarios = User::all();

        return view('usuarios.index', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuario,email',
            'contrasena' => 'required|string|min:4',
            'rol' => ['required', Rule::in(User::ROLES)],
        ]);

        User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'contrasena' => bcrypt($request->contrasena),
            'rol' => $request->rol,
            'activo' => true,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function estado(Request $request, int $idUsuario)
    {
        $datos = $request->validate(['activo' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $idUsuario, $datos) {
            $admins = User::where('rol', 'Administrador')->where('activo', true)->orderBy('idUsuario')->lockForUpdate()->get();
            $usuario = User::whereKey($idUsuario)->lockForUpdate()->firstOrFail();
            if (! User::whereKey($request->user()->idUsuario)->where('activo', true)->exists()) {
                abort(403, 'Tu cuenta ya no está activa.');
            }
            if (! $datos['activo'] && $usuario->idUsuario === $request->user()->idUsuario) {
                throw ValidationException::withMessages(['activo' => 'No puedes desactivar tu propia cuenta.']);
            }
            if (! $datos['activo'] && $usuario->rol === 'Administrador' && $usuario->activo && $admins->count() <= 1) {
                throw ValidationException::withMessages(['activo' => 'Debe quedar al menos un administrador activo.']);
            }
            if ($usuario->activo === (bool) $datos['activo']) {
                $request->attributes->set('accion_sin_cambios', true);

                return;
            }
            $usuario->update(['activo' => (bool) $datos['activo']]);
        });

        return redirect()->route('usuarios.index')->with('success', $request->boolean('activo') ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
    }
}
