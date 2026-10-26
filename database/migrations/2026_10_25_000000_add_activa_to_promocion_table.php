<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = Schema::hasTable('promocion') ? 'promocion' : 'Promocion';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'activa')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->boolean('activa')->default(true);
        });
    }

    public function down(): void
    {
        $table = Schema::hasTable('promocion') ? 'promocion' : 'Promocion';

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'activa')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('activa');
        });
    }
};
