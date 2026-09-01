<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AsignacionDocente */
class AsignacionDocenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'profesor_id' => $this->profesor_id,
            'rol' => $this->rol->value,
            'rol_label' => $this->rol->label(),
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'fecha_inicio' => $this->fecha_inicio->format('Y-m-d'),
            'fecha_termino' => $this->fecha_termino?->format('Y-m-d'),
            'observaciones' => $this->observaciones,
            'profesor' => new ProfesorResource($this->whenLoaded('profesor')),
        ];
    }
}
