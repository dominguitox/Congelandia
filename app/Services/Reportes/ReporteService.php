<?php

namespace App\Services\Reportes;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    private array $tablas = [];

    // Compatibilidad con los nombres de los dumps y de las migraciones en Linux.
    protected function tabla(string $nombre): string
    {
        if ($this->tablas === []) {
            foreach (DB::getSchemaBuilder()->getTableListing(null, false) as $tabla) {
                $this->tablas[strtolower($tabla)] = $tabla;
            }
        }

        return $this->tablas[strtolower($nombre)] ?? $nombre;
    }

    protected function salidas(): Builder
    {
        return DB::table($this->tabla('Salida').' as s')->whereNull('s.deleted_at');
    }

    protected function ventasBase(CarbonImmutable $desde, CarbonImmutable $hasta): Builder
    {
        return $this->salidas()
            ->join($this->tabla('Tipo_Salida').' as t', 's.idTipo', '=', 't.idTipo')
            ->join($this->tabla('Detalle_Salida').' as d', 's.idSalida', '=', 'd.idSalida')
            ->whereIn(DB::raw('LOWER(TRIM(t.nombre))'), ['venta', 'salida histórica'])
            ->whereNull('d.deleted_at')
            ->whereBetween('s.fecha', [$desde->startOfDay(), $hasta->endOfDay()]);
    }

    public function ventas(CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        $base = $this->ventasBase($desde, $hasta);
        $salidasPeriodo = (clone $base)->select('s.idSalida', 's.fecha')
            ->selectRaw('SUM(d.cantidad * d.precioCobrado) as ingresos, SUM(d.cantidad) as unidades')
            ->groupBy('s.idSalida', 's.fecha')->orderBy('s.fecha')->get();
        $ingresos = (float) $salidasPeriodo->sum('ingresos');
        $dias = [];
        for ($dia = $desde; $dia->lessThanOrEqualTo($hasta); $dia = $dia->addDay()) {
            $dias[$dia->toDateString()] = ['fecha' => $dia->toDateString(), 'ingresos' => 0, 'ventas' => 0];
        }
        $semana = array_map(fn ($nombre) => ['nombre' => $nombre, 'ingresos' => 0, 'ventas' => 0, 'ocurrencias' => 0],
            ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']);
        foreach (array_keys($dias) as $fecha) {
            $semana[CarbonImmutable::parse($fecha)->dayOfWeekIso - 1]['ocurrencias']++;
        }
        $mapa = array_fill(0, 7, array_fill(0, 24, 0));
        $horas = array_fill(0, 24, 0);
        foreach ($salidasPeriodo as $salidaPeriodo) {
            $fecha = CarbonImmutable::parse($salidaPeriodo->fecha);
            $dias[$fecha->toDateString()]['ingresos'] += (float) $salidaPeriodo->ingresos;
            $dias[$fecha->toDateString()]['ventas']++;
            $semana[$fecha->dayOfWeekIso - 1]['ingresos'] += (float) $salidaPeriodo->ingresos;
            $semana[$fecha->dayOfWeekIso - 1]['ventas']++;
            $mapa[$fecha->dayOfWeekIso - 1][$fecha->hour]++;
            $horas[$fecha->hour]++;
        }
        foreach ($semana as &$dia) {
            $dia['promedio'] = $dia['ocurrencias'] ? $dia['ingresos'] / $dia['ocurrencias'] : null;
        }
        unset($dia);
        $granularidad = $desde->diffInDays($hasta) > 90 ? 'mes' : 'día';
        $serieGrafico = $dias;
        if ($granularidad === 'mes') {
            $serieGrafico = [];
            foreach ($dias as $dia) {
                $mes = substr($dia['fecha'], 0, 7);
                $serieGrafico[$mes] ??= ['fecha' => $mes, 'ingresos' => 0, 'ventas' => 0];
                $serieGrafico[$mes]['ingresos'] += $dia['ingresos'];
                $serieGrafico[$mes]['ventas'] += $dia['ventas'];
            }
        }
        $comparables = collect($semana)->where('ocurrencias', '>', 0);
        $productos = (clone $base)->join($this->tabla('Producto').' as p', 'p.codigo', '=', 'd.codigoProducto')
            ->select('p.codigo', 'p.nombre')->selectRaw('SUM(d.cantidad) as unidades, SUM(d.cantidad * d.precioCobrado) as ingresos')
            ->groupBy('p.codigo', 'p.nombre')->orderByDesc('unidades')->orderBy('p.nombre')->get();
        $categorias = (clone $base)->join($this->tabla('Producto').' as p', 'p.codigo', '=', 'd.codigoProducto')
            ->leftJoin($this->tabla('Categoria').' as c', 'c.idCategoria', '=', 'p.idCategoria')
            ->selectRaw("COALESCE(c.nombre, 'Sin categoría') as nombre, SUM(d.cantidad) as unidades, SUM(d.cantidad * d.precioCobrado) as ingresos")
            ->groupBy('c.idCategoria', 'c.nombre')->orderByDesc('ingresos')->get();

        return [
            'historialReferencia' => DB::table($this->tabla('Tipo_Salida'))->where('nombre', 'Salida histórica')->whereNull('deleted_at')->exists(),
            'ingresos' => $ingresos, 'ventas' => $salidasPeriodo->count(), 'unidades' => $salidasPeriodo->sum('unidades'),
            'ingresoPromedioPorSalida' => $salidasPeriodo->isEmpty() ? 0 : $ingresos / $salidasPeriodo->count(),
            'dias' => collect(array_values($dias)), 'serieGrafico' => collect(array_values($serieGrafico)),
            'granularidad' => $granularidad, 'semana' => $semana, 'mapa' => $mapa,
            'maximo' => max(array_map('max', $mapa)), 'horaPunta' => $salidasPeriodo->isEmpty() ? null : array_search(max($horas), $horas),
            'diaFuerte' => $salidasPeriodo->isEmpty() ? null : $comparables->sortByDesc('promedio')->first(),
            'diaDebil' => $salidasPeriodo->isEmpty() ? null : $comparables->sortBy('promedio')->first(),
            'productos' => $productos, 'categorias' => $categorias,
        ];
    }

    public function inventario(): array
    {
        $entradas = DB::table($this->tabla('Detalle_Ingreso').' as d')
            ->join($this->tabla('Ingreso').' as i', 'd.idIngreso', '=', 'i.idIngreso')
            ->whereNull('d.deleted_at')->whereNull('i.deleted_at')->where('i.fecha', '<=', now())
            ->select('d.codigoProducto', 'd.cantidad')->get()->groupBy('codigoProducto');
        $salidas = $this->salidas()->join($this->tabla('Detalle_Salida').' as d', 'd.idSalida', '=', 's.idSalida')
            ->whereNull('d.deleted_at')->where('s.fecha', '<=', now())
            ->select('d.codigoProducto')->selectRaw('SUM(d.cantidad) as cantidad')->groupBy('d.codigoProducto')
            ->pluck('cantidad', 'codigoProducto');
        $productos = DB::table($this->tabla('Producto').' as p')
            ->leftJoin($this->tabla('Categoria').' as c', 'c.idCategoria', '=', 'p.idCategoria')
            ->whereNull('p.deleted_at')->select('p.codigo', 'p.nombre', 'c.nombre as categoria')
            ->orderBy('p.nombre')->get();
        $bajoStock = collect();
        foreach ($productos as $producto) {
            $ingresos = $entradas->get($producto->codigo, collect());
            $descontar = (int) $salidas->get($producto->codigo, 0);
            $stock = (int) $ingresos->sum('cantidad') - $descontar;
            if ($stock < 10) {
                $bajoStock->push((object) [
                    'codigo' => $producto->codigo, 'nombre' => $producto->nombre,
                    'categoria' => $producto->categoria ?? 'Sin categoría', 'stock' => $stock,
                    'reponer' => 20 - $stock,
                ]);
            }
        }

        return [
            'bajoStock' => $bajoStock->sortBy('stock')->values(),
            'reposicion' => $bajoStock->sum('reponer'),
            'criticos' => $bajoStock->where('stock', '<=', 3)->count(),
        ];
    }

    /** Proveedores activos con ingresos y detalles vigentes hasta ahora. */
    public function proveedoresPorProducto(): Collection
    {
        return DB::table($this->tabla('Detalle_Ingreso').' as d')
            ->join($this->tabla('Ingreso').' as i', 'i.idIngreso', '=', 'd.idIngreso')
            ->join($this->tabla('Proveedor').' as pr', 'pr.idProveedor', '=', 'i.idProveedor')
            ->join($this->tabla('Producto').' as p', 'p.codigo', '=', 'd.codigoProducto')
            ->whereNull('d.deleted_at')->whereNull('i.deleted_at')
            ->whereNull('pr.deleted_at')->whereNull('p.deleted_at')->where('i.fecha', '<=', now())
            ->select('d.codigoProducto', 'pr.idProveedor', 'pr.nombre', 'pr.telefono', 'pr.correo')
            ->distinct()->orderBy('pr.nombre')->get()->groupBy('codigoProducto');
    }

    public function filasCsv(string $seccion, array $datos): iterable
    {
        if ($datos['historialReferencia'] ?? false) {
            yield ['Histórico', 'Los movimientos históricos usan el precio y costo disponibles del producto; sus importes son de referencia.'];
        }
        if ($seccion === 'inventario') {
            yield ['Inventario actual', 'Umbral bajo stock: 10; objetivo: 20.'];
            yield ['Código', 'Producto', 'Stock actual', 'Reposición sugerida'];
            foreach ($datos['bajoStock'] as $producto) {
                yield [$producto->codigo, $producto->nombre, $producto->stock, $producto->reponer];
            }
        } elseif ($seccion === 'habitos') {
            yield ['Día', 'Hora', 'Salidas del período'];
            foreach ($datos['semana'] as $dia => $resumen) {
                foreach ($datos['mapa'][$dia] as $hora => $cantidad) {
                    yield [$resumen['nombre'], sprintf('%02d:00', $hora), $cantidad];
                }
            }
        } else {
            yield ['Resumen de ventas', 'Valor'];
            yield ['Ventas CLP (incluye fiado; excluye mermas)', $datos['ingresos']];
            yield ['Salidas', $datos['ventas']];
            yield ['Unidades', $datos['unidades']];
            yield ['Ingreso promedio por salida CLP', $datos['ingresoPromedioPorSalida']];
            yield [];
            yield ['Fecha', 'Ventas CLP', 'Salidas'];
            foreach ($datos['dias'] as $dia) {
                yield [$dia['fecha'], $dia['ingresos'], $dia['ventas']];
            }
            yield [];
            yield ['Código', 'Producto', 'Unidades vendidas', 'Ventas CLP'];
            foreach ($datos['productos'] as $producto) {
                yield [$producto->codigo, $producto->nombre, $producto->unidades, $producto->ingresos];
            }
            yield [];
            yield ['Categoría', 'Unidades vendidas', 'Ventas CLP'];
            foreach ($datos['categorias'] as $categoria) {
                yield [$categoria->nombre, $categoria->unidades, $categoria->ingresos];
            }
        }
    }
}
