<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ingreso', function (Blueprint $table) {
            $table->id('idDetalle');
            $table->unsignedBigInteger('idIngreso');
            $table->string('codigoProducto', 50);
            $table->integer('cantidad');
            $table->decimal('precioCompra', 10, 2);
            $table->date('fechaVencimiento');

            $table->foreign('idIngreso')->references('idIngreso')->on('ingreso')->cascadeOnDelete();
            $table->foreign('codigoProducto')->references('codigo')->on('producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ingreso');
    }
};
