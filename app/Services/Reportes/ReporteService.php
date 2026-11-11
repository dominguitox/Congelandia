<?php

namespace App\Services\Reportes;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
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
            ->whereRaw('LOWER(TRIM(t.nombre)) = ?', ['venta'])
            ->whereNull('d.deleted_at')
            ->whereBetween('s.fecha', [$desde->startOfDay(), $hasta->endOfDay()]);
    }

    public function ventas(CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        $base = $this->ventasBase($desde, $hasta);
        $tickets = (clone $base)->select('s.idSalida', 's.fecha')
            ->selectRaw('SUM(d.cantidad * d.precioCobrado) as ingresos, SUM(d.cantidad) as unidades')
            ->groupBy('s.idSalida', 's.fecha')->orderBy('s.fecha')->get();
        $ingresos = (float) $tickets->sum('ingresos');
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
        foreach ($tickets as $ticket) {
            $fecha = CarbonImmutable::parse($ticket->fecha);
            $dias[$fecha->toDateString()]['ingresos'] += (float) $ticket->ingresos;
            $dias[$fecha->toDateString()]['ventas']++;
            $semana[$fecha->dayOfWeekIso - 1]['ingresos'] += (float) $ticket->ingresos;
            $semana[$fecha->dayOfWeekIso - 1]['ventas']++;
            $mapa[$fecha->dayOfWeekIso - 1][$fecha->hour]++;
            $horas[$fecha->hour]++;
        }
        foreach ($semana as &$dia) {
            $dia['promedio'] = $dia['ocurrencias'] ? $dia['ingresos'] / $dia['ocurrencias'] : null;
        }
        unset($dia);
        $comparables = collect($semana)->where('ocurrencias', '>', 0);
        $productos = (clone $base)->join($this->tabla('Producto').' as p', 'p.codigo', '=', 'd.codigoProducto')
            ->select('p.codigo', 'p.nombre')->selectRaw('SUM(d.cantidad) as unidades, SUM(d.cantidad * d.precioCobrado) as ingresos')
            ->groupBy('p.codigo', 'p.nombre')->orderByDesc('unidades')->orderBy('p.nombre')->get();
        $categorias = (clone $base)->join($this->tabla('Producto').' as p', 'p.codigo', '=', 'd.codigoProducto')
            ->leftJoin($this->tabla('Categoria').' as c', 'c.idCategoria', '=', 'p.idCategoria')
            ->selectRaw("COALESCE(c.nombre, 'Sin categoría') as nombre, SUM(d.cantidad) as unidades, SUM(d.cantidad * d.precioCobrado) as ingresos")
            ->groupBy('c.idCategoria', 'c.nombre')->orderByDesc('ingresos')->get();

        return [
            'ingresos' => $ingresos, 'ventas' => $tickets->count(), 'unidades' => $tickets->sum('unidades'),
            'ticket' => $tickets->isEmpty() ? 0 : $ingresos / $tickets->count(),
            'dias' => collect(array_values($dias)), 'semana' => $semana, 'mapa' => $mapa,
            'maximo' => max(array_map('max', $mapa)), 'horaPunta' => $tickets->isEmpty() ? null : array_search(max($horas), $horas),
            'diaFuerte' => $tickets->isEmpty() ? null : $comparables->sortByDesc('promedio')->first(),
            'diaDebil' => $tickets->isEmpty() ? null : $comparables->sortBy('promedio')->first(),
            'productos' => $productos, 'categorias' => $categorias,
        ];
    }

    public function inventario(): array
    {
        $hoy = CarbonImmutable::today();
        $entradas = DB::table($this->tabla('Detalle_Ingreso').' as d')
            ->join($this->tabla('Ingreso').' as i', 'd.idIngreso', '=', 'i.idIngreso')
            ->whereNull('d.deleted_at')->whereNull('i.deleted_at')->where('i.fecha', '<=', now())
            ->select('d.idDetalle', 'd.codigoProducto', 'd.cantidad', 'd.fechaVencimiento')
            ->orderBy('d.fechaVencimiento')->orderBy('d.idDetalle')->get()->groupBy('codigoProducto');
        $salidas = $this->salidas()->join($this->tabla('Detalle_Salida').' as d', 'd.idSalida', '=', 's.idSalida')
            ->whereNull('d.deleted_at')->where('s.fecha', '<=', now())
            ->select('d.codigoProducto')->selectRaw('SUM(d.cantidad) as cantidad')->groupBy('d.codigoProducto')
            ->pluck('cantidad', 'codigoProducto');
        $precio = DB::table($this->tabla('Lista_Precio').' as lp')->select('lp.precioVenta')
            ->whereColumn('lp.codigoProducto', 'p.codigo')->whereNull('lp.deleted_at')
            ->where('lp.fechaInicio', '<=', now())->whereNull('lp.fechaFin')
            ->orderByDesc('lp.fechaInicio')->orderByDesc('lp.idPrecio')->limit(1);
        $productos = DB::table($this->tabla('Producto').' as p')
            ->leftJoin($this->tabla('Categoria').' as c', 'c.idCategoria', '=', 'p.idCategoria')
            ->whereNull('p.deleted_at')->select('p.codigo', 'p.nombre', 'c.nombre as categoria')
            ->selectSub($precio, 'precio')->orderBy('p.nombre')->get();
        $bajoStock = collect();
        $lotes = collect();
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
            // Estimación FEFO: no existe una relación entre la salida y su lote real.
            foreach ($ingresos as $ingreso) {
                $consumido = min($descontar, (int) $ingreso->cantidad);
                $descontar -= $consumido;
                $restante = (int) $ingreso->cantidad - $consumido;
                $dias = (int) $hoy->diffInDays(CarbonImmutable::parse($ingreso->fechaVencimiento), false);
                if ($restante > 0 && $dias <= 15) {
                    $lotes->push((object) [
                        'codigo' => $producto->codigo, 'nombre' => $producto->nombre,
                        'vencimiento' => $ingreso->fechaVencimiento, 'dias' => $dias, 'cantidad' => $restante,
                        'valor' => $producto->precio === null ? null : $restante * (float) $producto->precio,
                    ]);
                }
            }
        }
        $ventanas = collect([
            ['nombre' => 'Vencidos', 'min' => PHP_INT_MIN, 'max' => -1],
            ['nombre' => '0–3 días', 'min' => 0, 'max' => 3],
            ['nombre' => '4–7 días', 'min' => 4, 'max' => 7],
            ['nombre' => '8–15 días', 'min' => 8, 'max' => 15],
        ])->map(function ($ventana) use ($lotes) {
            $grupo = $lotes->whereBetween('dias', [$ventana['min'], $ventana['max']]);

            return $ventana + ['valor' => $grupo->sum('valor'), 'cantidad' => $grupo->sum('cantidad')];
        });

        return [
            'bajoStock' => $bajoStock->sortBy('stock')->values(), 'lotes' => $lotes->sortBy('dias')->values(),
            'valorRiesgo' => $lotes->sum('valor'), 'sinPrecio' => $lotes->whereNull('valor')->count(),
            'reposicion' => $bajoStock->sum('reponer'), 'ventanas' => $ventanas,
            'criticos' => $bajoStock->where('stock', '<=', 3)->count(),
        ];
    }

    public function filasCsv(string $seccion, array $datos): iterable
    {
        if ($seccion === 'inventario') {
            yield ['Inventario actual', 'Estimación FEFO: verificar lotes físicos. Umbral bajo stock: 10; objetivo: 20.'];
            yield ['Código', 'Producto', 'Stock actual', 'Reposición sugerida'];
            foreach ($datos['bajoStock'] as $producto) {
                yield [$producto->codigo, $producto->nombre, $producto->stock, $producto->reponer];
            }
            yield [];
            yield ['Código', 'Producto', 'Vencimiento', 'Unidades estimadas FEFO', 'Valor estimado a precio de venta CLP'];
            foreach ($datos['lotes'] as $lote) {
                yield [$lote->codigo, $lote->nombre, $lote->vencimiento, $lote->cantidad, $lote->valor];
            }
        } elseif ($seccion === 'habitos') {
            yield ['Día', 'Hora', 'Transacciones del período'];
            foreach ($datos['semana'] as $dia => $resumen) {
                foreach ($datos['mapa'][$dia] as $hora => $cantidad) {
                    yield [$resumen['nombre'], sprintf('%02d:00', $hora), $cantidad];
                }
            }
        } else {
            yield ['Resumen de ventas', 'Valor'];
            yield ['Ventas CLP (incluye fiado; excluye mermas)', $datos['ingresos']];
            yield ['Transacciones', $datos['ventas']];
            yield ['Unidades', $datos['unidades']];
            yield ['Ticket promedio CLP', $datos['ticket']];
            yield [];
            yield ['Fecha', 'Ventas CLP', 'Transacciones'];
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
