<?php

namespace App\Http\Requests;

use App\Enums\EstadoPeriodoEscolar;
use App\Enums\TipoPeriodoEscolar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePeriodoEscolarRequest extends FormRequest
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
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'anio' => ['sometimes', 'required', 'integer', 'min:2000', 'max:2100'],
            'tipo' => ['sometimes', 'required', Rule::enum(TipoPeriodoEscolar::class)],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_termino' => ['sometimes', 'required', 'date'],
            'estado' => ['sometimes', Rule::enum(EstadoPeriodoEscolar::class)],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'subperiodos' => ['nullable', 'array'],
            'subperiodos.*.nombre' => ['required_with:subperiodos', 'string', 'max:255'],
            'subperiodos.*.numero' => ['required_with:subperiodos', 'integer', 'min:1', 'max:3'],
            'subperiodos.*.fecha_inicio' => ['required_with:subperiodos', 'date'],
            'subperiodos.*.fecha_termino' => ['required_with:subperiodos', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del período es obligatorio.',
            'fecha_termino.after_or_equal' => 'La fecha de término debe ser posterior o igual a la fecha de inicio.',
        ];
    }
}
