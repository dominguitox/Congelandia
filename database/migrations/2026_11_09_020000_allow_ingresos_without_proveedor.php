<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingreso', function (Blueprint $table) {
            $table->unsignedBigInteger('idProveedor')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('ingreso')->whereNull('idProveedor')->exists()) {
            throw new RuntimeException('No se puede revertir: existen ingresos sin proveedor. Asígnales un proveedor antes de revertir.');
        }

        Schema::table('ingreso', function (Blueprint $table) {
            $table->unsignedBigInteger('idProveedor')->nullable(false)->change();
        });
    }
};
