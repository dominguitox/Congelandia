<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promocion extends Model
{
    use SoftDeletes;

    protected $table = 'promocion';
    protected $primaryKey = 'idPromocion';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'codigoProducto',
        'porcentajeDescuento',
        'fechaInicio',
        'fechaFin',
    ];

    public function producto()
    {
        // Vincula 'codigoProducto' de Promocion con el 'id' de Producto
        return $this->belongsTo(Producto::class, 'codigoProducto', 'codigo');
    }
}