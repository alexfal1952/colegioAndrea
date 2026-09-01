<?php

namespace App\Models;

use App\Enums\EstadoCalendario;
use App\Enums\TipoFeriado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feriado extends Model
{
    protected $table = 'feriados';

    protected $fillable = [
        'establecimiento_id',
        'periodo_escolar_id',
        'nombre',
        'tipo',
        'recurrente',
        'fecha',
        'mes',
        'dia',
        'descripcion',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'recurrente' => 'boolean',
            'fecha' => 'date:Y-m-d',
            'mes' => 'integer',
            'dia' => 'integer',
            'tipo' => TipoFeriado::class,
            'estado' => EstadoCalendario::class,
        ];
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function periodoEscolar(): BelongsTo
    {
        return $this->belongsTo(PeriodoEscolar::class, 'periodo_escolar_id');
    }

    public function resolverFecha(int $anio): ?string
    {
        if ($this->recurrente && $this->mes && $this->dia) {
            return sprintf('%04d-%02d-%02d', $anio, $this->mes, $this->dia);
        }

        return $this->fecha?->format('Y-m-d');
    }
}
