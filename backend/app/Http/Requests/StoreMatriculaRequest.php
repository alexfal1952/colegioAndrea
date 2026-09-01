<?php

namespace App\Http\Requests;

use App\Enums\EstadoMatricula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMatriculaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'periodo_escolar_id' => ['required', 'integer', 'exists:periodos_escolares,id'],
            'curso_id' => ['required', 'integer', 'exists:cursos,id'],
            'alumno_id' => ['required', 'integer', 'exists:alumnos,id'],
            'numero_matricula' => ['nullable', 'string', 'max:50'],
            'fecha_matricula' => ['nullable', 'date'],
            'estado' => ['sometimes', Rule::enum(EstadoMatricula::class)],
            'observaciones' => ['nullable', 'string'],
        ];
    }
}
