<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Crea el usuario administrador inicial del sistema.
     */
    public function run(): void
    {
        User::create([
            'nombre' => 'Admin principal',
            'email' => 'admin@congelandia.cl',
            'rol' => 'Administrador',
            'contrasena' => Hash::make('1234'),
            'activo' => true,
        ]);
    }
}
