<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroAccion extends Model
{
    protected $table = 'registro_acciones';

    public $timestamps = false;

    protected $fillable = ['idUsuario', 'usuarioNombre', 'accion', 'modulo', 'ruta', 'referencia', 'fecha'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }
}
