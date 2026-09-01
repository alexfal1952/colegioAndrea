<?php

namespace App\Enums;

enum EstadoCurso: string
{
    case Activo = 'activo';
    case Inactivo = 'inactivo';
    case Cerrado = 'cerrado';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
            self::Cerrado => 'Cerrado',
        };
    }
}
