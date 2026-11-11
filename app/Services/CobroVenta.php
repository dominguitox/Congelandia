<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Salida;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CobroVenta
{
    public function registrar(array $datos, int $usuarioId): array
    {
        $clave = $datos['claveOperacion'] ?? null;
        $firma = hash('sha256', json_encode([
            'usuario' => $usuarioId,
            'tipo' => (int) $datos['idTipo'],
            'cliente' => $datos['rutCliente'] ?? null,
            'pagos' => isset($datos['pagos']) ? array_map(fn ($p) => [(int) $p['idMetodo'], (int) $p['monto']], $datos['pagos']) : (int) $datos['metodoPago'],
            'recibido' => isset($datos['efectivoRecibido']) ? (int) $datos['efectivoRecibido'] : null,
            'productos' => array_map(fn ($p) => [(string) $p['id'], (int) $p['cantidad'], (float) $p['precio']], $datos['productos']),
        ], JSON_THROW_ON_ERROR));

        if ($clave && $existente = Salida::withTrashed()->where('claveOperacion', $clave)->first()) {
            return $this->repetir($existente, $firma, $usuarioId);
        }

        try {
            $salida = DB::transaction(function () use ($datos, $usuarioId, $clave, $firma) {
                $lineas = [];
                $total = 0;
                foreach ($datos['productos'] as $linea) {
                    $producto = Producto::where('codigo', $linea['id'])->lockForUpdate()->first();
                    if (! $producto) {
                        throw ValidationException::withMessages(['productos' => 'Uno de los productos ya no está disponible. Recarga el POS.']);
                    }
                    $precioNormal = DB::table('lista_precio')->where('codigoProducto', $producto->codigo)
                        ->where('fechaInicio', '<=', now())->whereNull('fechaFin')->whereNull('deleted_at')
                        ->orderByDesc('fechaInicio')->orderByDesc('idPrecio')->value('precioVenta');
                    if ($precioNormal === null) {
                        throw ValidationException::withMessages(['productos' => "El producto {$producto->nombre} no tiene un precio vigente."]);
                    }
                    $oferta = Promocion::vigentes()->where('codigoProducto', $producto->codigo)->orderByDesc('fechaInicio')->first();
                    $precio = (int) round((float) $precioNormal * (1 - (float) ($oferta?->porcentajeDescuento ?? 0) / 100));
                    if (round((float) $linea['precio'], 2) !== (float) $precio) {
                        throw new HttpResponseException(response()->json(['success' => false, 'error' => "El precio de {$producto->nombre} cambió. Actualiza el POS y revisa el carrito antes de cobrar."], 409));
                    }
                    $lineas[] = ['codigoProducto' => $producto->codigo, 'cantidad' => (int) $linea['cantidad'], 'precioCobrado' => $precio];
                    $total += $precio * (int) $linea['cantidad'];
                }
                if ($total > 9999999999) {
                    throw ValidationException::withMessages(['productos' => 'El total de la venta supera el importe permitido.']);
                }

                $pagos = $datos['pagos'] ?? [['idMetodo' => $datos['metodoPago'], 'monto' => $total]];
                $pagos = array_map(fn ($pago) => ['idMetodo' => (int) $pago['idMetodo'], 'monto' => (int) $pago['monto']], $pagos);
                if (array_sum(array_column($pagos, 'monto')) !== $total) {
                    throw ValidationException::withMessages(['pagos' => 'Los importes aplicados a los métodos deben sumar el total de la venta.']);
                }
                $efectivo = 0;
                foreach ($pagos as $pago) {
                    if ($total > 0 && (int) $pago['monto'] === 0) {
                        throw ValidationException::withMessages(['pagos' => 'Cada método debe tener un importe mayor que cero.']);
                    }
                    $nombre = DB::table('metodo_pago')->where('idMetodo', $pago['idMetodo'])->whereNull('deleted_at')->value('nombre');
                    if ($nombre === null) {
                        throw ValidationException::withMessages(['pagos' => 'Uno de los métodos de pago ya no está disponible.']);
                    }
                    if (str_contains(mb_strtolower($nombre), 'fiado')) {
                        throw ValidationException::withMessages(['pagos' => 'El POS solo admite pagos completos; este método no está permitido.']);
                    }
                    if (mb_strtolower(trim($nombre)) === 'efectivo') {
                        $efectivo += (int) $pago['monto'];
                    }
                }
                $recibido = isset($datos['efectivoRecibido']) ? (int) $datos['efectivoRecibido'] : (isset($datos['pagos']) ? 0 : $efectivo);
                if ($recibido < $efectivo || ($efectivo === 0 && $recibido > 0)) {
                    throw ValidationException::withMessages(['efectivoRecibido' => $efectivo > 0 ? 'El efectivo recibido no alcanza para cubrir la parte en efectivo.' : 'Solo se puede registrar efectivo recibido cuando se usa ese método.']);
                }

                $salida = Salida::create([
                    'fecha' => now(), 'idTipo' => $datos['idTipo'], 'idUsuario' => $usuarioId,
                    'rutCliente' => $datos['rutCliente'] ?? null, 'totalSalida' => $total,
                    'claveOperacion' => $clave, 'firmaCobro' => $clave ? $firma : null,
                    'efectivoRecibido' => $efectivo > 0 ? $recibido : null, 'vuelto' => $recibido - $efectivo,
                ]);
                $salida->detalles()->createMany($lineas);
                foreach ($pagos as $pago) {
                    $salida->pagos()->create(['idMetodo' => $pago['idMetodo'], 'montoPagado' => $pago['monto'], 'fechaPago' => now()->toDateString()]);
                }

                return $salida;
            }, 3);
        } catch (QueryException $exception) {
            // La restricción única también cubre peticiones simultáneas.
            if ($clave && $existente = Salida::withTrashed()->where('claveOperacion', $clave)->first()) {
                return $this->repetir($existente, $firma, $usuarioId);
            }
            throw $exception;
        }

        return ['salida' => $salida, 'repetida' => false];
    }

    private function repetir(Salida $salida, string $firma, int $usuarioId): array
    {
        if ((int) $salida->idUsuario !== $usuarioId || ! hash_equals((string) $salida->firmaCobro, $firma) || $salida->trashed()) {
            throw new HttpResponseException(response()->json(['success' => false, 'codigo' => 'operacion_utilizada', 'error' => 'La operación ya fue utilizada para otro cobro. Revisa el historial antes de continuar.'], 409));
        }

        return ['salida' => $salida, 'repetida' => true];
    }
}
