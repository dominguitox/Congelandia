<?php

namespace App\Http\Controllers;

use App\Services\Reportes\CalendarioReporteService;
use App\Services\Reportes\FeriadosChile;
use App\Services\Reportes\PronosticoStockService;
use App\Services\Reportes\ReporteService;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReporteController extends Controller
{
    public function index(Request $request, ReporteService $reportes)
    {
        $filtros = $request->validate([
            'seccion' => ['sometimes', Rule::in(['ventas', 'inventario', 'habitos', 'calendario'])],
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d'],
            'exportar' => ['sometimes', Rule::in(['csv'])],
            'fecha_calendario' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:2100-12-31'],
            'vista' => ['sometimes', Rule::in(['mes', 'semana'])],
            'dia' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:2100-12-31'],
            'anio' => ['nullable', 'integer', 'between:1900,2100'],
            'patron' => ['bail', 'nullable', 'string', 'regex:/^\d{2}-\d{2}$/', function ($atributo, $valor, $fallar) {
                if (! preg_match('/^\d{2}-\d{2}$/', $valor) || ! checkdate((int) substr($valor, 0, 2), (int) substr($valor, 3, 2), 2000)) {
                    $fallar('Selecciona un día/mes válido.');
                }
            }],
            'pagina_fechas' => ['sometimes', 'integer', 'min:1'],
            'pagina_patrones' => ['sometimes', 'integer', 'min:1'],
            'pagina_detalle' => ['sometimes', 'integer', 'min:1'],
            'pagina_categorias' => ['sometimes', 'integer', 'min:1'],
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

        $calendario = $analisis = $detalleDia = null;
        $dia = $filtros['dia'] ?? null;
        $patron = $filtros['patron'] ?? null;
        $vista = $filtros['vista'] ?? 'mes';
        $fechaCalendario = CarbonImmutable::parse($filtros['fecha_calendario'] ?? $dia ?? now()->toDateString());
        $anio = (int) ($filtros['anio'] ?? ($dia ? CarbonImmutable::parse($dia)->year : now()->year));
        if ($seccion === 'calendario' && ! $request->filled('exportar')) {
            $calendario = app(CalendarioReporteService::class)->periodo($fechaCalendario, $vista, app(FeriadosChile::class), app(PronosticoStockService::class), false);
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

        $categoriasPaginadas = null;
        if ($seccion === 'ventas') {
            $porPagina = 3;
            $total = $datos['categorias']->count();
            $pagina = min((int) ($filtros['pagina_categorias'] ?? 1), max(1, (int) ceil($total / $porPagina)));
            $categoriasPaginadas = new LengthAwarePaginator(
                $datos['categorias']->forPage($pagina, $porPagina)->values(), $total, $porPagina, $pagina,
                ['path' => $request->url(), 'pageName' => 'pagina_categorias']
            );
            $categoriasPaginadas->appends($request->except('pagina_categorias'));
        }

        return view('reportes.index', compact('seccion', 'fechaInicio', 'fechaFin', 'datos', 'categoriasPaginadas', 'calendario', 'analisis', 'detalleDia', 'dia', 'patron', 'vista', 'fechaCalendario', 'anio'));
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
