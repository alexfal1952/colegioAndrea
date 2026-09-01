<?php

namespace App\Enums;

enum EstadoAsignacionDocente: string
{
    case Activa = 'activa';
    case Inactiva = 'inactiva';
    case Finalizada = 'finalizada';

    public function label(): string
    {
        return match ($this) {
            self::Activa => 'Activa',
            self::Inactiva => 'Inactiva',
            self::Finalizada => 'Finalizada',
        };
    }
}
