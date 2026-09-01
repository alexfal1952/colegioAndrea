<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Vacacion */
class VacacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periodo_escolar_id' => $this->periodo_escolar_id,
            'nombre' => $this->nombre,
            'fecha_inicio' => $this->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $this->fecha_fin->format('Y-m-d'),
            'descripcion' => $this->descripcion,
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'periodo_escolar' => $this->whenLoaded('periodoEscolar', fn () => [
                'id' => $this->periodoEscolar->id,
                'nombre' => $this->periodoEscolar->nombre,
                'anio' => $this->periodoEscolar->anio,
            ]),
        ];
    }
}
