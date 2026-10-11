<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoVenta extends Model
{
    protected $table = 'Detalle_Salida'; // Según el diagrama relacional
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'cantidad',
        'precioCobrado',
        'codigoProducto', // FK a tabla PRODUCTO
        'idSalida' // FK a tabla salida
    ];

    // Relación para traer el nombre del producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Producto', 'codigo');
    }
}