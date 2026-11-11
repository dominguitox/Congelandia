<?php

namespace App\Services\Reportes;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class PronosticoStockService extends ReporteService
{
    public function estimar(): array
    {
        $hoy = CarbonImmutable::today();
        $desde = $hoy->subDays(56);
        // Días completos con ceros, igual criterio de exposición que Hábitos de compra.
        $recientes = $this->ventasBase($desde, $hoy->subDay())->select('d.codigoProducto')
            ->selectRaw('SUM(d.cantidad) as unidades, COUNT(DISTINCT DATE(s.fecha)) as dias')
            ->groupBy('d.codigoProducto')->get()->keyBy('codigoProducto');
        $entradas = DB::table($this->tabla('Detalle_Ingreso').' as d')->join($this->tabla('Ingreso').' as i', 'i.idIngreso', '=', 'd.idIngreso')
            ->whereNull('d.deleted_at')->whereNull('i.deleted_at')->where('i.fecha', '<=', now());
        $stockEntradas = (clone $entradas)->select('d.codigoProducto')
            ->selectRaw('SUM(d.cantidad) as cantidad, MIN(i.fecha) as primera')->groupBy('d.codigoProducto');
        $stockSalidas = $this->salidas()->join($this->tabla('Detalle_Salida').' as d', 'd.idSalida', '=', 's.idSalida')
            ->whereNull('d.deleted_at')->where('s.fecha', '<=', now())->select('d.codigoProducto')
            ->selectRaw('SUM(d.cantidad) as cantidad')->groupBy('d.codigoProducto');
        $productos = DB::table($this->tabla('Producto').' as p')->whereNull('p.deleted_at')
            ->leftJoinSub($stockEntradas, 'e', 'e.codigoProducto', '=', 'p.codigo')
            ->leftJoinSub($stockSalidas, 's', 's.codigoProducto', '=', 'p.codigo')
            ->select('p.codigo', 'p.nombre', 'e.primera')->selectRaw('COALESCE(e.cantidad, 0) - COALESCE(s.cantidad, 0) as stock')
            ->orderBy('p.nombre')->get();
        // Contexto de ingresos recibidos: agrupado por fecha y proveedor, últimos 365 días.
        $ingresos = (clone $entradas)->leftJoin($this->tabla('Proveedor').' as pr', 'pr.idProveedor', '=', 'i.idProveedor')
            ->where('i.fecha', '>=', $hoy->subDays(365))->select('d.codigoProducto', 'i.idProveedor', 'pr.nombre as proveedor')
            ->selectRaw('DATE(i.fecha) as fecha, SUM(d.cantidad) as cantidad')
            ->groupBy('d.codigoProducto', 'i.idProveedor', 'pr.nombre')->groupByRaw('DATE(i.fecha)')->orderBy('fecha')->get()->groupBy('codigoProducto');
        $estacionales = [];
        // Máximo tres consultas agregadas; sin consultas por producto.
        for ($n = 1; $n <= 3; $n++) {
            $pivote = $hoy->subYearsNoOverflow($n);
            $estacionales[] = $this->ventasBase($pivote->subDays(56), $pivote->addDays(27))->select('d.codigoProducto')
                ->selectRaw('SUM(CASE WHEN s.fecha < ? THEN d.cantidad ELSE 0 END) as antes, SUM(CASE WHEN s.fecha >= ? THEN d.cantidad ELSE 0 END) as despues', [$pivote, $pivote])
                ->selectRaw('COUNT(DISTINCT CASE WHEN s.fecha < ? THEN DATE(s.fecha) END) as dias_antes, COUNT(DISTINCT CASE WHEN s.fecha >= ? THEN DATE(s.fecha) END) as dias_despues', [$pivote, $pivote])
                ->groupBy('d.codigoProducto')->get()->keyBy('codigoProducto');
        }
        $avisos = collect();
        $insuficientes = collect();
        foreach ($productos as $producto) {
            $reciente = $recientes->get($producto->codigo);
            $inicio = $producto->primera ? CarbonImmutable::parse($producto->primera)->startOfDay()->max($desde) : $hoy;
            $dias = (int) $inicio->diffInDays($hoy);
            $contexto = ($ingresos->get($producto->codigo, collect()))->groupBy(fn ($i) => $i->idProveedor ?? 'sin-proveedor')->map(function ($grupo) {
                $fechas = $grupo->pluck('fecha')->unique()->sort()->values();

                return [
                    'proveedor' => $grupo->first()->proveedor ?? 'Sin proveedor registrado',
                    'recepciones' => $fechas->count(), 'cantidad' => $grupo->sum('cantidad'),
                    'intervalo' => $fechas->count() > 1 ? CarbonImmutable::parse($fechas->first())->diffInDays(CarbonImmutable::parse($fechas->last())) / ($fechas->count() - 1) : null,
                    'ultimos' => $grupo->sortByDesc('fecha')->take(3)->values(),
                ];
            })->values();
            $base = ['codigo' => $producto->codigo, 'nombre' => $producto->nombre, 'stock' => (int) $producto->stock, 'ingresos' => $contexto];
            if (! $producto->primera || $dias < 14 || ! $reciente || $reciente->dias < 3 || $reciente->unidades < 5) {
                $insuficientes->push($base + ['motivo' => 'Datos insuficientes para estimar: se requieren ingresos registrados, 14 días de observación y al menos 5 unidades en 3 días de venta durante los últimos 56 días completos.']);

                continue;
            }
            $antes = 0;
            $despues = 0;
            $anios = 0;
            foreach ($estacionales as $indice => $temporada) {
                $fila = $temporada->get($producto->codigo);
                if ($fila && $fila->dias_antes >= 3 && $fila->dias_despues >= 3
                    && CarbonImmutable::parse($producto->primera)->lessThanOrEqualTo($hoy->subYearsNoOverflow($indice + 1)->subDays(56))) {
                    $antes += $fila->antes;
                    $despues += $fila->despues;
                    $anios++;
                }
            }
            $factor = $anios >= 2 && $antes > 0 ? ($despues / 28) / ($antes / 56) : 1.0;
            $ritmo = ($reciente->unidades / $dias) * $factor;
            $restantes = max(0, (int) ceil($producto->stock / $ritmo));
            // Fuera del horizonte no se inventa una fecha de largo plazo.
            if ($restantes > 90) {
                $insuficientes->push($base + ['motivo' => 'Datos insuficientes para estimar dentro del horizonte de 90 días; el ritmo observado no permite una fecha fiable a más largo plazo.']);

                continue;
            }
            $fecha = $hoy->addDays($restantes)->toDateString();
            $avisos->push($base + [
                'fecha' => $fecha, 'estimada' => $fecha, 'ritmo' => $ritmo, 'dias' => $dias,
                'urgencia' => $producto->stock <= 0 ? 'Urgente / agotado o atrasado' : ($restantes <= 7 ? 'Próximo: revisar existencias' : 'Previsión: revisar existencias'),
                'estacional' => $anios >= 2 ? $anios : 0,
            ]);
        }

        return ['avisos' => $avisos->sortBy('fecha')->values(), 'insuficientes' => $insuficientes];
    }
}
