<?php

namespace App\Http\Controllers;

use App\Services\CatalogoOffline;
use App\Services\SincronizarOffline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OfflineController extends Controller
{
    public function datos(Request $request, CatalogoOffline $catalogo)
    {
        if ($error = $this->esquemaPendiente()) {
            return $error;
        }

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
        if ($error = $this->esquemaPendiente()) {
            return $error;
        }
        $operacion = $request->validate([
            'id' => ['required', 'uuid'], 'usuario' => ['required', 'integer'],
            'tipo' => ['required', 'in:venta,ingreso,merma'], 'fechaLocal' => ['required', 'date'],
            'datos' => ['required', 'array'],
        ]);
        abort_unless((int) $operacion['usuario'] === (int) $request->user()->idUsuario, 403, 'Inicia sesión con el usuario que guardó estos movimientos.');
        $resultado = $servicio->ejecutar($operacion, $request->user());

        return response()->json($resultado, $resultado['success'] ? 200 : 409)->header('Cache-Control', 'no-store, private');
    }

    private function esquemaPendiente()
    {
        if (! Schema::hasTable('operaciones_offline') || ! Schema::hasColumns('salida', ['claveOperacion', 'firmaCobro', 'efectivoRecibido', 'vuelto'])) {
            return response()->json([
                'success' => false, 'code' => 'offline_schema_missing',
                'message' => 'El servidor necesita actualizar su base de datos antes de recibir movimientos. Los pendientes se conservan en este equipo.',
            ], 503)->header('Cache-Control', 'no-store, private');
        }

        return null;
    }
}
