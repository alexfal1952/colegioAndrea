<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Resources\ProfesorResource;
use App\Models\Profesor;
use App\Services\ProfesorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfesorController extends Controller
{
    public function __construct(private readonly ProfesorService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ProfesorResource::collection(
            $this->service->listar($request->only(['q', 'per_page']))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $base = $request->validate([
            'persona_id' => ['nullable', 'integer', 'exists:personas,id'],
            'codigo_interno' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $payload = $base;

        if ($request->filled('persona_id')) {
            $payload['persona_id'] = (int) $request->input('persona_id');
        } else {
            $personaRequest = StorePersonaRequest::createFrom($request);
            $personaRequest->setContainer(app())->validateResolved();
            $payload['persona'] = $personaRequest->validated();
        }

        $profesor = $this->service->crear($payload, $request->user());

        return (new ProfesorResource($profesor))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $profesor): ProfesorResource
    {
        return new ProfesorResource($this->service->obtener($profesor));
    }

    public function update(Request $request, Profesor $profesor): ProfesorResource
    {
        $data = $request->validate([
            'codigo_interno' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        return new ProfesorResource(
            $this->service->actualizar($profesor, $data, $request->user())
        );
    }
}
