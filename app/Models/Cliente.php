<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

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
    public function salidas()
    {
        return $this->hasMany(
            Salida::class,
            'rutCliente',
            'rutCliente'
        );
    }
}