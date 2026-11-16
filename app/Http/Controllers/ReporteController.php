<?php

namespace App\Http\Controllers;

use App\Services\Reportes\ReporteService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReporteController extends Controller
{
    public function index(Request $request, ReporteService $reportes)
    {
        $filtros = $request->validate([
            'seccion' => ['sometimes', Rule::in(['ventas', 'inventario', 'habitos'])],
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d'],
            'exportar' => ['sometimes', Rule::in(['csv'])],
        ]);
        $seccion = $filtros['seccion'] ?? 'ventas';
        $fechaInicio = $filtros['fecha_inicio'] ?? now()->subDays(29)->toDateString();
        $fechaFin = $filtros['fecha_fin'] ?? now()->toDateString();
        $desde = CarbonImmutable::parse($fechaInicio);
        $hasta = CarbonImmutable::parse($fechaFin);

        if ($hasta->lessThan($desde) || $desde->diffInDays($hasta) > 365) {
            throw ValidationException::withMessages([
                'fecha_fin' => 'Selecciona un período ordenado de hasta 366 días.',
            ]);
        }

        $datos = $seccion === 'inventario' ? $reportes->inventario() : $reportes->ventas($desde, $hasta);

        if ($seccion === 'inventario' && $request->input('exportar') !== 'csv') {
            $datos['proveedoresPorProducto'] = $reportes->proveedoresPorProducto();
        }

        if ($request->input('exportar') === 'csv') {
            return response()->streamDownload(function () use ($reportes, $seccion, $datos) {
                $archivo = fopen('php://output', 'w');
                fwrite($archivo, "\xEF\xBB\xBF");
                foreach ($reportes->filasCsv($seccion, $datos) as $fila) {
                    $fila = array_map(fn ($valor) => is_string($valor) && preg_match('/^[\s]*[=+@-]/u', $valor)
                        ? "'".$valor : $valor, $fila);
                    fputcsv($archivo, $fila, ';', '"', '');
                }
                fclose($archivo);
            }, "reportes-{$seccion}-{$fechaInicio}-{$fechaFin}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('reportes.index', compact('seccion', 'fechaInicio', 'fechaFin', 'datos'));
    }

    public function prepararSolicitud(Request $request, ReporteService $reportes)
    {
        $entrada = $request->validate([
            'productos' => ['required', 'array', 'min:1', 'max:500'],
            'productos.*.codigo' => ['required', 'string', 'max:50', 'distinct'],
            'productos.*.proveedor' => ['nullable', 'integer', 'min:1'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);
        $disponibles = $reportes->inventario()['bajoStock']->keyBy('codigo');
        $proveedores = $reportes->proveedoresPorProducto();
        $solicitudes = collect();
        foreach ($entrada['productos'] as $linea) {
            if (empty($linea['proveedor'])) {
                continue;
            }
            $producto = $disponibles->get($linea['codigo']);
            $proveedor = $proveedores->get($linea['codigo'], collect())->firstWhere('idProveedor', $linea['proveedor']);
            if (! $producto || ! $proveedor) {
                throw ValidationException::withMessages(['productos' => 'Un producto o proveedor ya no es elegible. Revisa la selección.']);
            }
            if (! $solicitudes->has($proveedor->idProveedor)) {
                $solicitudes->put($proveedor->idProveedor, ['proveedor' => $proveedor, 'lineas' => []]);
            }
            $grupo = $solicitudes->get($proveedor->idProveedor);
            $grupo['lineas'][] = ['producto' => $producto, 'cantidad' => $linea['cantidad']];
            $solicitudes->put($proveedor->idProveedor, $grupo);
        }
        if ($solicitudes->isEmpty()) {
            throw ValidationException::withMessages(['productos' => 'Selecciona al menos un proveedor para preparar una solicitud.']);
        }

        return view('reportes.solicitudes', compact('solicitudes'));
    }
}
