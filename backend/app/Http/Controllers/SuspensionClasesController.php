<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuspensionClasesResource;
use App\Models\SuspensionClases;
use App\Services\SuspensionClasesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class SuspensionClasesController extends Controller
{
    public function __construct(private readonly SuspensionClasesService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return SuspensionClasesResource::collection(
            $this->service->listar($request->only(['periodo_escolar_id', 'curso_id', 'per_page']))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['required', 'integer', 'exists:periodos_escolares,id'],
            'curso_id' => ['nullable', 'integer', 'exists:cursos,id'],
            'fecha' => ['required', 'date'],
            'nombre' => ['required', 'string', 'max:255'],
            'motivo' => ['nullable', 'string'],
            'tipo' => ['sometimes', Rule::in(['total', 'parcial'])],
            'estado' => ['sometimes', Rule::in(['activa', 'inactiva'])],
        ]);

        $suspension = $this->service->crear($data, $request->user());

        return (new SuspensionClasesResource($suspension))->response()->setStatusCode(201);
    }

    public function update(Request $request, SuspensionClases $suspensionClase): SuspensionClasesResource
    {
        $data = $request->validate([
            'curso_id' => ['nullable', 'integer', 'exists:cursos,id'],
            'fecha' => ['sometimes', 'date'],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'motivo' => ['nullable', 'string'],
            'tipo' => ['sometimes', Rule::in(['total', 'parcial'])],
            'estado' => ['sometimes', Rule::in(['activa', 'inactiva'])],
        ]);

        return new SuspensionClasesResource($this->service->actualizar($suspensionClase, $data, $request->user()));
    }

    public function destroy(SuspensionClases $suspensionClase, Request $request): SuspensionClasesResource
    {
        return new SuspensionClasesResource($this->service->eliminar($suspensionClase, $request->user()));
    }
}
