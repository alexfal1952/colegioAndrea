<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Matricula */
class MatriculaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periodo_escolar_id' => $this->periodo_escolar_id,
            'curso_id' => $this->curso_id,
            'alumno_id' => $this->alumno_id,
            'numero_matricula' => $this->numero_matricula,
            'fecha_matricula' => $this->fecha_matricula->format('Y-m-d'),
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'observaciones' => $this->observaciones,
            'alumno' => new AlumnoResource($this->whenLoaded('alumno')),
            'curso' => new CursoResource($this->whenLoaded('curso')),
            'periodo_escolar' => $this->whenLoaded('periodoEscolar', fn () => [
                'id' => $this->periodoEscolar->id,
                'nombre' => $this->periodoEscolar->nombre,
                'anio' => $this->periodoEscolar->anio,
            ]),
        ];
    }
}
