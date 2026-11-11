<?php

namespace App\Http\Controllers;

use App\Services\Reportes\AnalisisFechasService;
use App\Services\Reportes\CalendarioReporteService;
use App\Services\Reportes\FeriadosChile;
use App\Services\Reportes\PronosticoStockService;
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
            'seccion' => ['sometimes', Rule::in(['ventas', 'inventario', 'habitos', 'calendario', 'fechas'])],
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

        $calendario = $analisis = $detalleDia = null;
        $dia = $filtros['dia'] ?? null;
        $patron = $filtros['patron'] ?? null;
        $vista = $filtros['vista'] ?? 'mes';
        $fechaCalendario = CarbonImmutable::parse($filtros['fecha_calendario'] ?? $dia ?? now()->toDateString());
        $anio = (int) ($filtros['anio'] ?? ($dia ? CarbonImmutable::parse($dia)->year : now()->year));
        if (in_array($seccion, ['calendario', 'fechas'], true) && ! $request->filled('exportar')) {
            $fechas = app(AnalisisFechasService::class);
            $analisis = $fechas->explorar($anio, $patron, $dia);
            if ($dia && ! $patron) {
                $detalleDia = $fechas->detalleDia(CarbonImmutable::parse($dia));
            }
            if ($seccion === 'calendario') {
                $calendario = app(CalendarioReporteService::class)->periodo($fechaCalendario, $vista, app(FeriadosChile::class), app(PronosticoStockService::class));
            }
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

        return view('reportes.index', compact('seccion', 'fechaInicio', 'fechaFin', 'datos', 'calendario', 'analisis', 'detalleDia', 'dia', 'patron', 'vista', 'fechaCalendario', 'anio'));
    }
}
