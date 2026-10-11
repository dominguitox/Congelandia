<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenProducto extends Model
{
    protected $table = 'IMAGEN_PRODUCTO';
    public $timestamps = false;
    
    protected $fillable = ['rutaImagen', 'codigoProducto', 'alt', 'descripcion'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigoProducto', 'id');
    }
}