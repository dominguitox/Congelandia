<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Devolucion', function (Blueprint $table) {
            $table->id('idDevolucion');
            $table->dateTime('fecha');
            $table->unsignedBigInteger('idSalida');
            $table->unsignedBigInteger('idTipo');
            $table->string('codigoProducto', 50);
            $table->integer('cantidad');
            $table->text('comentario')->nullable();

            $table->foreign('idSalida')->references('idSalida')->on('Salida');
            $table->foreign('idTipo')->references('idTipo')->on('Tipo_Devolucion');
            $table->foreign('codigoProducto')->references('codigo')->on('Producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Devolucion');
    }
};
