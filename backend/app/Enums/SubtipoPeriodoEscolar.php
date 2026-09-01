<?php

namespace App\Enums;

enum SubtipoPeriodoEscolar: string
{
    case Semestre = 'semestre';
    case Trimestre = 'trimestre';

    public function label(int $numero): string
    {
        return match ($this) {
            self::Semestre => "Semestre {$numero}",
            self::Trimestre => "Trimestre {$numero}",
        };
    }
}
