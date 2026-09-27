<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleSalida extends Model
{

    protected $table = 'detalle_salida';

    protected $primaryKey = 'idDetalle';

    public $timestamps = false;


    protected $fillable = [
        'idSalida',
        'codigoProducto',
        'cantidad',
        'precioCobrado'
    ];



    public function salida()
    {
        return $this->belongsTo(
            Salida::class,
            'idSalida',
            'idSalida'
        );
    }



    public function producto()
    {
        return $this->belongsTo(
            Producto::class,
            'codigoProducto',
            'codigo'
        );
    }

}