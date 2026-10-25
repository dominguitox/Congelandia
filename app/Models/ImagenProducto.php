<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImagenProducto extends Model
{
    use SoftDeletes;

    protected $table = 'IMAGEN_PRODUCTO';
    public $timestamps = false;
    
    protected $fillable = ['rutaImagen', 'codigoProducto', 'alt', 'descripcion'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigoProducto', 'id');
    }
}