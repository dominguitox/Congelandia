<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas de negocio que deben soportar eliminación lógica.
     * Se aceptan variantes de nombre por la diferencia entre el esquema
     * creado por migraciones y el creado desde los dumps SQL.
     */
    private array $tables = [
        ['salida', 'Salida'],
        ['ingreso', 'Ingreso'],
        ['detalle_ingreso', 'Detalle_Ingreso'],
        ['detalle_salida', 'Detalle_Salida'],
        ['lista_precio', 'Lista_Precio'],
        ['promocion', 'Promocion'],
        ['imagen_producto', 'IMAGEN_PRODUCTO'],
        ['pago_salida', 'Pago_Salida'],
        ['tipo_salida', 'Tipo_Salida'],
        ['metodo_pago', 'Metodo_Pago'],
        ['tipo_devolucion', 'Tipo_Devolucion'],
        ['devolucion', 'Devolucion'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $candidates) {
            $resolved = $this->resolveTable($candidates);

            if ($resolved === null || Schema::hasColumn($resolved, 'deleted_at')) {
                continue;
            }

            Schema::table($resolved, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $candidates) {
            $resolved = $this->resolveTable($candidates);

            if ($resolved === null || !Schema::hasColumn($resolved, 'deleted_at')) {
                continue;
            }

            Schema::table($resolved, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }

    private function resolveTable(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (Schema::hasTable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
};
