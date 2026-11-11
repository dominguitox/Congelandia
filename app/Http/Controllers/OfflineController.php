<?php

namespace App\Http\Controllers;

use App\Services\CatalogoOffline;
use App\Services\SincronizarOffline;
use Illuminate\Http\Request;

class OfflineController extends Controller
{
    public function datos(Request $request, CatalogoOffline $catalogo)
    {
        return response()->json($catalogo->obtener($request->user()))->header('Cache-Control', 'no-store, private');
    }

    public function sesion(Request $request)
    {
        // Se usa solamente en memoria para enviar peticiones con la sesión
        // actual; nunca se guarda en IndexedDB ni en el Service Worker.
        return response()->json(['usuario' => (int) $request->user()->idUsuario, 'csrf' => csrf_token()])
            ->header('Cache-Control', 'no-store, private');
    }

    public function sincronizar(Request $request, SincronizarOffline $servicio)
    {
        $operacion = $request->validate([
            'id' => ['required', 'uuid'], 'usuario' => ['required', 'integer'],
            'tipo' => ['required', 'in:venta,ingreso,merma'], 'fechaLocal' => ['required', 'date'],
            'datos' => ['required', 'array'],
        ]);
        abort_unless((int) $operacion['usuario'] === (int) $request->user()->idUsuario, 403, 'Inicia sesión con el usuario que guardó estos movimientos.');
        $resultado = $servicio->ejecutar($operacion, $request->user());

        return response()->json($resultado, $resultado['success'] ? 200 : 409)->header('Cache-Control', 'no-store, private');
    }
}
