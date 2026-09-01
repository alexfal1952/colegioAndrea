<?php

namespace App\Enums;

enum EstadoAsignatura: string
{
    case Activa = 'activa';
    case Inactiva = 'inactiva';

    public function label(): string
    {
        return match ($this) {
            self::Activa => 'Activa',
            self::Inactiva => 'Inactiva',
        };
    }
}
