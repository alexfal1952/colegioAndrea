<?php

namespace App\Enums;

enum TipoPeriodoEscolar: string
{
    case Anual = 'anual';
    case Semestral = 'semestral';
    case Trimestral = 'trimestral';

    public function cantidadSubperiodos(): int
    {
        return match ($this) {
            self::Anual => 0,
            self::Semestral => 2,
            self::Trimestral => 3,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Anual => 'Anual',
            self::Semestral => 'Semestral',
            self::Trimestral => 'Trimestral',
        };
    }
}
