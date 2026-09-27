<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{

    protected $table = 'Cliente';


    protected $primaryKey = 'rutCliente';


    public $incrementing = false;


    protected $keyType = 'string';


    public $timestamps = false;


    protected $fillable = [

        'rutCliente',
        'nombre',
        'telefono',
        'saldoDeuda'

    ];

}