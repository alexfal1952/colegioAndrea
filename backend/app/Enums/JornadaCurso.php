<?php

namespace App\Enums;

enum JornadaCurso: string
{
    case Manana = 'manana';
    case Tarde = 'tarde';
    case Completa = 'completa';

    public function label(): string
    {
        return match ($this) {
            self::Manana => 'Mañana',
            self::Tarde => 'Tarde',
            self::Completa => 'Completa',
        };
    }
}
