<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoVenta extends Model
{
    protected $table = 'PRODUCTO_VENTA'; // Según el diagrama relacional
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'CANTIDAD',
        'PRECIO_COBRADO',
        'PRODUCTO', // FK a tabla PRODUCTO
        'PROMOCION', // FK a tabla PROMOCION
        'VENTA' // FK a tabla VENTA
    ];

    // Relación para traer el nombre del producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'PRODUCTO', 'ID');
    }
}