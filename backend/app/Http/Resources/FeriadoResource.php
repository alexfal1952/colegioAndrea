<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Feriado */
class FeriadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periodo_escolar_id' => $this->periodo_escolar_id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo->value,
            'tipo_label' => $this->tipo->label(),
            'recurrente' => $this->recurrente,
            'fecha' => $this->fecha?->format('Y-m-d'),
            'mes' => $this->mes,
            'dia' => $this->dia,
            'fecha_display' => $this->recurrente
                ? sprintf('%02d/%02d (recurrente)', $this->dia, $this->mes)
                : $this->fecha?->format('d/m/Y'),
            'descripcion' => $this->descripcion,
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
        ];
    }
}
