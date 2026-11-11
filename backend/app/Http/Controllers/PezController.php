<?php

namespace App\Http\Controllers;

use App\Models\Pez;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PezController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Pez::query()->orderBy('nombre')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $pez = Pez::create($this->validatePez($request));

        return response()->json($pez, 201);
    }

    public function show(Pez $pez): JsonResponse
    {
        return response()->json($pez);
    }

    public function update(Request $request, Pez $pez): JsonResponse
    {
        $pez->update($this->validatePez($request, $pez));

        return response()->json($pez->fresh());
    }

    public function destroy(Pez $pez): JsonResponse
    {
        $pez->delete();

        return response()->json([
            'message' => 'Pez eliminado correctamente.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePez(Request $request, ?Pez $pez = null): array
    {
        return $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:30',
                Rule::unique('peces', 'codigo')->ignore($pez ? $pez->id : null),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'especie' => ['required', 'string', 'max:100'],
            'tipo_agua' => ['required', Rule::in(['dulce', 'salada'])],
            'longitud_cm' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ya existe un pez con este código.',
            'nombre.required' => 'El nombre es obligatorio.',
            'especie.required' => 'La especie es obligatoria.',
            'tipo_agua.required' => 'El tipo de agua es obligatorio.',
            'longitud_cm.min' => 'La longitud no puede ser negativa.',
        ]);
    }
}
