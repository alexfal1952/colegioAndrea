<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Asistencia */
class AsistenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periodo_escolar_id' => $this->periodo_escolar_id,
            'curso_id' => $this->curso_id,
            'matricula_id' => $this->matricula_id,
            'fecha' => $this->fecha->format('Y-m-d'),
            'turno' => $this->turno,
            'hora_ingreso' => $this->hora_ingreso,
            'hora_salida' => $this->hora_salida,
            'observacion' => $this->observacion,
            'estado_asistencia' => new EstadoAsistenciaResource($this->whenLoaded('estadoAsistencia')),
            'matricula' => new MatriculaResource($this->whenLoaded('matricula')),
        ];
    }
}
