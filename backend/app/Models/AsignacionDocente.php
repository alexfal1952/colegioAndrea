<?php

namespace App\Models;

use App\Enums\EstadoAsignacionDocente;
use App\Enums\RolAsignacionDocente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionDocente extends Model
{
    protected $table = 'asignaciones_docente';

    protected $fillable = [
        'curso_asignatura_id',
        'profesor_id',
        'rol',
        'estado',
        'fecha_inicio',
        'fecha_termino',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'rol' => RolAsignacionDocente::class,
            'estado' => EstadoAsignacionDocente::class,
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_termino' => 'date:Y-m-d',
        ];
    }

    public function cursoAsignatura(): BelongsTo
    {
        return $this->belongsTo(CursoAsignatura::class);
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }
}
