<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_errors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('mensaje', 500);
            $table->string('archivo', 500);
            $table->string('linea', 100);
            $table->string('usuario', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_errors');
    }
};
