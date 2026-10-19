<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Pago_Salida', function (Blueprint $table) {
            $table->id('idPago');
            $table->unsignedBigInteger('idSalida');
            $table->unsignedBigInteger('idMetodo');
            $table->decimal('montoPagado', 12, 2);
            $table->date('fechaPago');

            $table->foreign('idSalida')->references('idSalida')->on('Salida')->cascadeOnDelete();
            $table->foreign('idMetodo')->references('idMetodo')->on('Metodo_Pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Pago_Salida');
    }
};
