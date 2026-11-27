<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folio_venta_dia', function (Blueprint $table) {
            $table->date('fecha')->primary();
            $table->unsignedInteger('ultimo')->default(0);
        });

        Schema::table('salida', function (Blueprint $table) {
            $table->string('folio', 20)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('salida', function (Blueprint $table) {
            $table->dropUnique(['folio']);
            $table->dropColumn('folio');
        });
        Schema::dropIfExists('folio_venta_dia');
    }
};
