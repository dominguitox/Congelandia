<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('IMAGEN_PRODUCTO', function (Blueprint $table) {
            $table->id();
            $table->text('rutaImagen')->nullable();
            $table->string('codigoProducto', 50);
            $table->text('alt')->nullable();
            $table->text('descripcion')->nullable();

            $table->foreign('codigoProducto')->references('codigo')->on('Producto')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('IMAGEN_PRODUCTO');
    }
};
