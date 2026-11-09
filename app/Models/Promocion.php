<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promocion extends Model
{
    use SoftDeletes;

    protected $table = 'promocion';
    protected $primaryKey = 'idPromocion';
    protected $keyType = 'integer';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'codigoProducto',
        'porcentajeDescuento',
        'fechaInicio',
        'fechaFin',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'fechaInicio' => 'datetime',
            'fechaFin' => 'datetime',
        ];
    }

    public function scopeVigentes(Builder $query): Builder
    {
        $ahora = now();

        return $query
            ->where('fechaInicio', '<=', $ahora)
            ->where('fechaFin', '>=', $ahora)
            ->where('activa', true)
            ->where('porcentajeDescuento', '>', 0)
            ->whereHas('producto');
    }

    public function producto()
    {
        // Vincula codigoProducto con la clave codigo de Producto.
        return $this->belongsTo(Producto::class, 'codigoProducto', 'codigo');
    }
}
