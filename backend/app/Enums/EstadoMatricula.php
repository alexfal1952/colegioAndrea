<?php

namespace App\Enums;

enum EstadoMatricula: string
{
    case Activa = 'activa';
    case Retirada = 'retirada';
    case Traslado = 'traslado';
    case Egresado = 'egresado';

    public function label(): string
    {
        return match ($this) {
            self::Activa => 'Activa',
            self::Retirada => 'Retirada',
            self::Traslado => 'Traslado',
            self::Egresado => 'Egresado',
        };
    }
}
