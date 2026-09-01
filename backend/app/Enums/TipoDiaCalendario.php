<?php

namespace App\Enums;

enum TipoDiaCalendario: string
{
    case Lectivo = 'lectivo';
    case Feriado = 'feriado';
    case Vacacion = 'vacacion';
    case Suspension = 'suspension';
    case FinSemana = 'fin_semana';
    case FueraPeriodo = 'fuera_periodo';

    public function label(): string
    {
        return match ($this) {
            self::Lectivo => 'Día de clases',
            self::Feriado => 'Feriado',
            self::Vacacion => 'Vacaciones',
            self::Suspension => 'Suspensión de clases',
            self::FinSemana => 'Fin de semana',
            self::FueraPeriodo => 'Fuera del período escolar',
        };
    }
}
