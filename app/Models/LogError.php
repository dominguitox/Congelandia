<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogError extends Model
{
    protected $fillable = ['mensaje', 'archivo', 'linea', 'usuario'];
}
