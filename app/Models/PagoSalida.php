<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoSalida extends Model
{

    protected $table = 'pago_salida';

    protected $primaryKey = 'idPago';

    public $timestamps = false;


    protected $fillable = [
        'idSalida',
        'idMetodo',
        'montoPagado',
        'fechaPago'
    ];


    protected $casts = [
        'fechaPago' => 'datetime'
    ];



    public function salida()
    {
        return $this->belongsTo(
            Salida::class,
            'idSalida',
            'idSalida'
        );
    }

}