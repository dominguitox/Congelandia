<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DetalleSalida extends Model
{
    protected $table = 'Detalle_Salida';
    public $timestamps = false;
    protected $fillable = [
        'idSalida',
        'codigoProducto',
        'cantidad',
        'precioCobrado',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigoProducto', 'codigo');
    }
}