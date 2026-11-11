<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CatalogoOffline
{
    public function producto(Producto $producto): array
    {
        $oferta = Promocion::vigentes()->where('codigoProducto', $producto->codigo)
            ->orderByDesc('fechaInicio')->first();
        $normal = $producto->precio === null ? null : (float) $producto->precio;

        return [
            'codigo' => $producto->codigo, 'nombre' => $producto->nombre,
            'stock' => (int) $producto->stock, 'precioNormal' => $normal,
            'precio' => $normal === null ? null : (int) round($normal * (1 - (float) ($oferta?->porcentajeDescuento ?? 0) / 100)),
            'descuento' => (float) ($oferta?->porcentajeDescuento ?? 0),
            'ofertaHasta' => $oferta?->fechaFin?->toIso8601String(),
        ];
    }

    public function obtener(User $usuario): array
    {
        // Bloquear las mismas filas que usan ingresos, mermas y cobros evita una
        // copia de stock partida a la mitad de una operación en esos módulos.
        return DB::transaction(function () use ($usuario) {
            Producto::orderBy('codigo')->lockForUpdate()->get(['codigo']);
            $actualizado = now()->toIso8601String();
            $productos = Producto::conDetalle()->orderBy('producto.codigo')->get()
                ->map(fn ($producto) => [...$this->producto($producto), 'actualizado' => $actualizado]);

            return [
                'version' => 1, 'actualizado' => $actualizado,
                'usuario' => ['id' => (int) $usuario->idUsuario, 'nombre' => $usuario->nombre],
                'productos' => $productos,
                'clientes' => Cliente::orderBy('nombre')->get(['rutCliente', 'nombre']),
                'proveedores' => Proveedor::orderBy('nombre')->get(['idProveedor', 'nombre']),
                'metodos' => DB::table('metodo_pago')->whereNull('deleted_at')
                    ->whereRaw('LOWER(nombre) NOT LIKE ?', ['%fiado%'])->orderBy('idMetodo')->get(['idMetodo', 'nombre']),
                'tipoVenta' => DB::table('tipo_salida')->where('nombre', 'Venta')->whereNull('deleted_at')->value('idTipo'),
                'motivos' => ['Merma por Vencimiento', 'Merma por Daño'],
            ];
        });
    }
}
