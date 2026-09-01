<?php

namespace App\Enums;

enum RolAsignacionDocente: string
{
    case Titular = 'titular';
    case Reemplazante = 'reemplazante';
    case Apoyo = 'apoyo';
    case CoDocente = 'co-docente';

    public function label(): string
    {
        return match ($this) {
            self::Titular => 'Titular',
            self::Reemplazante => 'Reemplazante',
            self::Apoyo => 'Apoyo',
            self::CoDocente => 'Co-docente',
        };
    }
}
