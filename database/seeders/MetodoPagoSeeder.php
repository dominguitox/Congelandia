<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        // Mantener el orden histórico: efectivo primero y débito segundo.
        foreach (['Efectivo', 'Débito', 'Crédito'] as $nombre) {
            DB::table('metodo_pago')->updateOrInsert(
                ['nombre' => $nombre],
                ['deleted_at' => null],
            );
        }
    }
}
