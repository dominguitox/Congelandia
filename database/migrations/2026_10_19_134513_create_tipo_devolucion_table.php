<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Tipo_Devolucion', function (Blueprint $table) {
            $table->id('idTipo');
            $table->string('nombre', 100);
            $table->boolean('reintegraStock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Tipo_Devolucion');
    }
};
