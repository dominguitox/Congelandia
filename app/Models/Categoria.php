<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;

    protected $table = 'categoria';

    protected $primaryKey = 'idCategoria';

    protected $keyType = 'integer';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    /**
     * Una categoría puede tener muchos productos.
     */
    public function productos()
    {
        return $this->hasMany(
            Producto::class,
            'idCategoria',
            'idCategoria'
        );
    }
}