<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistorialService
{
    public function datos(Request $request): array
    {
        $pagina = filter_var($request->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($pagina === false, 404, 'La página solicitada no existe.');
        $filters = $request->validate(['buscar' => 'nullable|string|max:150', 'tipo' => 'nullable|string|max:100']);
        $buscar = trim($filters['buscar'] ?? '');
        $tipo = $filters['tipo'] ?? 'todos';
        $historicalEmail = 'historico@congelandia.invalid';
        $incomes = DB::table('ingreso as i')->join('usuario as u', 'u.idUsuario', '=', 'i.idUsuario')
            ->leftJoin('proveedor as p', 'p.idProveedor', '=', 'i.idProveedor')
            ->whereNull('i.deleted_at')
            ->select(['i.idIngreso as id', 'i.fecha', 'u.nombre as usuario', 'p.nombre as proveedor', 'i.totalCompra as total'])
            ->selectRaw("'ingreso' AS tipo_clase, 'Ingreso' AS tipo, CASE WHEN u.email = ? THEN 1 ELSE 0 END AS historico, NULL AS folio", [$historicalEmail]);
        $outgoings = DB::table('salida as s')->join('usuario as u', 'u.idUsuario', '=', 's.idUsuario')
            ->join('tipo_salida as t', 't.idTipo', '=', 's.idTipo')->whereNull('s.deleted_at')
            ->select(['s.idSalida as id', 's.fecha', 'u.nombre as usuario'])->selectRaw('NULL AS proveedor')
            ->addSelect('s.totalSalida as total')->selectRaw("'salida' AS tipo_clase")
            ->addSelect('t.nombre as tipo')->selectRaw('CASE WHEN u.email = ? THEN 1 ELSE 0 END AS historico', [$historicalEmail])
            ->addSelect('s.folio as folio');
        $all = DB::query()->fromSub($incomes->unionAll($outgoings), 'm');
        $totalMovimientos = (clone $all)->count();
        $totalIngresos = (clone $all)->where('m.tipo_clase', 'ingreso')->count();
        $totalSalidas = $totalMovimientos - $totalIngresos;
        $query = clone $all;
        if ($tipo === 'ingreso') {
            $query->where('m.tipo_clase', 'ingreso');
        } elseif (str_starts_with($tipo, 'salida:')) {
            $name = mb_strtolower(substr($tipo, 7));
            $query->where('m.tipo_clase', 'salida')->whereRaw('LOWER(m.tipo) = ?', [$name === 'merma' ? 'merma por vencimiento' : $name]);
        }
        if ($buscar !== '') {
            $pattern = '%'.$buscar.'%';
            $query->where(function ($q) use ($pattern, $buscar) {
                $q->where('m.usuario', 'like', $pattern)->orWhere('m.proveedor', 'like', $pattern)
                    ->orWhere('m.tipo', 'like', $pattern)->orWhere('m.folio', 'like', $pattern);
                if (preg_match('/^(?:Ingreso|Salida)?\s*#?(\d+)$/ui', $buscar, $match)) {
                    $q->orWhere('m.id', (int) $match[1]);
                }
                foreach (['ingreso' => ['detalle_ingreso', 'idIngreso'], 'salida' => ['detalle_salida', 'idSalida']] as $direction => [$table, $parent]) {
                    $q->orWhereExists(function ($detail) use ($direction, $table, $parent, $pattern) {
                        $detail->selectRaw('1')->from($table.' as d')->join('producto as p', 'p.codigo', '=', 'd.codigoProducto')
                            ->where('m.tipo_clase', $direction)->whereColumn('d.'.$parent, 'm.id')->whereNull('d.deleted_at')
                            ->where(fn ($text) => $text->where('p.nombre', 'like', $pattern)->orWhere('p.codigo', 'like', $pattern));
                    });
                }
            });
        }
        $totalFiltrado = (clone $query)->count();
        abort_if($pagina > max(1, (int) ceil($totalFiltrado / 50)), 404, 'La página solicitada no existe.');
        $movimientos = $query->orderByDesc('m.fecha')->orderBy('m.tipo_clase')->orderByDesc('m.id')
            ->paginate(50, ['*'], 'page', $pagina, $totalFiltrado)->withQueryString();
        $details = [];
        foreach (['ingreso' => ['detalle_ingreso', 'idIngreso', 'precioCompra'], 'salida' => ['detalle_salida', 'idSalida', 'precioCobrado']] as $direction => [$table, $parent, $price]) {
            $ids = $movimientos->getCollection()->where('tipo_clase', $direction)->pluck('id');
            $details[$direction] = DB::table($table.' as d')->join('producto as p', 'p.codigo', '=', 'd.codigoProducto')
                ->whereIn('d.'.$parent, $ids)->whereNull('d.deleted_at')
                ->select(['d.'.$parent.' as padre', 'p.nombre as producto', 'd.codigoProducto as codigo', 'd.cantidad', 'd.'.$price.' as precio'])
                ->get()->groupBy('padre');
        }
        $movimientos->setCollection($movimientos->getCollection()->map(function ($row) use ($details) {
            $lines = $details[$row->tipo_clase]->get($row->id, collect());
            $type = $row->tipo === 'Merma por Vencimiento' ? 'Merma' : $row->tipo;

            return [
                'id' => $row->id, 'fecha' => $row->fecha, 'tipo_clase' => $row->tipo_clase,
                'tipo' => $row->tipo_clase === 'ingreso' && $row->historico ? 'Ingreso histórico' : $type,
                'referencia' => $row->folio ? 'Venta '.$row->folio : ($row->tipo_clase === 'ingreso' ? 'Ingreso #' : 'Salida #').$row->id,
                'usuario' => $row->usuario, 'proveedor' => $row->tipo_clase === 'ingreso' ? ($row->proveedor ?? 'Sin proveedor') : null,
                'cantidad' => $lines->sum('cantidad'), 'total' => $row->total,
                'detalles' => $lines->map(fn ($line) => ['producto' => $line->producto, 'codigo' => $line->codigo, 'cantidad' => $line->cantidad, 'precio' => $line->precio])->values()->all(),
            ];
        }));
        $tiposSalida = DB::table('tipo_salida')->whereNull('deleted_at')->orderBy('nombre')->pluck('nombre')
            ->map(fn ($name) => $name === 'Merma por Vencimiento' ? 'Merma' : $name)->unique()->values();
        $hayHistorico = DB::table('usuario')->where('email', $historicalEmail)->exists();

        return compact('movimientos', 'tiposSalida', 'totalMovimientos', 'totalIngresos', 'totalSalidas', 'buscar', 'tipo', 'hayHistorico');
    }
}
