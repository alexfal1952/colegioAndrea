<?php

namespace App\Enums;

enum TipoSuspensionClases: string
{
    case Total = 'total';
    case Parcial = 'parcial';

    public function label(): string
    {
        return match ($this) {
            self::Total => 'Suspensión total',
            self::Parcial => 'Suspensión parcial',
        };
    }
}
