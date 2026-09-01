<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePeriodoEscolarRequest;
use App\Http\Requests\UpdatePeriodoEscolarRequest;
use App\Http\Resources\PeriodoEscolarResource;
use App\Models\PeriodoEscolar;
use App\Services\PeriodoEscolarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PeriodoEscolarController extends Controller
{
    public function __construct(
        private readonly PeriodoEscolarService $service,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $periodos = $this->service->listar($request->only(['anio', 'tipo', 'estado', 'sort', 'per_page']));

        return PeriodoEscolarResource::collection($periodos);
    }

    public function store(StorePeriodoEscolarRequest $request): JsonResponse
    {
        $periodo = $this->service->crear($request->validated(), $request->user());

        return (new PeriodoEscolarResource($periodo))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $periodoEscolar): PeriodoEscolarResource
    {
        return new PeriodoEscolarResource($this->service->obtener($periodoEscolar));
    }

    public function update(UpdatePeriodoEscolarRequest $request, PeriodoEscolar $periodoEscolar): PeriodoEscolarResource
    {
        $periodo = $this->service->actualizar($periodoEscolar, $request->validated(), $request->user());

        return new PeriodoEscolarResource($periodo);
    }

    public function activar(Request $request, PeriodoEscolar $periodoEscolar): PeriodoEscolarResource
    {
        $periodo = $this->service->activar($periodoEscolar, $request->user());

        return new PeriodoEscolarResource($periodo);
    }

    public function cerrar(Request $request, PeriodoEscolar $periodoEscolar): PeriodoEscolarResource
    {
        $periodo = $this->service->cerrar($periodoEscolar, $request->user());

        return new PeriodoEscolarResource($periodo);
    }

    public function destroy(PeriodoEscolar $periodoEscolar): JsonResponse
    {
        $this->service->eliminar($periodoEscolar);

        return response()->json(['message' => 'Período escolar eliminado correctamente.']);
    }
}
