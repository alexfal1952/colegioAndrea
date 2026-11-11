<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnimalController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Animal::query()->orderBy('nombre')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $animal = Animal::create($this->validateAnimal($request));

        return response()->json($animal, 201);
    }

    public function show(Animal $animal): JsonResponse
    {
        return response()->json($animal);
    }

    public function update(Request $request, Animal $animal): JsonResponse
    {
        $animal->update($this->validateAnimal($request, $animal));

        return response()->json($animal->fresh());
    }

    public function destroy(Animal $animal): JsonResponse
    {
        $animal->delete();

        return response()->json([
            'message' => 'Animal eliminado correctamente.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAnimal(Request $request, ?Animal $animal = null): array
    {
        return $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:30',
                Rule::unique('animales', 'codigo')->ignore($animal ? $animal->id : null),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'especie' => ['required', 'string', 'max:80'],
            'raza' => ['nullable', 'string', 'max:100'],
            'sexo' => ['required', Rule::in(['macho', 'hembra', 'desconocido'])],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'peso_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'estado' => ['required', Rule::in(['activo', 'adoptado', 'en_tratamiento', 'inactivo'])],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ya existe un animal con este código.',
            'nombre.required' => 'El nombre es obligatorio.',
            'especie.required' => 'La especie es obligatoria.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'peso_kg.min' => 'El peso no puede ser negativo.',
        ]);
    }
}
