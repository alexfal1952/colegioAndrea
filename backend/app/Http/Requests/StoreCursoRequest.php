<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCursoRequest extends FormRequest
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
            'nivel_id' => ['required', 'integer', 'exists:niveles,id'],
            'paralelo' => ['required', 'string', 'max:10'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1'],
            'profesor_jefe_id' => ['nullable', 'integer', 'exists:profesores,id'],
            'jornada' => ['nullable', 'string', 'in:manana,tarde,completa'],
            'estado' => ['sometimes', 'string', 'in:activo,inactivo,cerrado'],
        ];
    }
}
