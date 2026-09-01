<?php

namespace App\Support;

use App\Enums\TipoDocumento;
use InvalidArgumentException;

class DocumentoIdentidad
{
    public static function normalizarNumero(TipoDocumento $tipo, string $numero): string
    {
        $limpio = strtoupper(trim($numero));

        if ($tipo === TipoDocumento::Rut) {
            return self::normalizarRut($limpio);
        }

        return preg_replace('/\s+/', '', $limpio) ?? $limpio;
    }

    public static function normalizarPais(TipoDocumento $tipo, ?string $pais): ?string
    {
        if ($tipo === TipoDocumento::Rut) {
            return 'CL';
        }

        return $pais ? strtoupper(trim($pais)) : null;
    }

    public static function validarRut(string $rutNormalizado): bool
    {
        if (! preg_match('/^(\d{7,8})([\dK])$/', $rutNormalizado, $matches)) {
            return false;
        }

        $cuerpo = $matches[1];
        $dvIngresado = $matches[2];
        $suma = 0;
        $multiplo = 2;

        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += (int) $cuerpo[$i] * $multiplo;
            $multiplo = $multiplo === 7 ? 2 : $multiplo + 1;
        }

        $resto = 11 - ($suma % 11);
        $dvCalculado = match ($resto) {
            11 => '0',
            10 => 'K',
            default => (string) $resto,
        };

        return $dvIngresado === $dvCalculado;
    }

    public static function formatear(TipoDocumento $tipo, string $numeroNormalizado): string
    {
        if ($tipo !== TipoDocumento::Rut || ! preg_match('/^(\d+)([\dK])$/', $numeroNormalizado, $matches)) {
            return $numeroNormalizado;
        }

        $cuerpo = number_format((int) $matches[1], 0, '', '.');
        return $cuerpo.'-'.$matches[2];
    }

    private static function normalizarRut(string $rut): string
    {
        $rut = preg_replace('/[^0-9kK]/', '', $rut) ?? '';

        if ($rut === '') {
            throw new InvalidArgumentException('RUT inválido.');
        }

        $dv = strtoupper(substr($rut, -1));
        $cuerpo = substr($rut, 0, -1);

        if ($cuerpo === '') {
            throw new InvalidArgumentException('RUT inválido.');
        }

        return $cuerpo.$dv;
    }
}
