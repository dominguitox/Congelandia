<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Metodo_Pago', function (Blueprint $table) {
            $table->id('idMetodo');
            $table->string('nombre', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Metodo_Pago');
    }
};
