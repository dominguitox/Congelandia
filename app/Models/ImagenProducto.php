<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenProducto extends Model
{
    protected $table = 'Imagen_Producto';
    public $timestamps = false;
    
    protected $fillable = ['rutaImagen', 'codigoProducto', 'alt', 'descripcion'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigoProducto', 'id');
    }
}