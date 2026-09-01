<?php

namespace App\Models;

use App\Enums\EstadoPeriodoEscolar;
use App\Enums\SubtipoPeriodoEscolar;
use App\Enums\TipoPeriodoEscolar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoEscolar extends Model
{
    protected $table = 'periodos_escolares';

    protected $fillable = [
        'establecimiento_id',
        'parent_id',
        'nombre',
        'anio',
        'tipo',
        'subtipo',
        'numero_subperiodo',
        'fecha_inicio',
        'fecha_termino',
        'estado',
        'descripcion',
        'created_by',
        'updated_by',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'numero_subperiodo' => 'integer',
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_termino' => 'date:Y-m-d',
            'tipo' => TipoPeriodoEscolar::class,
            'subtipo' => SubtipoPeriodoEscolar::class,
            'estado' => EstadoPeriodoEscolar::class,
            'closed_at' => 'datetime',
        ];
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function subperiodos(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('numero_subperiodo');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'periodo_escolar_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'periodo_escolar_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeRaices(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function esRaiz(): bool
    {
        return $this->parent_id === null;
    }

    public function estaCerrado(): bool
    {
        return $this->estado === EstadoPeriodoEscolar::Cerrado;
    }
}
