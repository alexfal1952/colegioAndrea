<?php

namespace App\Models;

use App\Enums\EstadoCurso;
use App\Enums\JornadaCurso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $fillable = [
        'establecimiento_id',
        'periodo_escolar_id',
        'nivel_id',
        'paralelo',
        'nombre',
        'jornada',
        'estado',
        'cupo_maximo',
        'profesor_jefe_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'cupo_maximo' => 'integer',
            'jornada' => JornadaCurso::class,
            'estado' => EstadoCurso::class,
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

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class);
    }

    public function profesorJefe(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_jefe_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function cursoAsignaturas(): HasMany
    {
        return $this->hasMany(CursoAsignatura::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function nombreDisplay(): string
    {
        if ($this->nombre) {
            return $this->nombre;
        }

        $nivel = $this->relationLoaded('nivel') ? $this->nivel->nombre : '';

        return trim("{$nivel} {$this->paralelo}");
    }
}
