<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    protected $table = 'salida';
    protected $primaryKey = 'idSalida';
    public $timestamps = false;
    protected $fillable = [
        'fecha',
        'idTipo',
        'idUsuario',
        'rutCliente',
        'totalSalida'
    ];

    // Relación correcta hacia los detalles
    public function detalles()
    {
        return $this->hasMany(DetalleSalida::class, 'idSalida', 'idSalida');
    }
    public function tipo()
    {
        return $this->belongsTo(TipoSalida::class, 'idTipo', 'idTipo');
    }
    public function pagos()
    {
        // El primer parámetro es el modelo de los pagos.
        // El segundo es la llave foránea en la tabla de pagos (ej: 'idSalida').
        // El tercero es la llave local en esta tabla (ej: 'idSalida').
        return $this->hasMany(PagoSalida::class, 'idSalida', 'idSalida');
    }
}