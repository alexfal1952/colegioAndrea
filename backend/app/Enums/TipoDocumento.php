<?php

namespace App\Enums;

enum TipoDocumento: string
{
    case Rut = 'rut';
    case Pasaporte = 'pasaporte';
    case Dni = 'dni';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Rut => 'RUT',
            self::Pasaporte => 'Pasaporte',
            self::Dni => 'DNI',
            self::Otro => 'Otro',
        };
    }

    public function requierePais(): bool
    {
        return $this !== self::Rut;
    }
}
