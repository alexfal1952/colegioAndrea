<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asistencia extends Model
{
    protected $table = 'asistencias';

    protected $fillable = [
        'establecimiento_id',
        'periodo_escolar_id',
        'curso_id',
        'matricula_id',
        'fecha',
        'estado_asistencia_id',
        'turno',
        'hora_ingreso',
        'hora_salida',
        'observacion',
        'justificacion_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
        ];
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function periodoEscolar(): BelongsTo
    {
        return $this->belongsTo(PeriodoEscolar::class, 'periodo_escolar_id');
    }

    public function estadoAsistencia(): BelongsTo
    {
        return $this->belongsTo(EstadoAsistencia::class, 'estado_asistencia_id');
    }

    public function justificacion(): BelongsTo
    {
        return $this->belongsTo(JustificacionAsistencia::class, 'justificacion_id');
    }

    public function auditoria(): HasMany
    {
        return $this->hasMany(AsistenciaAuditoria::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
