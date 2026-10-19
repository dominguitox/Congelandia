<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Salida', function (Blueprint $table) {
            $table->id('idSalida');
            $table->dateTime('fecha');
            $table->unsignedBigInteger('idTipo');
            $table->unsignedBigInteger('idUsuario');
            $table->string('rutCliente', 15)->nullable();
            $table->decimal('totalSalida', 12, 2)->default(0.00);
            $table->softDeletes();

            $table->foreign('idTipo')->references('idTipo')->on('Tipo_Salida');
            $table->foreign('idUsuario')->references('idUsuario')->on('Usuario');
            $table->foreign('rutCliente')->references('rutCliente')->on('Cliente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Salida');
    }
};
