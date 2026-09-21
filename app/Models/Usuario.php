<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User;

class Usuario extends User
{
    protected $table = 'usuarios';
    protected $fillable = ['nombre', 'email', 'password', 'rol'];
    protected $hidden = ['password'];

    public function tieneRol($rolEsperado)
    {
        return trim($this->rol) === trim($rolEsperado);
    }
}
