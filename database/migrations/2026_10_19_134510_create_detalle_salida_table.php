<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Detalle_Salida', function (Blueprint $table) {
            $table->id('idDetalle');
            $table->unsignedBigInteger('idSalida');
            $table->string('codigoProducto', 50);
            $table->integer('cantidad');
            $table->decimal('precioCobrado', 10, 2);

            $table->foreign('idSalida')->references('idSalida')->on('Salida')->cascadeOnDelete();
            $table->foreign('codigoProducto')->references('codigo')->on('Producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Detalle_Salida');
    }
};
