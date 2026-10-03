<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:Usuario,email',
            'contrasena' => 'required|min:4',
            'rol' => 'nullable|string',


        ]);

        $datos['contrasena'] = Hash::make($datos['contrasena']);
        $datos['activo'] = true;


        \App\Models\User::create($datos);
        return redirect()->route('dashboard.index');
    }
}
