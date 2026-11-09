<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListaPrecio extends Model
{
    use SoftDeletes;

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
