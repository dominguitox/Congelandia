<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salida', function (Blueprint $table) {
            $table->uuid('claveOperacion')->nullable()->unique();
            $table->char('firmaCobro', 64)->nullable();
            $table->decimal('efectivoRecibido', 12, 2)->nullable();
            $table->decimal('vuelto', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('salida', function (Blueprint $table) {
            $table->dropUnique(['claveOperacion']);
            $table->dropColumn(['claveOperacion', 'firmaCobro', 'efectivoRecibido', 'vuelto']);
        });
    }
};
