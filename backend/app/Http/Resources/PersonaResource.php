<?php

namespace App\Http\Resources;

use App\Support\DocumentoIdentidad;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Persona */
class PersonaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_documento' => $this->tipo_documento->value,
            'tipo_documento_label' => $this->tipo_documento->label(),
            'numero_documento' => $this->numero_documento,
            'numero_documento_formateado' => DocumentoIdentidad::formatear($this->tipo_documento, $this->numero_documento),
            'pais_documento' => $this->pais_documento,
            'pais' => $this->whenLoaded('pais', fn () => [
                'codigo' => $this->pais?->codigo,
                'nombre' => $this->pais?->nombre,
            ]),
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'nombre_completo' => $this->nombreCompleto(),
            'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
            'email' => $this->email,
            'telefono' => $this->telefono,
            'activo' => $this->activo,
            'es_alumno' => $this->relationLoaded('alumno') ? (bool) $this->alumno : null,
            'es_profesor' => $this->relationLoaded('profesor') ? (bool) $this->profesor : null,
        ];
    }
}
