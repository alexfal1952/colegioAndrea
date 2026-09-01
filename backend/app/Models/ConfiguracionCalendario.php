<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionCalendario extends Model
{
    protected $table = 'configuracion_calendario';

    protected $fillable = [
        'establecimiento_id',
        'periodo_escolar_id',
        'sabado_lectivo',
        'domingo_lectivo',
        'permitir_asistencia_en_feriado',
        'permitir_asistencia_en_vacaciones',
        'permitir_asistencia_en_suspension',
        'permitir_asistencia_fin_semana',
        'dia_sin_registro_es_ausente',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'sabado_lectivo' => 'boolean',
            'domingo_lectivo' => 'boolean',
            'permitir_asistencia_en_feriado' => 'boolean',
            'permitir_asistencia_en_vacaciones' => 'boolean',
            'permitir_asistencia_en_suspension' => 'boolean',
            'permitir_asistencia_fin_semana' => 'boolean',
            'dia_sin_registro_es_ausente' => 'boolean',
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
}
