<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Resources\PersonaResource;
use App\Models\Persona;
use App\Services\PersonaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PersonaController extends Controller
{
    public function __construct(private readonly PersonaService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return PersonaResource::collection(
            $this->service->listar($request->only(['q', 'tipo_documento', 'per_page']))
        );
    }

    public function store(StorePersonaRequest $request): JsonResponse
    {
        $persona = $this->service->crear($request->validated(), $request->user());

        return (new PersonaResource($persona->load(['pais', 'alumno', 'profesor'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $persona): PersonaResource
    {
        return new PersonaResource($this->service->obtener($persona));
    }

    public function update(StorePersonaRequest $request, Persona $persona): PersonaResource
    {
        return new PersonaResource(
            $this->service->actualizar($persona, $request->validated(), $request->user())
        );
    }
}
