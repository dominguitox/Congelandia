<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $names = Schema::getTableListing(schemaQualified: false);
        $legacy = ['Usuario', 'Cliente', 'Categoria', 'Producto', 'Lista_Precio',
            'Promocion', 'Proveedor', 'Ingreso', 'Detalle_Ingreso', 'Tipo_Salida',
            'Salida', 'Detalle_Salida', 'Metodo_Pago', 'Pago_Salida',
            'Tipo_Devolucion', 'Devolucion', 'IMAGEN_PRODUCTO', 'Versiones'];

        // Validar todo antes del primer DDL: nunca fusionar ni reemplazar tablas.
        foreach ($legacy as $old) {
            $new = strtolower($old);
            if (in_array($old, $names, true) &&
                (in_array($new, $names, true) || in_array('_normalizando_'.$new, $names, true))) {
                throw new RuntimeException("Conflicto al normalizar {$old}. Revisar las tablas antes de migrar.");
            }
        }

        foreach ($legacy as $old) {
            if (! in_array($old, $names, true)) {
                continue;
            }
            $new = strtolower($old);
            // El paso intermedio permite renombrar también en motores insensibles al casing.
            Schema::rename($old, '_normalizando_'.$new);
            Schema::rename('_normalizando_'.$new, $new);
        }
    }

    public function down(): void
    {
        // Las migraciones históricas y la aplicación ya usan el esquema canónico.
        // Un rollback de código requiere restaurar el respaldo del esquema anterior.
        throw new RuntimeException('La normalización de tablas no se revierte automáticamente. Restaurar el respaldo previo.');
    }
};
