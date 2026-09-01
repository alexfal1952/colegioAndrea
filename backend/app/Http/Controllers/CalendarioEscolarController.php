<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Services\CalendarioEscolarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarioEscolarController extends Controller
{
    public function __construct(private readonly CalendarioEscolarService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['required', 'integer', 'exists:periodos_escolares,id'],
            'anio' => ['required', 'integer'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
            'curso_id' => ['nullable', 'integer', 'exists:cursos,id'],
        ]);

        return response()->json([
            'data' => $this->service->calendarioMensual(
                (int) $data['periodo_escolar_id'],
                (int) $data['anio'],
                (int) $data['mes'],
                isset($data['curso_id']) ? (int) $data['curso_id'] : null
            ),
        ]);
    }

    public function show(Request $request, string $fecha): JsonResponse
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['required', 'integer', 'exists:periodos_escolares,id'],
            'curso_id' => ['nullable', 'integer', 'exists:cursos,id'],
        ]);

        $periodo = $this->service->validarPeriodoRaiz((int) $data['periodo_escolar_id']);
        $curso = isset($data['curso_id']) ? Curso::query()->findOrFail($data['curso_id']) : null;

        return response()->json([
            'data' => $this->service->evaluarDia($fecha, $periodo, $curso),
        ]);
    }

    public function configuracion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['nullable', 'integer', 'exists:periodos_escolares,id'],
        ]);

        $periodoId = $data['periodo_escolar_id'] ?? null;
        $config = $this->service->obtenerConfig($periodoId ? (int) $periodoId : null);

        return response()->json(['data' => $config]);
    }

    public function actualizarConfiguracion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['nullable', 'integer', 'exists:periodos_escolares,id'],
            'sabado_lectivo' => ['sometimes', 'boolean'],
            'domingo_lectivo' => ['sometimes', 'boolean'],
            'permitir_asistencia_en_feriado' => ['sometimes', 'boolean'],
            'permitir_asistencia_en_vacaciones' => ['sometimes', 'boolean'],
            'permitir_asistencia_en_suspension' => ['sometimes', 'boolean'],
            'permitir_asistencia_fin_semana' => ['sometimes', 'boolean'],
            'dia_sin_registro_es_ausente' => ['sometimes', 'boolean'],
        ]);

        $config = $this->service->actualizarConfig($data, $request->user());

        return response()->json(['data' => $config]);
    }
}
