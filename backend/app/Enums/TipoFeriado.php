<?php

namespace App\Enums;

enum TipoFeriado: string
{
    case Nacional = 'nacional';
    case Regional = 'regional';
    case Comunal = 'comunal';
    case Escolar = 'escolar';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Nacional => 'Feriado nacional',
            self::Regional => 'Feriado regional',
            self::Comunal => 'Feriado comunal',
            self::Escolar => 'Feriado escolar',
            self::Otro => 'Otro',
        };
    }
}
