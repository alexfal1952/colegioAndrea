<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\SuspensionClases */
class SuspensionClasesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periodo_escolar_id' => $this->periodo_escolar_id,
            'curso_id' => $this->curso_id,
            'fecha' => $this->fecha->format('Y-m-d'),
            'nombre' => $this->nombre,
            'motivo' => $this->motivo,
            'tipo' => $this->tipo->value,
            'tipo_label' => $this->tipo->label(),
            'alcance' => $this->curso_id ? 'curso' : 'establecimiento',
            'alcance_label' => $this->curso_id ? 'Curso específico' : 'Todo el establecimiento',
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'curso' => new CursoResource($this->whenLoaded('curso')),
        ];
    }
}
