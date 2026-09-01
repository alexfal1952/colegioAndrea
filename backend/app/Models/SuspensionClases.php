<?php

namespace App\Models;

use App\Enums\EstadoVacacion;
use App\Enums\TipoSuspensionClases;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuspensionClases extends Model
{
    protected $table = 'suspensiones_clases';

    protected $fillable = [
        'establecimiento_id',
        'periodo_escolar_id',
        'curso_id',
        'fecha',
        'nombre',
        'motivo',
        'tipo',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
            'tipo' => TipoSuspensionClases::class,
            'estado' => EstadoVacacion::class,
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

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
}
