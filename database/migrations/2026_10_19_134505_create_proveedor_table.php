<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Proveedor', function (Blueprint $table) {
            $table->id('idProveedor');
            $table->string('nombre', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 100)->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Proveedor');
    }
};
