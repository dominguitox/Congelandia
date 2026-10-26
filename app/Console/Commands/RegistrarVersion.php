<?php

namespace App\Console\Commands;

use App\Models\Version;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegistrarVersion extends Command
{
    protected $signature = 'version:registrar
        {numero : Numero de version (ej. 1.2.0)}
        {--comentarios= : Detalle de los cambios}
        {--inactiva : Registrar sin marcarla como version activa}';

    protected $description = 'Registra una nueva version en la tabla versiones';

    public function handle(): int
    {
        $numero = $this->argument('numero');
        $comentarios = $this->option('comentarios');
        $activa = ! $this->option('inactiva');

        DB::transaction(function () use ($numero, $comentarios, $activa) {
            if ($activa) {
                Version::where('activa', true)->update(['activa' => false]);
            }

            Version::create([
                'numero' => $numero,
                'comentarios' => $comentarios,
                'activa' => $activa,
            ]);
        });

        $this->info("Version {$numero} registrada.");

        return self::SUCCESS;
    }
}
