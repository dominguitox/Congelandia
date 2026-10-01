<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use SoftDeletes;
    protected $table = 'Proveedor';
    protected $primaryKey = 'idProveedor';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
    ];
}
