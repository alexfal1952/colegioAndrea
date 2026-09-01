<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Alumno */
class AlumnoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_interno' => $this->codigo_interno,
            'observaciones' => $this->observaciones,
            'activo' => $this->activo,
            'persona' => new PersonaResource($this->whenLoaded('persona')),
            'matriculas' => MatriculaResource::collection($this->whenLoaded('matriculas')),
        ];
    }
}
