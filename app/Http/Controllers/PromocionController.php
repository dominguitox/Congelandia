<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PromocionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarOferta($request);
        $this->validarSinCruce($datos);

        Promocion::create($datos + ['activa' => true]);

        return redirect()->to(route('inventario.index') . '#gestionar-ofertas')
            ->with('success', 'Oferta creada correctamente.');
    }

    public function update(Request $request, int $idPromocion): RedirectResponse
    {
        $oferta = Promocion::findOrFail($idPromocion);
        $datos = $this->validarOferta($request);
        $this->validarSinCruce($datos, $oferta->idPromocion);
        $oferta->update($datos);

        return redirect()->to(route('inventario.index') . '#gestionar-ofertas')
            ->with('success', 'Oferta actualizada correctamente.');
    }

    public function toggle(int $idPromocion): RedirectResponse
    {
        $oferta = Promocion::findOrFail($idPromocion);

        if (! $oferta->activa) {
            if ($this->hayCruce([
                'codigoProducto' => $oferta->codigoProducto,
                'fechaInicio' => $oferta->fechaInicio,
                'fechaFin' => $oferta->fechaFin,
            ], $oferta->idPromocion)) {
                return redirect()->to(route('inventario.index') . '#gestionar-ofertas')
                    ->with('error', 'No se puede reactivar: ya existe otra oferta activa para ese producto en ese período.');
            }
        }

        $oferta->update(['activa' => ! $oferta->activa]);

        return redirect()->to(route('inventario.index') . '#gestionar-ofertas')
            ->with('success', $oferta->activa ? 'Oferta activada.' : 'Oferta pausada.');
    }

    public function destroy(int $idPromocion): RedirectResponse
    {
        Promocion::findOrFail($idPromocion)->delete();

        return redirect()->to(route('inventario.index') . '#gestionar-ofertas')
            ->with('success', 'Oferta eliminada.');
    }

    private function validarOferta(Request $request): array
    {
        $datos = $request->validate([
            'codigoProducto' => [
                'required',
                'string',
                Rule::exists('Producto', 'codigo')->whereNull('deleted_at'),
            ],
            'porcentajeDescuento' => ['required', 'numeric', 'gt:0', 'lte:100'],
            'fechaInicio' => ['required', 'date'],
            'fechaFin' => ['required', 'date', 'after:fechaInicio'],
        ], [
            'fechaFin.after' => 'La fecha de término debe ser posterior al inicio.',
            'porcentajeDescuento.gt' => 'El descuento debe ser mayor que 0%.',
            'porcentajeDescuento.lte' => 'El descuento no puede superar el 100%.',
        ]);

        $datos['fechaInicio'] = Carbon::parse($datos['fechaInicio'])->format('Y-m-d H:i:s');
        $datos['fechaFin'] = Carbon::parse($datos['fechaFin'])->format('Y-m-d H:i:s');

        return $datos;
    }

    private function validarSinCruce(array $datos, ?int $excepto = null): void
    {
        if ($this->hayCruce($datos, $excepto)) {
            throw ValidationException::withMessages([
                'codigoProducto' => 'Este producto ya tiene una oferta activa en ese período.',
            ]);
        }
    }

    private function hayCruce(array $datos, ?int $excepto = null): bool
    {
        return Promocion::query()
            ->where('codigoProducto', $datos['codigoProducto'])
            ->where('activa', true)
            ->where('fechaInicio', '<', $datos['fechaFin'])
            ->where('fechaFin', '>', $datos['fechaInicio'])
            ->when($excepto, fn ($query) => $query->where('idPromocion', '!=', $excepto))
            ->exists();
    }
}
