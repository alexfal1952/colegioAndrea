<?php

namespace App\Enums;

enum AccionAsistenciaAuditoria: string
{
    case Creacion = 'creacion';
    case Actualizacion = 'actualizacion';

    public function label(): string
    {
        return match ($this) {
            self::Creacion => 'Creación',
            self::Actualizacion => 'Actualización',
        };
    }
}
