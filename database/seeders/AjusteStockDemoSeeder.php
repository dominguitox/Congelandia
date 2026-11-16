<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Ajusta solo ingresos ficticios creados por ReposicionDemoSeeder. */
class AjusteStockDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $productos = Producto::withTrashed()->conDetalle()->get()->filter(fn ($producto) => $producto->stock < 0);
            $total = 0;
            foreach ($productos as $producto) {
                $detalle = DB::table('detalle_ingreso as d')
                    ->join('ingreso as i', 'i.idIngreso', '=', 'd.idIngreso')
                    ->join('proveedor as p', 'p.idProveedor', '=', 'i.idProveedor')
                    ->where('d.codigoProducto', $producto->codigo)
                    ->where('p.correo', 'like', '%@proveedores.example.test')
                    ->whereNull('d.deleted_at')->whereNull('i.deleted_at')->first(['d.idDetalle', 'd.idIngreso', 'd.cantidad', 'd.precioCompra']);
                if (! $detalle) {
                    throw new RuntimeException("No existe ingreso demo para {$producto->codigo}; no se aplicaron ajustes.");
                }
                $adicional = 3 - (int) $producto->stock;
                DB::table('detalle_ingreso')->where('idDetalle', $detalle->idDetalle)->increment('cantidad', $adicional);
                DB::table('ingreso')->where('idIngreso', $detalle->idIngreso)->increment('totalCompra', $adicional * $detalle->precioCompra);
                $total++;
            }
            $this->command?->info("Ajustados $total productos demo considerando todos los movimientos.");
        });
    }
}
