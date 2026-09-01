<?php

namespace App\Http\Controllers;

use App\Http\Resources\EstadoAsistenciaResource;
use App\Models\EstadoAsistencia;
use App\Services\EstadoAsistenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EstadoAsistenciaController extends Controller
{
    public function __construct(private readonly EstadoAsistenciaService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        if ($request->boolean('activos')) {
            return EstadoAsistenciaResource::collection($this->service->listarActivos());
        }

        return EstadoAsistenciaResource::collection($this->service->listar($request->only(['per_page'])));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:30'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'cuenta_como_presente' => ['sometimes', 'boolean'],
            'cuenta_como_ausencia' => ['sometimes', 'boolean'],
            'cuenta_como_atraso' => ['sometimes', 'boolean'],
            'cuenta_como_justificado' => ['sometimes', 'boolean'],
            'color' => ['nullable', 'string', 'max:20'],
            'orden' => ['sometimes', 'integer'],
        ]);

        $estado = $this->service->crear($data, $request->user());

        return (new EstadoAsistenciaResource($estado))->response()->setStatusCode(201);
    }

    public function update(Request $request, EstadoAsistencia $estadoAsistencia): EstadoAsistenciaResource
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'cuenta_como_presente' => ['sometimes', 'boolean'],
            'cuenta_como_ausencia' => ['sometimes', 'boolean'],
            'cuenta_como_atraso' => ['sometimes', 'boolean'],
            'cuenta_como_justificado' => ['sometimes', 'boolean'],
            'color' => ['nullable', 'string', 'max:20'],
            'orden' => ['sometimes', 'integer'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        return new EstadoAsistenciaResource($this->service->actualizar($estadoAsistencia, $data, $request->user()));
    }
}
