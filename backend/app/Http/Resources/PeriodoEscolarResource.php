<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PeriodoEscolar */
class PeriodoEscolarResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'anio' => $this->anio,
            'tipo' => $this->tipo->value,
            'tipo_label' => $this->tipo->label(),
            'subtipo' => $this->subtipo?->value,
            'numero_subperiodo' => $this->numero_subperiodo,
            'fecha_inicio' => $this->fecha_inicio->format('Y-m-d'),
            'fecha_termino' => $this->fecha_termino->format('Y-m-d'),
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->label(),
            'descripcion' => $this->descripcion,
            'subperiodos' => self::collection($this->whenLoaded('subperiodos')),
            'subperiodos_count' => $this->whenCounted('subperiodos'),
            'auditoria' => [
                'created_by' => $this->whenLoaded('creador', fn () => [
                    'id' => $this->creador->id,
                    'name' => $this->creador->name,
                    'email' => $this->creador->email ?? null,
                ]),
                'updated_by' => $this->whenLoaded('editor', fn () => $this->editor ? [
                    'id' => $this->editor->id,
                    'name' => $this->editor->name,
                    'email' => $this->editor->email ?? null,
                ] : null),
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
                'closed_at' => $this->closed_at?->toIso8601String(),
            ],
        ];
    }
}
