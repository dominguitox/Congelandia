<?php

namespace Database\Seeders;

use App\Services\Reportes\ReporteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProveedorAdicionalDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $correo = 'alternativo@proveedores.example.test';
            if (DB::table('proveedor')->where('correo', $correo)->exists()) {
                throw new RuntimeException('El proveedor alternativo ya existe.');
            }
            $codigos = app(ReporteService::class)->inventario()['bajoStock']->pluck('codigo');
            $detalle = DB::table('detalle_ingreso as d')->join('ingreso as i', 'i.idIngreso', '=', 'd.idIngreso')
                ->join('proveedor as p', 'p.idProveedor', '=', 'i.idProveedor')
                ->whereIn('d.codigoProducto', $codigos)->where('p.correo', 'like', '%@proveedores.example.test')
                ->whereNull('d.deleted_at')->whereNull('i.deleted_at')->where('d.cantidad', '>', 1)
                ->first(['d.idDetalle', 'd.idIngreso', 'd.codigoProducto', 'd.precioCompra', 'd.fechaVencimiento', 'i.idUsuario']);
            if (! $detalle) {
                throw new RuntimeException('No se encontró un ingreso demo que permita crear el historial alternativo.');
            }
            $proveedor = DB::table('proveedor')->insertGetId([
                'nombre' => 'Proveedor alternativo demo', 'telefono' => '+56955500006', 'correo' => $correo,
            ], 'idProveedor');
            $ingreso = DB::table('ingreso')->insertGetId([
                'fecha' => now(), 'idProveedor' => $proveedor, 'idUsuario' => $detalle->idUsuario, 'totalCompra' => $detalle->precioCompra,
            ], 'idIngreso');
            DB::table('detalle_ingreso')->where('idDetalle', $detalle->idDetalle)->decrement('cantidad');
            DB::table('ingreso')->where('idIngreso', $detalle->idIngreso)->decrement('totalCompra', $detalle->precioCompra);
            DB::table('detalle_ingreso')->insert([
                'idIngreso' => $ingreso, 'codigoProducto' => $detalle->codigoProducto, 'cantidad' => 1,
                'precioCompra' => $detalle->precioCompra, 'fechaVencimiento' => $detalle->fechaVencimiento,
            ]);
            $this->command?->info("Proveedor alternativo asociado a {$detalle->codigoProducto}, sin cambiar el stock.");
        });
    }
}
