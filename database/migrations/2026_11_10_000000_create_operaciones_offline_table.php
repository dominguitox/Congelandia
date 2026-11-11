<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operaciones_offline', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('idUsuario');
            $table->char('firma', 64);
            $table->string('tipo', 16);
            $table->dateTime('fechaLocal');
            $table->json('datos');
            $table->string('estado', 16);
            $table->json('resultado');
            $table->timestamp('created_at');
            $table->index(['idUsuario', 'estado']);
            $table->foreign('idUsuario')->references('idUsuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operaciones_offline');
    }
};
