<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JustificacionAsistencia extends Model
{
    protected $table = 'justificaciones_asistencia';

    protected $fillable = [
        'motivo',
        'descripcion',
        'documento_path',
        'estado',
        'created_by',
        'updated_by',
    ];

    public function asistencia(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'justificacion_id');
    }
}
