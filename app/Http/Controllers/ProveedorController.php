<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    /**
     * =========================================================
     * LISTAR PROVEEDORES
     * =========================================================
     */
    public function listarProveedores(Request $request)
    {
        $query = Proveedor::whereNull('deleted_at');

        // Búsqueda por nombre, teléfono o correo.
        if ($request->filled('buscar')) {

            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {

                $q->where('nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('telefono', 'like', '%' . $buscar . '%')
                    ->orWhere('correo', 'like', '%' . $buscar . '%');

            });
        }

        $proveedores = $query
            ->orderBy('nombre')
            ->get();

        /*
         * Obtener los ingresos, detalles y productos
         * asociados a cada proveedor.
         */
        $proveedores->load([
            'ingresos.detalles.producto'
        ]);

        /*
         * Preparar los productos de cada proveedor.
         */
        foreach ($proveedores as $proveedor) {

            $productos = collect();

            foreach ($proveedor->ingresos as $ingreso) {

                foreach ($ingreso->detalles as $detalle) {

                    if (
                        $detalle->producto &&
                        is_null($detalle->producto->deleted_at)
                    ) {

                        $producto = $detalle->producto;

                        /*
                         * Acumulamos la cantidad ingresada.
                         */
                        $producto->cantidadIngresada =
                            ($producto->cantidadIngresada ?? 0)
                            + $detalle->cantidad;

                        /*
                         * Guardamos el último precio registrado.
                         */
                        $producto->ultimoPrecioCompra =
                            $detalle->precioCompra;

                        /*
                         * Guardamos la última fecha de vencimiento.
                         */
                        $producto->ultimaFechaVencimiento =
                            $detalle->fechaVencimiento;

                        $productos->push($producto);
                    }
                }
            }

            /*
             * Evitar productos duplicados.
             */
            $productos = $productos
                ->unique('codigo')
                ->values();

            $proveedor->productos = $productos;
            $proveedor->productos_count = $productos->count();
        }

        /*
         * =====================================================
         * ESTADÍSTICAS
         * =====================================================
         */

        $totalProveedores = Proveedor::whereNull('deleted_at')
            ->count();

        /*
         * Productos que tienen al menos un proveedor.
         */
        $productosConProveedor = DB::table('detalle_ingreso')
            ->join(
                'ingreso',
                'detalle_ingreso.idIngreso',
                '=',
                'ingreso.idIngreso'
            )
            ->join(
                'proveedor',
                'ingreso.idProveedor',
                '=',
                'proveedor.idProveedor'
            )
            ->join(
                'producto',
                'detalle_ingreso.codigoProducto',
                '=',
                'producto.codigo'
            )
            ->whereNull('proveedor.deleted_at')
            ->whereNull('producto.deleted_at')
            ->distinct()
            ->pluck('detalle_ingreso.codigoProducto');

        $cantidadProductosConProveedor =
            $productosConProveedor->count();

        /*
         * Total de productos activos.
         */
        $totalProductos = DB::table('producto')
            ->whereNull('deleted_at')
            ->count();

        /*
         * Productos sin proveedor.
         */
        $productosSinProveedor = max(
            0,
            $totalProductos - $cantidadProductosConProveedor
        );

        return view('proveedores.index', compact(
            'proveedores',
            'totalProveedores',
            'cantidadProductosConProveedor',
            'productosSinProveedor'
        ));
    }


    /**
     * =========================================================
     * CREAR PROVEEDOR
     * =========================================================
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
        ]);

        Proveedor::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }


    /**
     * =========================================================
     * EDITAR PROVEEDOR
     * =========================================================
     */
    public function update(Request $request, $idProveedor)
    {
        $proveedor = Proveedor::whereNull('deleted_at')
            ->findOrFail($idProveedor);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
        ]);

        $proveedor->update([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }


    /**
     * =========================================================
     * ELIMINAR PROVEEDOR
     * =========================================================
     */
    public function destroy($idProveedor)
    {
        $proveedor = Proveedor::whereNull('deleted_at')
            ->findOrFail($idProveedor);

        /*
         * Comprobar si existen productos activos
         * relacionados con este proveedor.
         */
        $tieneProductos = DB::table('detalle_ingreso')
            ->join(
                'ingreso',
                'detalle_ingreso.idIngreso',
                '=',
                'ingreso.idIngreso'
            )
            ->join(
                'producto',
                'detalle_ingreso.codigoProducto',
                '=',
                'producto.codigo'
            )
            ->where(
                'ingreso.idProveedor',
                $proveedor->idProveedor
            )
            ->whereNull('producto.deleted_at')
            ->exists();

        if ($tieneProductos) {

            return redirect()
                ->route('proveedores.index')
                ->with(
                    'error',
                    'No se puede eliminar el proveedor porque tiene productos asociados.'
                );
        }

        $proveedor->delete();

        return redirect()
            ->route('proveedores.index')
            ->with(
                'success',
                'Proveedor eliminado correctamente.'
            );
    }
}