<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $table = 'ingreso';
    protected $primaryKey = 'idIngreso';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'idProveedor',
        'idUsuario',
        'totalCompra',
    ];

    /**
     * Un ingreso pertenece a un proveedor.
     */
    public function proveedor()
    {
        return $this->belongsTo(
            Proveedor::class,
            'idProveedor',
            'idProveedor'
        );
    }

    /**
     * Un ingreso puede tener muchos detalles.
     */
    public function detalles()
    {
        return $this->hasMany(
            DetalleIngreso::class,
            'idIngreso',
            'idIngreso'
        );
    }
}