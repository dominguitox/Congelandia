<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lista_precio', function (Blueprint $table) {
            $table->id('idPrecio');
            $table->string('codigoProducto', 50);
            $table->decimal('precioVenta', 10, 2);
            $table->dateTime('fechaInicio');
            $table->dateTime('fechaFin')->nullable();

            $table->foreign('codigoProducto')->references('codigo')->on('producto')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_precio');
    }
};
