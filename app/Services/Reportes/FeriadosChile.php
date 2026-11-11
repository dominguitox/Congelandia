<?php

namespace App\Services\Reportes;

use Carbon\CarbonImmutable;

class FeriadosChile
{
    public function anios(): array
    {
        return array_keys(config('feriados.solsticios'));
    }

    public function entre(CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        $eventos = [];
        for ($anio = $desde->year; $anio <= $hasta->year; $anio++) {
            if (! in_array($anio, $this->anios(), true)) {
                continue;
            }
            $fechas = [];
            $agregar = function (CarbonImmutable $fecha, string $nombre) use (&$fechas) {
                $fechas[] = ['fecha' => $fecha->toDateString(), 'nombre' => $nombre];
            };
            foreach ([
                '01-01' => 'Año Nuevo', '05-01' => 'Día del Trabajo', '05-21' => 'Glorias Navales',
                '07-16' => 'Virgen del Carmen', '08-15' => 'Asunción de la Virgen',
                '09-18' => 'Independencia Nacional', '09-19' => 'Glorias del Ejército',
                '11-01' => 'Todos los Santos', '12-08' => 'Inmaculada Concepción', '12-25' => 'Navidad',
            ] as $dia => $nombre) {
                $agregar(CarbonImmutable::parse("$anio-$dia"), $nombre);
            }
            // Computus gregoriano, independiente de la extensión calendar y de la zona del SO.
            $a = $anio % 19;
            $b = intdiv($anio, 100);
            $c = $anio % 100;
            $d = intdiv($b, 4);
            $e = $b % 4;
            $f = intdiv($b + 8, 25);
            $g = intdiv($b - $f + 1, 3);
            $h = (19 * $a + $b - $d - $g + 15) % 30;
            $i = intdiv($c, 4);
            $k = $c % 4;
            $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
            $m = intdiv($a + 11 * $h + 22 * $l, 451);
            $pascua = CarbonImmutable::create($anio, intdiv($h + $l - 7 * $m + 114, 31), ($h + $l - 7 * $m + 114) % 31 + 1)->startOfDay();
            $agregar($pascua->subDays(2), 'Viernes Santo');
            $agregar($pascua->subDay(), 'Sábado Santo');
            $agregar(CarbonImmutable::parse($anio.'-'.config("feriados.solsticios.$anio")), 'Día Nacional de los Pueblos Indígenas');
            foreach (['06-29' => 'San Pedro y San Pablo', '10-12' => 'Encuentro de Dos Mundos'] as $dia => $nombre) {
                $fecha = CarbonImmutable::parse("$anio-$dia");
                if (in_array($fecha->dayOfWeekIso, [2, 3, 4], true)) {
                    $fecha = $fecha->startOfWeek();
                } elseif ($fecha->dayOfWeekIso === 5) {
                    $fecha = $fecha->addDays(3);
                }
                $agregar($fecha, $nombre);
            }
            $evangelico = CarbonImmutable::parse("$anio-10-31");
            $agregar(match ($evangelico->dayOfWeekIso) {
                2 => $evangelico->subDays(4), 3 => $evangelico->addDays(2), default => $evangelico,
            }, 'Día de las Iglesias Evangélicas y Protestantes');
            $septiembre = CarbonImmutable::parse("$anio-09-18");
            if (in_array($septiembre->dayOfWeekIso, [2, 6], true)) {
                $agregar($septiembre->subDay(), 'Feriado adicional de Fiestas Patrias');
            } elseif ($septiembre->dayOfWeekIso === 3) {
                $agregar($septiembre->addDays(2), 'Feriado adicional de Fiestas Patrias');
            }
            $enero = CarbonImmutable::parse("$anio-01-01");
            if ($enero->isSunday()) {
                $agregar($enero->addDay(), 'Feriado adicional de Año Nuevo');
            }
            foreach (config('feriados.extraordinarios') as $fecha => $nombre) {
                if (str_starts_with($fecha, (string) $anio)) {
                    $agregar(CarbonImmutable::parse($fecha), $nombre);
                }
            }
            foreach ($fechas as $fecha) {
                if ($fecha['fecha'] >= $desde->toDateString() && $fecha['fecha'] <= $hasta->toDateString()) {
                    $eventos[] = $fecha;
                }
            }
        }

        return $eventos;
    }
}
