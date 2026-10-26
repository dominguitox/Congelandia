<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $table = 'versiones';
    public $timestamps = false; 
    protected $fillable = ['numero', 'comentarios', 'activa'];
}
