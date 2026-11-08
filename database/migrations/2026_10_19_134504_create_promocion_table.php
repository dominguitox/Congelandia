<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion', function (Blueprint $table) {
            $table->id('idPromocion');
            $table->string('codigoProducto', 50);
            $table->decimal('porcentajeDescuento', 5, 2);
            $table->dateTime('fechaInicio');
            $table->dateTime('fechaFin');

            $table->foreign('codigoProducto')->references('codigo')->on('producto')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion');
    }
};
