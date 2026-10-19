<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de usuarios del sistema con el esquema real que usa
 * App\Models\User (tabla "Usuario", PK idUsuario, columna contrasena).
 * Reemplaza la migración original que creaba una tabla "usuarios"
 * estilo Laravel y que ningún modelo utilizaba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Usuario', function (Blueprint $table) {
            $table->id('idUsuario');
            $table->string('nombre', 100);
            $table->string('contrasena', 255);
            $table->string('rol', 50);
            $table->string('email', 100)->unique();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Usuario');
    }
};
