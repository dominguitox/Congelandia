<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagen_producto', function (Blueprint $table) {
            $table->id();
            $table->text('rutaImagen')->nullable();
            $table->string('codigoProducto', 50);
            $table->text('alt')->nullable();
            $table->text('descripcion')->nullable();

            $table->foreign('codigoProducto')->references('codigo')->on('producto')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagen_producto');
    }
};
