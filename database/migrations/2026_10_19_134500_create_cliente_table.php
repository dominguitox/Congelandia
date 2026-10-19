<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Cliente', function (Blueprint $table) {
            $table->string('rutCliente', 15)->primary();
            $table->string('nombre', 100);
            $table->string('telefono', 20)->nullable();
            $table->decimal('saldoDeuda', 10, 2)->default(0.00);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Cliente');
    }
};
