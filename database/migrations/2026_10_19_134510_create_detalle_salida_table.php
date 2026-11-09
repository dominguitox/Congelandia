<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_salida', function (Blueprint $table) {
            $table->id('idDetalle');
            $table->unsignedBigInteger('idSalida');
            $table->string('codigoProducto', 50);
            $table->integer('cantidad');
            $table->decimal('precioCobrado', 10, 2);

            $table->foreign('idSalida')->references('idSalida')->on('salida')->cascadeOnDelete();
            $table->foreign('codigoProducto')->references('codigo')->on('producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_salida');
    }
};
