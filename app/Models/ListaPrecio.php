<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaPrecio extends Model
{
    protected $table = 'lista_precio';
    protected $primaryKey = 'idPrecio';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'codigoProducto',
        'precioVenta',
        'fechaInicio',
        'fechaFin'
    ];
}
