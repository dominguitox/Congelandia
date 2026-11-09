<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingreso', function (Blueprint $table) {
            $table->id('idIngreso');
            $table->dateTime('fecha');
            $table->unsignedBigInteger('idProveedor');
            $table->unsignedBigInteger('idUsuario');
            $table->decimal('totalCompra', 12, 2);

            $table->foreign('idProveedor')->references('idProveedor')->on('proveedor');
            $table->foreign('idUsuario')->references('idUsuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingreso');
    }
};
