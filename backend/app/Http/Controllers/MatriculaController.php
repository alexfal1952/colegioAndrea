<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatriculaRequest;
use App\Http\Resources\CursoResource;
use App\Http\Resources\MatriculaResource;
use App\Models\Matricula;
use App\Models\PeriodoEscolar;
use App\Services\CursoService;
use App\Services\MatriculaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MatriculaController extends Controller
{
    public function __construct(
        private readonly MatriculaService $service,
        private readonly CursoService $cursoService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return MatriculaResource::collection(
            $this->service->listar($request->only(['periodo_escolar_id', 'curso_id', 'alumno_id', 'per_page']))
        );
    }

    public function store(StoreMatriculaRequest $request): JsonResponse
    {
        $matricula = $this->service->crear($request->validated(), $request->user());

        return (new MatriculaResource($matricula))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $matricula): MatriculaResource
    {
        return new MatriculaResource($this->service->obtener($matricula));
    }

    public function update(Request $request, Matricula $matricula): MatriculaResource
    {
        $data = $request->validate([
            'curso_id' => ['sometimes', 'integer', 'exists:cursos,id'],
            'numero_matricula' => ['nullable', 'string', 'max:50'],
            'fecha_matricula' => ['sometimes', 'date'],
            'estado' => ['sometimes', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        return new MatriculaResource(
            $this->service->actualizar($matricula, $data, $request->user())
        );
    }

    public function cursosPorPeriodo(PeriodoEscolar $periodoEscolar): AnonymousResourceCollection
    {
        return CursoResource::collection(
            $this->cursoService->listarPorPeriodo($periodoEscolar->id)
        );
    }
}
