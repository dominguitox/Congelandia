<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;
    protected $table = 'Producto';
    protected $primaryKey = 'codigo';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;


    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'idCategoria'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }
    public function imagenes()
    {
        return $this->hasMany(ImagenProducto::class, 'codigoProducto', 'codigo');
    }
    // Relación para obtener el precio actual del producto
    public function precioActual()
    {
        return $this->hasOne(ListaPrecio::class, 'codigoProducto', 'codigo')
            ->whereNull('fechaFin')
            ->latest('fechaInicio');
    }

}