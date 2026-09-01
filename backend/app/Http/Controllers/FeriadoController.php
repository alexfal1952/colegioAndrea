<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeriadoResource;
use App\Models\Feriado;
use App\Services\FeriadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class FeriadoController extends Controller
{
    public function __construct(private readonly FeriadoService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return FeriadoResource::collection($this->service->listar($request->only(['periodo_escolar_id', 'tipo', 'q', 'per_page'])));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['nullable', 'integer', 'exists:periodos_escolares,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['sometimes', Rule::in(['nacional', 'regional', 'comunal', 'escolar', 'otro'])],
            'recurrente' => ['sometimes', 'boolean'],
            'fecha' => ['nullable', 'date'],
            'mes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'dia' => ['nullable', 'integer', 'min:1', 'max:31'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['sometimes', Rule::in(['activo', 'inactivo'])],
        ]);

        $feriado = $this->service->crear($data, $request->user());

        return (new FeriadoResource($feriado))->response()->setStatusCode(201);
    }

    public function update(Request $request, Feriado $feriado): FeriadoResource
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['nullable', 'integer', 'exists:periodos_escolares,id'],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'tipo' => ['sometimes', Rule::in(['nacional', 'regional', 'comunal', 'escolar', 'otro'])],
            'recurrente' => ['sometimes', 'boolean'],
            'fecha' => ['nullable', 'date'],
            'mes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'dia' => ['nullable', 'integer', 'min:1', 'max:31'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['sometimes', Rule::in(['activo', 'inactivo'])],
        ]);

        return new FeriadoResource($this->service->actualizar($feriado, $data, $request->user()));
    }

    public function sincronizarBoostr(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'periodo_escolar_id' => ['nullable', 'integer', 'exists:periodos_escolares,id'],
        ]);

        $resultado = $this->service->sincronizarDesdeBoostr(
            (int) $data['anio'],
            $request->user(),
            isset($data['periodo_escolar_id']) ? (int) $data['periodo_escolar_id'] : null
        );

        return response()->json([
            'data' => $resultado,
            'message' => "Feriados {$resultado['anio']} sincronizados: {$resultado['creados']} creados, {$resultado['actualizados']} actualizados.",
        ]);
    }
}
