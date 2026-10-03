<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleIngreso extends Model
{
    protected $table = 'detalle_ingreso';
    protected $primaryKey = 'idDetalle';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'idIngreso',
        'codigoProducto',
        'cantidad',
        'precioCompra',
        'fechaVencimiento'
    ];
}
