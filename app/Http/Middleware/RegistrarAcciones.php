<?php

namespace App\Http\Middleware;

use App\Models\RegistroAccion;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RegistrarAcciones
{
    public const ACCIONES = [
        'auth.login' => ['Autenticación', 'Inicio de sesión'],
        'logout' => ['Autenticación', 'Cierre de sesión'],
        'usuarios.store' => ['Usuarios', 'Usuario creado'],
        'usuarios.estado' => ['Usuarios', 'Estado de usuario actualizado'],
        'productos.crearProducto' => ['Inventario', 'Producto creado'],
        'inventario.editarProducto' => ['Inventario', 'Producto editado'],
        'inventario.ingresos.store' => ['Inventario', 'Ingreso registrado'],
        'inventario.salidas.store' => ['Inventario', 'Salida o merma registrada'],
        'venta.registrar' => ['Ventas', 'Venta registrada'],
        'clientes.store' => ['Clientes', 'Cliente creado'],
        'clientes.update' => ['Clientes', 'Cliente editado'],
        'clientes.destroy' => ['Clientes', 'Cliente eliminado'],
        'clientes.registrarPago' => ['Clientes', 'Pago registrado'],
        'categorias.store' => ['Categorías', 'Categoría creada'],
        'categorias.update' => ['Categorías', 'Categoría editada'],
        'categorias.destroy' => ['Categorías', 'Categoría eliminada'],
        'proveedores.store' => ['Proveedores', 'Proveedor creado'],
        'proveedores.update' => ['Proveedores', 'Proveedor editado'],
        'proveedores.destroy' => ['Proveedores', 'Proveedor eliminado'],
        'ofertas.store' => ['Ofertas', 'Oferta creada'],
        'ofertas.update' => ['Ofertas', 'Oferta editada'],
        'ofertas.toggle' => ['Ofertas', 'Estado de oferta actualizado'],
        'ofertas.destroy' => ['Ofertas', 'Oferta eliminada'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ruta = $request->route()?->getName();
        if ($request->isMethod('POST') && $request->is('login')) {
            $ruta = 'auth.login';
        }
        if (! isset(self::ACCIONES[$ruta]) || ! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) || (! $request->user() && $ruta !== 'auth.login')) {
            return $next($request);
        }

        // El cambio de negocio y su registro se confirman juntos, sin repetir peticiones.
        return DB::transaction(function () use ($request, $next, $ruta) {
            $anterior = $request->user();
            $response = $next($request);
            $usuario = $request->user() ?? $anterior;
            $json = $response instanceof JsonResponse ? $response->getData(true) : [];
            $errores = array_intersect(['error', 'errors'], $request->session()->get('_flash.new', []));
            if ($response->getStatusCode() >= 400 || $errores || ($json['success'] ?? true) === false) {
                throw new HttpResponseException($response);
            }
            if (! $usuario || ($json['repetida'] ?? false) || $request->attributes->get('accion_sin_cambios')) {
                return $response;
            }
            [$modulo, $accion] = self::ACCIONES[$ruta];
            if ($ruta === 'usuarios.estado') {
                $accion = $request->boolean('activo') ? 'Usuario activado' : 'Usuario desactivado';
            }
            $referencias = [];
            foreach ($request->route()->parameters() as $nombre => $valor) {
                if ($valor instanceof Model) {
                    $valor = $valor->getKey();
                }
                if (is_scalar($valor)) {
                    $referencias[] = $nombre.': '.$valor;
                }
            }
            if (! $referencias) {
                // Lista explícita: nunca copiar formularios, credenciales ni cabeceras.
                foreach (['codigo', 'codigoProducto', 'rutCliente', 'nombre'] as $campo) {
                    if (is_scalar($request->input($campo))) {
                        $referencias[] = $campo.': '.$request->input($campo);
                        break;
                    }
                }
            }
            if (isset($json['idSalida'])) {
                $referencias[] = 'venta: '.$json['idSalida'];
            }
            RegistroAccion::create([
                'idUsuario' => $usuario->idUsuario, 'usuarioNombre' => $usuario->nombre,
                'accion' => $accion, 'modulo' => $modulo, 'ruta' => $ruta,
                'referencia' => $referencias ? Str::limit(implode(' · ', $referencias), 255, '') : null,
                'fecha' => now(),
            ]);

            return $response;
        });
    }
}
