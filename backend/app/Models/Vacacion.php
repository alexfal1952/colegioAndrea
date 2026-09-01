<?php

namespace App\Models;

use App\Enums\EstadoVacacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacacion extends Model
{
    protected $table = 'vacaciones';

    protected $fillable = [
        'establecimiento_id',
        'periodo_escolar_id',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
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

    public function contieneFecha(string $fecha): bool
    {
        return $fecha >= $this->fecha_inicio->format('Y-m-d')
            && $fecha <= $this->fecha_fin->format('Y-m-d');
    }
}
