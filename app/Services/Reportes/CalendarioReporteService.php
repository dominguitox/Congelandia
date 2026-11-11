<?php

namespace App\Services\Reportes;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CalendarioReporteService extends ReporteService
{
    public function periodo(CarbonImmutable $fecha, string $vista, FeriadosChile $feriados, PronosticoStockService $stock): array
    {
        $inicio = $vista === 'semana' ? $fecha->startOfWeek(CarbonImmutable::MONDAY) : $fecha->startOfMonth();
        $fin = $vista === 'semana' ? $inicio->addDays(6) : $fecha->endOfMonth();
        $desde = $inicio->startOfWeek(CarbonImmutable::MONDAY);
        $hasta = $fin->endOfWeek(CarbonImmutable::SUNDAY);
        $ventas = $this->ventasBase($desde, $hasta)->selectRaw('DATE(s.fecha) as fecha, SUM(d.cantidad * d.precioCobrado) as ingresos, SUM(d.cantidad) as unidades, COUNT(DISTINCT s.idSalida) as ventas')
            ->groupByRaw('DATE(s.fecha)')->get()->keyBy('fecha');
        $ofertas = DB::table($this->tabla('Promocion').' as o')->join($this->tabla('Producto').' as p', 'p.codigo', '=', 'o.codigoProducto')
            ->whereNull('o.deleted_at')->where('o.porcentajeDescuento', '>', 0)
            ->where('o.fechaInicio', '<=', $hasta->endOfDay())->where('o.fechaFin', '>=', $desde->startOfDay())
            ->select('o.*', 'p.nombre', 'p.deleted_at as productoEliminado')->orderBy('o.fechaInicio')->get()->map(function ($oferta) {
                // Ángulo áureo: separa identificadores consecutivos sin depender del orden o período consultado.
                $oferta->tono = number_format(fmod((int) $oferta->idPromocion * 137.50776405003785, 360), 6, '.', '');
                $oferta->estado = match (true) {
                    ! $oferta->activa => 'Pausada (no válida)',
                    $oferta->productoEliminado !== null => 'Producto retirado (no válida)',
                    CarbonImmutable::parse($oferta->fechaFin)->isPast() => 'Vencida',
                    CarbonImmutable::parse($oferta->fechaInicio)->isFuture() => 'Programada',
                    default => 'Vigente',
                };

                return $oferta;
            });
        $festivos = collect($feriados->entre($desde, $hasta))->groupBy('fecha');
        $pronostico = $stock->estimar();
        $avisos = $pronostico['avisos']->groupBy('fecha');
        $dias = [];
        for ($dia = $desde; $dia->lessThanOrEqualTo($hasta); $dia = $dia->addDay()) {
            $clave = $dia->toDateString();
            $dias[] = [
                'fecha' => $dia, 'adyacente' => $dia->lessThan($inicio) || $dia->greaterThan($fin),
                'ventas' => $ventas->get($clave), 'feriados' => $festivos->get($clave, collect()),
                'ofertas' => $ofertas->filter(fn ($o) => substr($o->fechaInicio, 0, 10) <= $clave && substr($o->fechaFin, 0, 10) >= $clave)
                    ->map(function ($oferta) use ($clave) {
                        $evento = clone $oferta;
                        $comienza = substr($oferta->fechaInicio, 0, 10) === $clave;
                        $termina = substr($oferta->fechaFin, 0, 10) === $clave;
                        $evento->tramo = match (true) {
                            $comienza && $termina => 'Comienza y termina hoy',
                            $comienza => 'Comienza hoy',
                            $termina => 'Termina hoy',
                            default => 'Continúa',
                        };

                        return $evento;
                    }),
                'avisos' => $avisos->get($clave, collect()),
            ];
        }
        $sinCobertura = [];
        for ($anio = $desde->year; $anio <= $hasta->year; $anio++) {
            if (! in_array($anio, $feriados->anios(), true)) {
                $sinCobertura[] = $anio;
            }
        }

        return compact('inicio', 'fin', 'desde', 'hasta', 'dias', 'pronostico', 'sinCobertura');
    }
}
