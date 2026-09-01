<?php

namespace App\Models;

use App\Enums\AccionAsistenciaAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaAuditoria extends Model
{
    public $timestamps = false;

    protected $table = 'asistencias_auditoria';

    protected $fillable = [
        'asistencia_id',
        'estado_anterior_id',
        'estado_nuevo_id',
        'hora_ingreso_anterior',
        'hora_ingreso_nuevo',
        'observacion_anterior',
        'observacion_nuevo',
        'accion',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'accion' => AccionAsistenciaAuditoria::class,
            'created_at' => 'datetime',
        ];
    }

    public function asistencia(): BelongsTo
    {
        return $this->belongsTo(Asistencia::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
