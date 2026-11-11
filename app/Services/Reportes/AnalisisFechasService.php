<?php

namespace App\Services\Reportes;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalisisFechasService extends ReporteService
{
    private function historial(): Builder
    {
        // El rango amplio solo se agrega en SQL; nunca se descargan los tickets del historial.
        return $this->ventasBase(CarbonImmutable::parse('1000-01-01'), CarbonImmutable::parse('9999-12-31'));
    }

    private function resumen(Builder $base, string $expresion): Builder
    {
        return $base->selectRaw("$expresion as fecha, SUM(d.cantidad * d.precioCobrado) as ingresos, SUM(d.cantidad) as unidades, COUNT(DISTINCT s.idSalida) as ventas")
            ->groupByRaw($expresion);
    }

    public function explorar(int $anio, ?string $patron, ?string $dia): array
    {
        $exactas = $this->resumen($this->ventasBase(CarbonImmutable::create($anio, 1, 1), CarbonImmutable::create($anio, 12, 31)), 'DATE(s.fecha)')
            ->orderByDesc('ingresos')->orderBy('fecha')->paginate(20, ['*'], 'pagina_fechas')->withQueryString();
        $recurrentes = $this->resumen($this->historial(), 'SUBSTR(s.fecha, 6, 5)')
            ->selectRaw('COUNT(DISTINCT SUBSTR(s.fecha, 1, 4)) as ocurrencias')
            ->orderByDesc('ingresos')->orderBy('fecha')->paginate(20, ['*'], 'pagina_patrones')->withQueryString();
        $anios = $this->historial()->selectRaw('SUBSTR(s.fecha, 1, 4) as anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $seleccion = $this->historial();
        if ($patron) {
            $seleccion->whereRaw('SUBSTR(s.fecha, 6, 5) = ?', [$patron]);
        } elseif ($dia) {
            $seleccion->whereBetween('s.fecha', [CarbonImmutable::parse($dia)->startOfDay(), CarbonImmutable::parse($dia)->endOfDay()]);
        } else {
            $seleccion->whereRaw('1 = 0');
        }
        $ocurrencias = $this->resumen(clone $seleccion, 'DATE(s.fecha)')->orderByDesc('fecha')->get();
        $rankings = $this->rankings($seleccion);

        return compact('exactas', 'recurrentes', 'anios', 'ocurrencias', 'rankings');
    }

    private function rankings(Builder $seleccion): array
    {
        $ventas = $seleccion->join($this->tabla('Producto').' as p', 'p.codigo', '=', 'd.codigoProducto')
            ->leftJoin($this->tabla('Categoria').' as c', 'c.idCategoria', '=', 'p.idCategoria')
            ->select('p.codigo', 'p.nombre', 'c.idCategoria')
            ->selectRaw("COALESCE(c.nombre, 'Sin categoría') as categoria, DATE(s.fecha) as fecha, SUM(d.cantidad) as unidades, SUM(d.cantidad * d.precioCobrado) as ingresos")
            ->groupBy('p.codigo', 'p.nombre', 'c.idCategoria', 'c.nombre')->groupByRaw('DATE(s.fecha)');
        // Regla uniforme: incluye todos los ingresos recibidos el día de la ocurrencia.
        $costo = DB::table($this->tabla('Detalle_Ingreso').' as di')
            ->join($this->tabla('Ingreso').' as i', 'i.idIngreso', '=', 'di.idIngreso')
            ->whereNull('i.deleted_at')->whereNull('di.deleted_at')->where('di.cantidad', '>', 0)
            ->whereColumn('di.codigoProducto', 'v.codigo')->whereRaw('DATE(i.fecha) <= v.fecha')
            ->selectRaw('1.0 * SUM(di.cantidad * di.precioCompra) / NULLIF(SUM(di.cantidad), 0)');
        $filas = DB::query()->fromSub($ventas, 'v')->select('v.*')->selectSub($costo, 'costo')->get();
        $productos = $filas->groupBy('codigo')->map(fn ($grupo) => $this->ponderar($grupo) + [
            'nombre' => $grupo->first()->nombre, 'codigo' => $grupo->first()->codigo,
        ])->sortBy([['unidades', 'desc'], ['nombre', 'asc']])->values();
        $categorias = $filas->groupBy(fn ($fila) => $fila->idCategoria ?? 'sin-categoria')
            ->map(fn ($grupo) => $this->ponderar($grupo) + ['nombre' => $grupo->first()->categoria])
            ->sortBy([['unidades', 'desc'], ['nombre', 'asc']])->values();

        return compact('productos', 'categorias');
    }

    private function ponderar(Collection $filas): array
    {
        $unidades = (int) $filas->sum('unidades');
        $ingresos = (float) $filas->sum('ingresos');
        $sinCosto = (int) $filas->whereNull('costo')->sum('unidades');
        $costoTotal = $filas->sum(fn ($fila) => $fila->costo === null ? 0 : $fila->unidades * $fila->costo);

        return [
            'unidades' => $unidades, 'ingresos' => $ingresos,
            'precio' => $unidades > 0 ? $ingresos / $unidades : null,
            'costo' => $unidades > 0 && $sinCosto === 0 ? $costoTotal / $unidades : null,
            'margen' => $unidades > 0 && $sinCosto === 0 ? ($ingresos - $costoTotal) / $unidades : null,
            'sinCosto' => $sinCosto, 'ocurrencias' => $filas->pluck('fecha')->unique()->count(),
        ];
    }

    public function detalleDia(CarbonImmutable $dia)
    {
        return $this->ventasBase($dia, $dia)->join($this->tabla('Producto').' as p', 'p.codigo', '=', 'd.codigoProducto')
            ->select('s.idSalida', 's.fecha', 'p.nombre', 'd.cantidad', 'd.precioCobrado')
            ->selectRaw('d.cantidad * d.precioCobrado as ingresos')->orderBy('s.fecha')->orderBy('d.idDetalle')
            ->paginate(25, ['*'], 'pagina_detalle')->withQueryString();
    }
}
