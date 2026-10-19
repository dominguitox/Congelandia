<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Producto', function (Blueprint $table) {
            $table->string('codigo', 50)->primary();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('idCategoria')->nullable();
            $table->softDeletes();

            $table->foreign('idCategoria')->references('idCategoria')->on('Categoria')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Producto');
    }
};
