<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Versiones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20);
            $table->text('comentarios')->nullable();
            $table->boolean('activa')->default(false);
            $table->timestamp('fecha_creacion')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Versiones');
    }
};
