<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Resources\AlumnoResource;
use App\Http\Resources\MatriculaResource;
use App\Models\Alumno;
use App\Services\AlumnoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlumnoController extends Controller
{
    public function __construct(private readonly AlumnoService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AlumnoResource::collection(
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

        $alumno = $this->service->crear($payload, $request->user());

        return (new AlumnoResource($alumno))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $alumno): AlumnoResource
    {
        return new AlumnoResource($this->service->obtener($alumno));
    }

    public function update(Request $request, Alumno $alumno): AlumnoResource
    {
        $data = $request->validate([
            'codigo_interno' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
            'persona' => ['sometimes', 'array'],
        ]);

        if (isset($data['persona'])) {
            $personaRequest = StorePersonaRequest::createFrom($request->duplicate(['persona' => null] + $data['persona']));
            $personaRequest->setContainer(app())->validateResolved();
            $data['persona'] = $personaRequest->validated();
        }

        return new AlumnoResource(
            $this->service->actualizar($alumno, $data, $request->user())
        );
    }

    public function historial(Alumno $alumno): AnonymousResourceCollection
    {
        return MatriculaResource::collection($this->service->historial($alumno));
    }
}
