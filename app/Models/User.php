<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Tabla asociada al modelo.
     */
    protected $table = 'Usuario';

    /**
     * Llave primaria personalizada.
     */
    protected $primaryKey = 'idUsuario';

    /**
     * Indica que la llave primaria no es autoincremental.
     * Cambia a true si tu tabla usa AUTO_INCREMENT.
     */
    public $incrementing = true;

    /**
     * Tipo de dato de la llave primaria.
     */
    protected $keyType = 'int';

    /**
     * Campos permitidos para asignación masiva.
     */
    protected $fillable = [
        'nombre',
        'email',
        'contrasena',
        'rol',
        'activo'
    ];

    /**
     * Campos ocultos.
     */
    protected $hidden = [
    'contrasena',
];

    /**
     * Laravel por defecto busca una columna llamada password.
     * Esta función le indica usar "contrasena".
     */
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    /**
     * Conversión de atributos.
     */
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
}