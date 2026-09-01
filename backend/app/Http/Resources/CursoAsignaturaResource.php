<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CursoAsignatura */
class CursoAsignaturaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'curso_id' => $this->curso_id,
            'asignatura_id' => $this->asignatura_id,
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'horas_semanales' => $this->horas_semanales,
            'asignatura' => new AsignaturaResource($this->whenLoaded('asignatura')),
            'docentes' => AsignacionDocenteResource::collection($this->whenLoaded('asignacionesDocente')),
            'profesores_label' => $this->whenLoaded('asignacionesDocente', function () {
                return $this->asignacionesDocente
                    ->filter(fn ($d) => $d->estado->value === 'activa')
                    ->map(fn ($d) => $d->profesor?->persona?->nombreCompleto())
                    ->filter()
                    ->values()
                    ->implode(', ');
            }),
        ];
    }
}
