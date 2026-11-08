<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
}