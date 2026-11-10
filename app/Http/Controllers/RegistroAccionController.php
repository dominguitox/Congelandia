<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RegistrarAcciones;
use App\Models\RegistroAccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistroAccionController extends Controller
{
    public function index(Request $request)
    {
        $modulos = collect(RegistrarAcciones::ACCIONES)->pluck(0)->unique()->sort()->values();
        $filtros = $request->validate([
            'usuario' => ['nullable', 'integer', Rule::exists('usuario', 'idUsuario')],
            'modulo' => ['nullable', Rule::in($modulos->all())],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', ...($request->filled('desde') ? ['after_or_equal:desde'] : [])],
        ]);
        $query = RegistroAccion::query();
        if (! empty($filtros['usuario'])) {
            $query->where('idUsuario', $filtros['usuario']);
        }
        if (! empty($filtros['modulo'])) {
            $query->where('modulo', $filtros['modulo']);
        }
        if (! empty($filtros['desde'])) {
            $query->where('fecha', '>=', $filtros['desde'].' 00:00:00');
        }
        if (! empty($filtros['hasta'])) {
            $query->where('fecha', '<=', $filtros['hasta'].' 23:59:59');
        }
        $acciones = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(25)->withQueryString();
        $usuarios = User::withTrashed()->orderBy('nombre')->get();

        return view('acciones.index', compact('acciones', 'usuarios', 'modulos', 'filtros'));
    }
}
