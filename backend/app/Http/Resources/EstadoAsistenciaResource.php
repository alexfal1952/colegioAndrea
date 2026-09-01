<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\EstadoAsistencia */
class EstadoAsistenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'cuenta_como_presente' => $this->cuenta_como_presente,
            'cuenta_como_ausencia' => $this->cuenta_como_ausencia,
            'cuenta_como_atraso' => $this->cuenta_como_atraso,
            'cuenta_como_justificado' => $this->cuenta_como_justificado,
            'color' => $this->color,
            'orden' => $this->orden,
            'activo' => $this->activo,
            'es_sistema' => $this->es_sistema,
        ];
    }
}
