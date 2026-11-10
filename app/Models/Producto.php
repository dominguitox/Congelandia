<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    /** Datos compartidos por inventario, POS y dashboard; los borrados no suman stock. */
    public function scopeConDetalle(Builder $query): Builder
    {
        $tabla = $this->getTable();
        $ingreso = (new Ingreso)->getTable();
        $detalleIngreso = (new DetalleIngreso)->getTable();
        $salida = (new Salida)->getTable();
        $detalleSalida = (new DetalleSalida)->getTable();
        $proveedor = (new Proveedor)->getTable();
        $categoria = (new Categoria)->getTable();

        $ultimoIngreso = DB::table($detalleIngreso.' as di')
            ->join($ingreso.' as i', 'di.idIngreso', '=', 'i.idIngreso')
            ->whereColumn('di.codigoProducto', $tabla.'.codigo')
            ->whereNull('di.deleted_at')->whereNull('i.deleted_at')
            ->orderByDesc('i.fecha')->orderByDesc('di.idDetalle');

        return $query->select($tabla.'.*')
            ->addSelect([
                'categoria' => DB::table($categoria)->select('nombre')
                    ->whereColumn('idCategoria', $tabla.'.idCategoria')->whereNull('deleted_at')->limit(1),
                'precio' => ListaPrecio::select('precioVenta')
                    ->whereColumn('codigoProducto', $tabla.'.codigo')
                    ->where('fechaInicio', '<=', now())->whereNull('fechaFin')
                    ->orderByDesc('fechaInicio')->orderByDesc('idPrecio')->limit(1),
                'costo' => (clone $ultimoIngreso)->select('di.precioCompra')->limit(1),
                'fechaVencimiento' => (clone $ultimoIngreso)->select('di.fechaVencimiento')->limit(1),
                'proveedor' => (clone $ultimoIngreso)->leftJoin($proveedor.' as prov', 'i.idProveedor', '=', 'prov.idProveedor')
                    ->select('prov.nombre')->whereNull('prov.deleted_at')->limit(1),
            ])
            ->selectRaw("(COALESCE((SELECT SUM(di.cantidad) FROM {$detalleIngreso} di INNER JOIN {$ingreso} i ON di.idIngreso = i.idIngreso WHERE di.codigoProducto = {$tabla}.codigo AND di.deleted_at IS NULL AND i.deleted_at IS NULL), 0) - COALESCE((SELECT SUM(ds.cantidad) FROM {$detalleSalida} ds INNER JOIN {$salida} s ON ds.idSalida = s.idSalida WHERE ds.codigoProducto = {$tabla}.codigo AND ds.deleted_at IS NULL AND s.deleted_at IS NULL), 0)) AS stock");
    }

    // Alias usado por los botones de edición, sin repetir la consulta del precio.
    public function getPrecioVentaAttribute(): ?float
    {
        return isset($this->attributes['precio']) ? (float) $this->attributes['precio'] : null;
    }

    /**
     * Un producto pertenece a una categoría.
     */
    public function categoria()
    {
        return $this->belongsTo(
            Categoria::class,
            'idCategoria',
            'idCategoria'
        );
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenProducto::class, 'codigoProducto', 'codigo');
    }

    public function promociones()
    {
        return $this->hasMany(Promocion::class, 'codigoProducto', 'codigo');
    }

    // Relación para obtener el precio actual del producto
    public function precioActual()
    {
        return $this->hasOne(ListaPrecio::class, 'codigoProducto', 'codigo')
            ->whereNull('fechaFin')
            ->latest('fechaInicio');
    }

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
