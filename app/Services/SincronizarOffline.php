<?php

namespace App\Services;

use App\Models\Ingreso;
use App\Models\Producto;
use App\Models\RegistroAccion;
use App\Models\Salida;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SincronizarOffline
{
    public function ejecutar(array $operacion, User $usuario): array
    {
        // La firma incluye TODOS los datos originales. No aceptar la misma
        // clave con otro usuario, fecha, stock, precio, cantidad o pago.
        $firma = hash('sha256', json_encode($this->ordenar($operacion), JSON_THROW_ON_ERROR));
        try {
            return DB::transaction(function () use ($operacion, $usuario, $firma) {
                $actual = User::whereKey($usuario->idUsuario)->lockForUpdate()->first();
                abort_unless($actual && $actual->activo, 401, 'El usuario ya no está activo. La cola local se conserva.');
                if ($existente = DB::table('operaciones_offline')->where('id', $operacion['id'])->first()) {
                    return $this->repetir($existente, $firma, $usuario);
                }
                $datos = $this->validar($operacion);
                $lineas = $operacion['tipo'] === 'venta' ? $datos['productos'] : [[
                    'id' => $datos['codigoProducto'], 'cantidad' => $datos['cantidad'], 'stock' => $datos['stock'],
                ]];
                $codigos = array_column($lineas, 'id');
                Producto::whereIn('codigo', $codigos)->orderBy('codigo')->lockForUpdate()->get();
                $actuales = Producto::conDetalle()->whereIn('producto.codigo', $codigos)->get()->keyBy('codigo');
                $conflictos = [];
                foreach ($lineas as $linea) {
                    $actual = $actuales->get($linea['id']);
                    if (! $actual) {
                        $conflictos[] = ['producto' => $linea['id'], 'campo' => 'producto', 'mensaje' => 'El producto ya no está disponible.'];

                        continue;
                    }
                    if ((int) $actual->stock !== (int) $linea['stock'] || ($operacion['tipo'] !== 'ingreso' && $linea['cantidad'] > (int) $actual->stock)) {
                        $conflictos[] = ['producto' => $linea['id'], 'campo' => 'stock', 'local' => $linea['stock'], 'servidor' => (int) $actual->stock];
                    }
                    if ($operacion['tipo'] === 'venta') {
                        $precio = app(CatalogoOffline::class)->producto($actual)['precio'];
                        if ($precio === null || (float) $linea['precio'] !== (float) $precio) {
                            $conflictos[] = ['producto' => $linea['id'], 'campo' => 'precio', 'local' => $linea['precio'], 'servidor' => $precio];
                        }
                    }
                }
                if ($conflictos) {
                    return $this->guardar($operacion, $usuario, $firma, 'conflicto', [
                        'success' => false, 'estado' => 'conflicto', 'id' => $operacion['id'],
                        'message' => 'La operación requiere revisión. Se conservan sus datos originales; no se aplicó ningún movimiento.',
                        'conflictos' => $conflictos,
                    ]);
                }

                if ($operacion['tipo'] === 'venta') {
                    $venta = app(CobroVenta::class)->registrar([
                        ...$datos, 'claveOperacion' => $operacion['id'],
                    ], (int) $usuario->idUsuario)['salida'];
                    $resultado = ['idSalida' => $venta->idSalida, 'vuelto' => (int) $venta->vuelto];
                } elseif ($operacion['tipo'] === 'ingreso') {
                    $ingreso = Ingreso::create([
                        'fecha' => now(), 'idProveedor' => $datos['idProveedor'] ?? null,
                        'idUsuario' => $usuario->idUsuario, 'totalCompra' => $datos['cantidad'] * $datos['precioCompra'],
                    ]);
                    $ingreso->detalles()->create(collect($datos)->only(['codigoProducto', 'cantidad', 'precioCompra', 'fechaVencimiento'])->all());
                    $resultado = ['idIngreso' => $ingreso->idIngreso];
                } else {
                    // Solo los dos motivos actuales. Igual que el flujo online,
                    // recuperar un catálogo eliminado no crea un motivo nuevo.
                    $tipo = DB::table('tipo_salida')->where('nombre', $datos['motivo'])->lockForUpdate()->first();
                    $idTipo = $tipo?->idTipo ?? DB::table('tipo_salida')->insertGetId(['nombre' => $datos['motivo']], 'idTipo');
                    if ($tipo?->deleted_at !== null) {
                        DB::table('tipo_salida')->where('idTipo', $idTipo)->update(['deleted_at' => null]);
                    }
                    $salida = Salida::create([
                        'fecha' => now(), 'idTipo' => $idTipo, 'idUsuario' => $usuario->idUsuario, 'totalSalida' => 0,
                    ]);
                    $salida->detalles()->create(['codigoProducto' => $datos['codigoProducto'], 'cantidad' => $datos['cantidad'], 'precioCobrado' => 0]);
                    $resultado = ['idSalida' => $salida->idSalida];
                }
                RegistroAccion::create([
                    'idUsuario' => $usuario->idUsuario, 'usuarioNombre' => $usuario->nombre,
                    'accion' => 'Operación offline sincronizada', 'modulo' => $operacion['tipo'] === 'venta' ? 'Ventas' : 'Inventario',
                    'ruta' => 'offline.sync', 'referencia' => $operacion['id'], 'fecha' => now(),
                ]);

                return $this->guardar($operacion, $usuario, $firma, 'sincronizada', [
                    'success' => true, 'estado' => 'sincronizada', 'id' => $operacion['id'], ...$resultado,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            // También cubre dos usuarios enviando la misma UUID simultáneamente.
            if ($existente = DB::table('operaciones_offline')->where('id', $operacion['id'])->first()) {
                return $this->repetir($existente, $firma, $usuario);
            }
            throw $exception;
        }
    }

    private function validar(array $operacion): array
    {
        $existente = fn ($tabla, $campo) => Rule::exists($tabla, $campo)->whereNull('deleted_at');
        $reglas = match ($operacion['tipo']) {
            'venta' => [
                'idTipo' => ['required', 'integer', Rule::exists('tipo_salida', 'idTipo')->where('nombre', 'Venta')->whereNull('deleted_at')],
                'rutCliente' => ['nullable', 'string', $existente('cliente', 'rutCliente')],
                'productos' => ['required', 'array', 'min:1', 'max:500'],
                'productos.*.id' => ['required', 'string', 'max:255', 'distinct'],
                'productos.*.cantidad' => ['required', 'integer', 'min:1', 'max:2147483647'],
                'productos.*.precio' => ['required', 'integer', 'min:0', 'max:9999999999'],
                'productos.*.stock' => ['required', 'integer', 'min:0'],
                'pagos' => ['required', 'array', 'min:1', 'max:30'],
                'pagos.*.idMetodo' => ['required', 'integer', 'distinct', $existente('metodo_pago', 'idMetodo')],
                'pagos.*.monto' => ['required', 'integer', 'min:0', 'max:9999999999'],
                'efectivoRecibido' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            ],
            'ingreso' => [
                'codigoProducto' => ['required', 'string', 'max:255'],
                'cantidad' => ['required', 'integer', 'min:1', 'max:2147483647'],
                'stock' => ['required', 'integer'],
                'idProveedor' => ['nullable', 'integer', $existente('proveedor', 'idProveedor')],
                'precioCompra' => ['required', 'numeric', 'min:0', 'max:9999999999'],
                // Un ingreso guardado ayer sigue siendo válido al sincronizar:
                // el vencimiento se compara con su fecha local original.
                'fechaVencimiento' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::parse($operacion['fechaLocal'])->toDateString()],
            ],
            'merma' => [
                'codigoProducto' => ['required', 'string', 'max:255'],
                'cantidad' => ['required', 'integer', 'min:1', 'max:2147483647'],
                'stock' => ['required', 'integer', 'min:0'],
                'motivo' => ['required', Rule::in(['Merma por Vencimiento', 'Merma por Daño'])],
            ],
        };
        $datos = Validator::make($operacion['datos'], $reglas)->validate();
        if ($operacion['tipo'] === 'ingreso') {
            Validator::make(['totalCompra' => $datos['cantidad'] * $datos['precioCompra']], ['totalCompra' => 'numeric|max:9999999999'])->validate();
        }

        return $datos;
    }

    private function guardar(array $operacion, User $usuario, string $firma, string $estado, array $resultado): array
    {
        DB::table('operaciones_offline')->insert([
            'id' => $operacion['id'], 'idUsuario' => $usuario->idUsuario, 'firma' => $firma,
            'tipo' => $operacion['tipo'], 'fechaLocal' => Carbon::parse($operacion['fechaLocal'])->utc(),
            'datos' => json_encode($operacion, JSON_THROW_ON_ERROR), 'estado' => $estado,
            'resultado' => json_encode($resultado, JSON_THROW_ON_ERROR), 'created_at' => now(),
        ]);

        return $resultado;
    }

    private function repetir(object $existente, string $firma, User $usuario): array
    {
        abort_unless((int) $existente->idUsuario === (int) $usuario->idUsuario && hash_equals($existente->firma, $firma), 409, 'La clave ya pertenece a otra operación. Conserva los datos y revisa el historial.');

        return [...json_decode($existente->resultado, true, flags: JSON_THROW_ON_ERROR), 'repetida' => true];
    }

    private function ordenar(array $datos): array
    {
        if (! array_is_list($datos)) {
            ksort($datos);
        }

        return array_map(fn ($dato) => is_array($dato) ? $this->ordenar($dato) : $dato, $datos);
    }
}
