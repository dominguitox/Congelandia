<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleIngreso extends Model
{
    use SoftDeletes;

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
        'fechaVencimiento',
    ];

    /**
     * El detalle pertenece a un ingreso.
     */
    public function ingreso()
    {
        return $this->belongsTo(
            Ingreso::class,
            'idIngreso',
            'idIngreso'
        );
    }

    /**
     * El detalle pertenece a un producto.
     */
    public function producto()
    {
        return $this->belongsTo(
            Producto::class,
            'codigoProducto',
            'codigo'
        );
    }
}