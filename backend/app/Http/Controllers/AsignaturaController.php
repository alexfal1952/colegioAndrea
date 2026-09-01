<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsignaturaResource;
use App\Models\Asignatura;
use App\Services\AsignaturaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AsignaturaController extends Controller
{
    public function __construct(private readonly AsignaturaService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AsignaturaResource::collection(
            $this->service->listar($request->only(['q', 'estado', 'per_page']))
        );
    }

    public function activas(): AnonymousResourceCollection
    {
        return AsignaturaResource::collection($this->service->listarActivas());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['sometimes', Rule::in(['activa', 'inactiva'])],
        ]);

        $asignatura = $this->service->crear($data, $request->user());

        return (new AsignaturaResource($asignatura))->response()->setStatusCode(201);
    }

    public function update(Request $request, Asignatura $asignatura): AsignaturaResource
    {
        $data = $request->validate([
            'codigo' => ['sometimes', 'string', 'max:20'],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['sometimes', Rule::in(['activa', 'inactiva'])],
        ]);

        return new AsignaturaResource($this->service->actualizar($asignatura, $data, $request->user()));
    }
}
