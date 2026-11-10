<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_acciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idUsuario')->nullable();
            $table->string('usuarioNombre', 100);
            $table->string('accion', 100);
            $table->string('modulo', 50);
            $table->string('ruta', 100);
            $table->string('referencia', 255)->nullable();
            $table->dateTime('fecha');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuario')->nullOnDelete();
            $table->index(['idUsuario', 'fecha']);
            $table->index(['modulo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_acciones');
    }
};
