<?php

namespace App\Models;

use App\Enums\EstadoAsignatura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignatura extends Model
{
    protected $fillable = [
        'establecimiento_id',
        'codigo',
        'nombre',
        'descripcion',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return ['estado' => EstadoAsignatura::class];
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function cursoAsignaturas(): HasMany
    {
        return $this->hasMany(CursoAsignatura::class);
    }
}
