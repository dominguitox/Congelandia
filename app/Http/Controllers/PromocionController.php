<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    /**
     * Muestra una lista de las promociones.
     */
    public function index()
    {
        $promociones = Promocion::all();
        return view('promociones.index', compact('promociones'));
    }

    /**
     * Muestra el formulario para crear una nueva promoción.
     */
    public function create()
    {
        return view('promociones.create');
    }

    /**
     * Almacena una nueva promoción en la base de datos.
     */
    public function store(Request $request)
    {
        // Validación de los datos ingresados
        $request->validate([
            'codigoProducto' => 'required',
            'porcentajeDescuento' => 'required|numeric|min:0|max:100',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        Promocion::create($request->all());

        return redirect()->route('promociones.index')
                         ->with('success', 'Promoción creada exitosamente.');
    }

    /**
     * Muestra una promoción específica.
     */
    public function show($idPromocion)
    {
        // Se usa findOrFail con la clave primaria personalizada
        $promocion = Promocion::findOrFail($idPromocion);
        return view('promociones.show', compact('promocion'));
    }

    /**
     * Muestra el formulario para editar una promoción existente.
     */
    public function edit($idPromocion)
    {
        $promocion = Promocion::findOrFail($idPromocion);
        return view('promociones.edit', compact('promocion'));
    }

    /**
     * Actualiza una promoción específica en la base de datos.
     */
    public function update(Request $request, $idPromocion)
    {
        $request->validate([
            'codigoProducto' => 'required',
            'porcentajeDescuento' => 'required|numeric|min:0|max:100',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        $promocion = Promocion::findOrFail($idPromocion);
        $promocion->update($request->all());

        return redirect()->route('promociones.index')
                         ->with('success', 'Promoción actualizada exitosamente.');
    }

    /**
     * Elimina una promoción específica de la base de datos.
     */
    public function destroy($idPromocion)
    {
        $promocion = Promocion::findOrFail($idPromocion);
        $promocion->delete();

        return redirect()->route('promociones.index')
                         ->with('success', 'Promoción eliminada exitosamente.');
    }
}