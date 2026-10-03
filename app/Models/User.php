<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $table = 'Usuario';
    protected $primaryKey = 'idUsuario';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'nombre',
        'email',
        'contrasena',
        'rol',
        'activo'
    ];
    protected $hidden = [
        'contrasena',
    ];
    public function getAuthPassword()
    {
        return $this->contrasena;
    }
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }
    public function getRememberTokenName()
    {
        return null;
    }
    public function tieneRol($rolEsperado)
    {
        return trim($this->rol) === trim($rolEsperado);
    }
}