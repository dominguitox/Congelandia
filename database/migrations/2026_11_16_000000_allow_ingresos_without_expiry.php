<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_ingreso', function (Blueprint $table) {
            $table->date('fechaVencimiento')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Los ingresos sin fecha no pueden volver a la restricción anterior sin asignar fechas ficticias.
        throw new RuntimeException('No se puede revertir automáticamente sin completar las fechas ausentes.');
    }
};
