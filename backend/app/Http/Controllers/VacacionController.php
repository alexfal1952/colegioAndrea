<?php

namespace App\Http\Controllers;

use App\Http\Resources\VacacionResource;
use App\Models\Vacacion;
use App\Services\VacacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class VacacionController extends Controller
{
    public function __construct(private readonly VacacionService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return VacacionResource::collection($this->service->listar($request->only(['periodo_escolar_id', 'per_page'])));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['required', 'integer', 'exists:periodos_escolares,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['sometimes', Rule::in(['activa', 'inactiva'])],
        ]);

        $vacacion = $this->service->crear($data, $request->user());

        return (new VacacionResource($vacacion))->response()->setStatusCode(201);
    }

    public function update(Request $request, Vacacion $vacacion): VacacionResource
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin' => ['sometimes', 'date'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['sometimes', Rule::in(['activa', 'inactiva'])],
        ]);

        return new VacacionResource($this->service->actualizar($vacacion, $data, $request->user()));
    }

    public function destroy(Vacacion $vacacion, Request $request): VacacionResource
    {
        return new VacacionResource($this->service->eliminar($vacacion, $request->user()));
    }
}
