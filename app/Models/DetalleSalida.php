<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleSalida extends Model
{
    use SoftDeletes;

    protected $table = 'detalle_salida';

    protected $primaryKey = 'idDetalle';

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
