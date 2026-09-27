<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoSalida extends Model
{

    protected $table = 'tipo_salida';

    protected $primaryKey = 'idTipo';

    public $timestamps = false;


    protected $fillable = [
        'nombre'
    ];



    public function salidas()
    {
        return $this->hasMany(
            Salida::class,
            'idTipo',
            'idTipo'
        );
    }

}