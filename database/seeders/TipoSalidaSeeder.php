<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoSalidaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Venta', 'Merma por Vencimiento', 'Merma por Daño'] as $nombre) {
            DB::table('tipo_salida')->updateOrInsert(
                ['nombre' => $nombre],
                ['deleted_at' => null],
            );
        }
    }
}
