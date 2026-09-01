<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Curso */
class CursoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periodo_escolar_id' => $this->periodo_escolar_id,
            'nivel_id' => $this->nivel_id,
            'paralelo' => $this->paralelo,
            'nombre' => $this->nombre,
            'nombre_display' => $this->nombreDisplay(),
            'jornada' => $this->jornada?->value,
            'jornada_label' => $this->jornada?->label(),
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'cupo_maximo' => $this->cupo_maximo,
            'profesor_jefe_id' => $this->profesor_jefe_id,
            'nivel' => $this->whenLoaded('nivel', fn () => [
                'id' => $this->nivel->id,
                'nombre' => $this->nivel->nombre,
                'codigo' => $this->nivel->codigo,
            ]),
            'periodo_escolar' => $this->whenLoaded('periodoEscolar', fn () => [
                'id' => $this->periodoEscolar->id,
                'nombre' => $this->periodoEscolar->nombre,
                'anio' => $this->periodoEscolar->anio,
            ]),
            'profesor_jefe' => new ProfesorResource($this->whenLoaded('profesorJefe')),
            'matriculas_count' => $this->whenCounted('matriculas'),
            'matriculas' => MatriculaResource::collection($this->whenLoaded('matriculas')),
            'asignaturas' => CursoAsignaturaResource::collection($this->whenLoaded('cursoAsignaturas')),
        ];
    }
}
