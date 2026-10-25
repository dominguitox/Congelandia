<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSalida extends Model
{
    use SoftDeletes;

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