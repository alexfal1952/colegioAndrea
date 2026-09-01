<?php

namespace App\Enums;

enum EstadoPeriodoEscolar: string
{
    case Borrador = 'borrador';
    case Activo = 'activo';
    case Cerrado = 'cerrado';

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Activo => 'Activo',
            self::Cerrado => 'Cerrado',
        };
    }

    public function esEditable(): bool
    {
        return $this === self::Borrador || $this === self::Activo;
    }

    public function esEstructuraEditable(): bool
    {
        return $this === self::Borrador;
    }
}
