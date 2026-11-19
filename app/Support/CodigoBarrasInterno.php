<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Códigos EAN-13 de uso interno para productos sin código de origen.
 *
 * El prefijo 20 identifica códigos de circulación interna. Congelandia no
 * registra estos códigos ante GS1 ni garantiza que otros sistemas los acepten.
 * El valor siempre se conserva como texto para no perder ceros iniciales.
 *
 * Estructura: 20 + AAMMDD + secuencia diaria (0001-9999) + dígito verificador.
 */
final class CodigoBarrasInterno
{
    public const FORMATO = 'EAN-13';

    public const PREFIJO = '20';

    public const LONGITUD = 13;

    public const MAX_SECUENCIA = 9999;

    private const LONGITUD_BASE = 12;

    private const DIGITOS_SECUENCIA = 4;

    /**
     * Genera el código para una fecha y un número de secuencia de ese día.
     */
    public static function generar(?DateTimeInterface $fecha = null, int $secuencia = 1): string
    {
        return self::formatear($fecha ?? new DateTimeImmutable(), $secuencia);
    }

    public static function formatear(DateTimeInterface $fecha, int $secuencia): string
    {
        if ($secuencia < 1 || $secuencia > self::MAX_SECUENCIA) {
            throw new InvalidArgumentException('La secuencia diaria debe estar entre 1 y '.self::MAX_SECUENCIA.'.');
        }

        $base = self::prefijoFecha($fecha).str_pad((string) $secuencia, self::DIGITOS_SECUENCIA, '0', STR_PAD_LEFT);

        return self::conDigitoVerificador($base);
    }

    /**
     * Primeros 8 dígitos del código: prefijo interno + AAMMDD.
     */
    public static function prefijoFecha(DateTimeInterface $fecha): string
    {
        return self::PREFIJO.$fecha->format('ymd');
    }

    /**
     * Devuelve la secuencia diaria si el código pertenece al formato interno y,
     * opcionalmente, a la fecha indicada. No exige dígito verificador válido:
     * así se evita reutilizar un número aunque exista un código mal formado.
     */
    public static function secuenciaDe(string $codigo, ?DateTimeInterface $fecha = null): ?int
    {
        if (preg_match('/^\d{'.self::LONGITUD.'}$/D', $codigo) !== 1 || substr($codigo, 0, 2) !== self::PREFIJO) {
            return null;
        }

        if ($fecha !== null && substr($codigo, 2, 6) !== $fecha->format('ymd')) {
            return null;
        }

        return (int) substr($codigo, 8, self::DIGITOS_SECUENCIA);
    }

    public static function conDigitoVerificador(string $base): string
    {
        if (preg_match('/^\d{12}$/D', $base) !== 1) {
            throw new InvalidArgumentException('La base de un EAN-13 debe tener exactamente 12 dígitos.');
        }

        return $base.self::digitoVerificador($base);
    }

    public static function digitoVerificador(string $base): int
    {
        if (preg_match('/^\d{12}$/D', $base) !== 1) {
            throw new InvalidArgumentException('La base de un EAN-13 debe tener exactamente 12 dígitos.');
        }

        $suma = 0;
        for ($posicion = 0; $posicion < self::LONGITUD_BASE; $posicion++) {
            $digito = (int) $base[$posicion];
            $suma += $posicion % 2 === 0 ? $digito : $digito * 3;
        }

        return (10 - ($suma % 10)) % 10;
    }

    public static function esValido(string $codigo): bool
    {
        if (strlen($codigo) !== self::LONGITUD || ! ctype_digit($codigo)) {
            return false;
        }

        return (int) $codigo[self::LONGITUD - 1] === self::digitoVerificador(substr($codigo, 0, self::LONGITUD_BASE));
    }
}
