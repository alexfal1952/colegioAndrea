<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoAsistencia extends Model
{
    protected $table = 'estados_asistencia';

    protected $fillable = [
        'establecimiento_id',
        'codigo',
        'nombre',
        'descripcion',
        'cuenta_como_presente',
        'cuenta_como_ausencia',
        'cuenta_como_atraso',
        'cuenta_como_justificado',
        'color',
        'orden',
        'activo',
        'es_sistema',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'cuenta_como_presente' => 'boolean',
            'cuenta_como_ausencia' => 'boolean',
            'cuenta_como_atraso' => 'boolean',
            'cuenta_como_justificado' => 'boolean',
            'activo' => 'boolean',
            'es_sistema' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'estado_asistencia_id');
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }
}
