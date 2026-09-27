<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{

    protected $table = 'salida';

    protected $primaryKey = 'idSalida';

    public $timestamps = false;


    protected $fillable = [
        'fecha',
        'idTipo',
        'idUsuario',
        'rutCliente',
        'totalSalida'
    ];



    public function cliente()
    {
        return $this->belongsTo(
            Cliente::class,
            'rutCliente',
            'rutCliente'
        );
    }



    public function tipo()
    {
        return $this->belongsTo(
            TipoSalida::class,
            'idTipo',
            'idTipo'
        );
    }



    public function pagos()
    {
        return $this->hasMany(
            PagoSalida::class,
            'idSalida',
            'idSalida'
        );
    }



    public function detalles()
    {
        return $this->hasMany(
            DetalleSalida::class,
            'idSalida',
            'idSalida'
        );
    }

}