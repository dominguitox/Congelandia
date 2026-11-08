<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_salida', function (Blueprint $table) {
            $table->id('idPago');
            $table->unsignedBigInteger('idSalida');
            $table->unsignedBigInteger('idMetodo');
            $table->decimal('montoPagado', 12, 2);
            $table->date('fechaPago');

            $table->foreign('idSalida')->references('idSalida')->on('salida')->cascadeOnDelete();
            $table->foreign('idMetodo')->references('idMetodo')->on('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_salida');
    }
};
