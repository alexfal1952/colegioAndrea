<?php

namespace App\Models;

use App\Enums\EstadoAsignatura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CursoAsignatura extends Model
{
    protected $table = 'curso_asignaturas';

    protected $fillable = [
        'curso_id',
        'asignatura_id',
        'estado',
        'horas_semanales',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoAsignatura::class,
            'horas_semanales' => 'integer',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class);
    }

    public function asignacionesDocente(): HasMany
    {
        return $this->hasMany(AsignacionDocente::class);
    }

    public function asignacionesActivas(): HasMany
    {
        return $this->asignacionesDocente()->where('estado', 'activa');
    }
}
