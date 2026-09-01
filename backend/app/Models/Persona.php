<?php

namespace App\Models;

use App\Enums\TipoDocumento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $fillable = [
        'establecimiento_id',
        'tipo_documento',
        'numero_documento',
        'pais_documento',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'email',
        'telefono',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tipo_documento' => TipoDocumento::class,
            'fecha_nacimiento' => 'date:Y-m-d',
            'activo' => 'boolean',
        ];
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_documento', 'codigo');
    }

    public function alumno(): HasOne
    {
        return $this->hasOne(Alumno::class);
    }

    public function profesor(): HasOne
    {
        return $this->hasOne(Profesor::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function nombreCompleto(): string
    {
        return trim(collect([
            $this->nombres,
            $this->apellido_paterno,
            $this->apellido_materno,
        ])->filter()->implode(' '));
    }
}
