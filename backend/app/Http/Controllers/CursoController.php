<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoRequest;
use App\Http\Resources\CursoResource;
use App\Models\Curso;
use App\Services\CursoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CursoController extends Controller
{
    public function __construct(private readonly CursoService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return CursoResource::collection(
            $this->service->listar($request->only(['periodo_escolar_id', 'nivel_id', 'per_page']))
        );
    }

    public function store(StoreCursoRequest $request): JsonResponse
    {
        $curso = $this->service->crear($request->validated(), $request->user());

        return (new CursoResource($curso))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $curso): CursoResource
    {
        return new CursoResource($this->service->obtener($curso));
    }

    public function update(Request $request, Curso $curso): CursoResource
    {
        $data = $request->validate([
            'paralelo' => ['sometimes', 'string', 'max:10'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1'],
            'profesor_jefe_id' => ['nullable', 'integer', 'exists:profesores,id'],
            'jornada' => ['sometimes', 'nullable', 'string', 'in:manana,tarde,completa'],
            'estado' => ['sometimes', 'string', 'in:activo,inactivo,cerrado'],
        ]);

        return new CursoResource(
            $this->service->actualizar($curso, $data, $request->user())
        );
    }

    public function destroy(Curso $curso): JsonResponse
    {
        $this->service->eliminar($curso);

        return response()->json(['message' => 'Curso eliminado correctamente.']);
    }
}
