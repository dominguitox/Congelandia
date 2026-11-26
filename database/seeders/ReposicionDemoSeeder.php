<?php

namespace Database\Seeders;

use App\Services\Reportes\ReporteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Datos ficticios locales; ejecutar solo en una base de pruebas. */
class ReposicionDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            if (DB::table('proveedor')->where('correo', 'like', '%@proveedores.example.test')->exists()) {
                throw new RuntimeException('Los proveedores demo ya existen; no se duplicaron los ingresos.');
            }
            $usuario = DB::table('usuario')->where('rol', 'Administrador')->whereNull('deleted_at')->value('idUsuario');
            if (! $usuario) {
                throw new RuntimeException('Se necesita un administrador para registrar los ingresos de prueba.');
            }
            $proveedores = [];
            for ($i = 1; $i <= 5; $i++) {
                $proveedores[] = DB::table('proveedor')->insertGetId([
                    'nombre' => "Proveedor ficticio demo $i",
                    'telefono' => '+5695550000'.$i,
                    'correo' => "proveedor$i@proveedores.example.test",
                ], 'idProveedor');
            }
            $productos = app(ReporteService::class)->inventario()['bajoStock']
                ->filter(fn ($producto) => str_starts_with($producto->codigo, 'DEMO-NOV26-') && $producto->stock < 0)
                ->sortBy('codigo')->values();
            if ($productos->isEmpty()) {
                throw new RuntimeException('No se encontraron productos demo con stock negativo.');
            }
            $ingresos = [];
            foreach ($proveedores as $idProveedor) {
                $ingresos[] = DB::table('ingreso')->insertGetId([
                    'fecha' => now(), 'idProveedor' => $idProveedor, 'idUsuario' => $usuario, 'totalCompra' => 0,
                ], 'idIngreso');
            }
            $totales = array_fill(0, count($ingresos), 0);
            foreach ($productos as $indice => $producto) {
                $grupo = $indice % count($ingresos);
                // Distintos niveles de stock permiten probar prioridades y estados sin alerta.
                $objetivo = [3, 7, 12, 20, 25][$indice % 5];
                $cantidad = $objetivo - $producto->stock;
                $costo = 1000;
                DB::table('detalle_ingreso')->insert([
                    'idIngreso' => $ingresos[$grupo], 'codigoProducto' => $producto->codigo,
                    'cantidad' => $cantidad, 'precioCompra' => $costo, 'fechaVencimiento' => now()->addMonths(6)->toDateString(),
                ]);
                $totales[$grupo] += $cantidad * $costo;
            }
            foreach ($ingresos as $grupo => $idIngreso) {
                DB::table('ingreso')->where('idIngreso', $idIngreso)->update(['totalCompra' => $totales[$grupo]]);
            }
            $this->command?->info("Creados 5 proveedores ficticios y ".$productos->count()." detalles de ingreso demo.");
        });
    }
}
