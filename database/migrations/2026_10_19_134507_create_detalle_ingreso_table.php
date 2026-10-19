<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Detalle_Ingreso', function (Blueprint $table) {
            $table->id('idDetalle');
            $table->unsignedBigInteger('idIngreso');
            $table->string('codigoProducto', 50);
            $table->integer('cantidad');
            $table->decimal('precioCompra', 10, 2);
            $table->date('fechaVencimiento');

            $table->foreign('idIngreso')->references('idIngreso')->on('Ingreso')->cascadeOnDelete();
            $table->foreign('codigoProducto')->references('codigo')->on('Producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Detalle_Ingreso');
    }
};
