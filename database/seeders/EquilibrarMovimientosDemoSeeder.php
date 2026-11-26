<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Corrige únicamente movimientos ficticios para probar reportes con fechas coherentes. */
class EquilibrarMovimientosDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $futuras = DB::table('salida')->where('fecha', '>', now())->get(['idSalida', 'fecha']);
            $movidas = 0;
            foreach ($futuras as $salida) {
                $codigos = DB::table('detalle_salida')->where('idSalida', $salida->idSalida)->pluck('codigoProducto');
                if ($codigos->isNotEmpty() && $codigos->every(fn ($codigo) => str_starts_with($codigo, 'DEMO-NOV26-'))) {
                    DB::table('salida')->where('idSalida', $salida->idSalida)->update([
                        'fecha' => \Carbon\Carbon::parse($salida->fecha)->subDays(15),
                    ]);
                    $movidas++;
                }
            }
            $productos = Producto::conDetalle()->where('codigo', 'like', 'DEMO-NOV26-%')->orderBy('codigo')->get();
            foreach ($productos as $indice => $producto) {
                $detalle = DB::table('detalle_ingreso as d')->join('ingreso as i', 'i.idIngreso', '=', 'd.idIngreso')
                    ->join('proveedor as p', 'p.idProveedor', '=', 'i.idProveedor')
                    ->where('d.codigoProducto', $producto->codigo)->where('p.correo', 'like', '%@proveedores.example.test')
                    ->whereNull('d.deleted_at')->whereNull('i.deleted_at')->first(['d.idDetalle', 'd.idIngreso', 'd.cantidad', 'd.precioCompra']);
                if (! $detalle) {
                    throw new RuntimeException("Falta ingreso demo para {$producto->codigo}.");
                }
                $objetivo = [3, 7, 12, 20, 25][$indice % 5];
                $diferencia = $objetivo - (int) $producto->stock;
                if ((int) $detalle->cantidad + $diferencia <= 0) {
                    throw new RuntimeException("El ajuste de {$producto->codigo} requiere revisión.");
                }
                DB::table('detalle_ingreso')->where('idDetalle', $detalle->idDetalle)->increment('cantidad', $diferencia);
                DB::table('ingreso')->where('idIngreso', $detalle->idIngreso)->increment('totalCompra', $diferencia * $detalle->precioCompra);
            }
            $this->command?->info("Reprogramadas $movidas salidas ficticias y equilibrados ".$productos->count().' productos demo.');
        });
    }
}
