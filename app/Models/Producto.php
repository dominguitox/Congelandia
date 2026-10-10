<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'producto';
    protected $primaryKey = 'codigo';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'idCategoria',
    ];

<<<<<<< Updated upstream
=======
    public function categoria()
    {
        return $this->belongsTo(
            Categoria::class,
            'idCategoria',
            'idCategoria'
        );
    }
>>>>>>> Stashed changes

    /**
     * Un producto puede aparecer en varios detalles de ingreso.
     */
    public function detallesIngreso()
    {
        return $this->hasMany(
            DetalleIngreso::class,
            'codigoProducto',
            'codigo'
        );
    }
}