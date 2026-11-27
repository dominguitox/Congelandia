<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FolioVenta
{
    /** Debe llamarse dentro de la misma transacción que guarda la venta. */
    public function reservar(CarbonInterface $momento): string
    {
        $fecha = $momento->toDateString();
        DB::table('folio_venta_dia')->insertOrIgnore(['fecha' => $fecha, 'ultimo' => 0]);
        $ultimo = (int) DB::table('folio_venta_dia')->where('fecha', $fecha)->lockForUpdate()->value('ultimo');
        if ($ultimo >= 999) {
            throw ValidationException::withMessages(['venta' => 'Se alcanzó el máximo de 999 ventas para este día.']);
        }

        $numero = $ultimo + 1;
        DB::table('folio_venta_dia')->where('fecha', $fecha)->update(['ultimo' => $numero]);

        return '#'.$momento->format('Ymd').'-'.sprintf('%03d', $numero);
    }
}
