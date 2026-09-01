<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsistenciaResource;
use App\Http\Resources\EstadoAsistenciaResource;
use App\Http\Resources\MatriculaResource;
use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\Matricula;
use App\Services\AsistenciaResumenService;
use App\Services\AsistenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AsistenciaController extends Controller
{
    public function __construct(
        private readonly AsistenciaService $service,
        private readonly AsistenciaResumenService $resumenService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'curso_id' => ['nullable', 'integer'],
            'fecha' => ['nullable', 'date'],
            'matricula_id' => ['nullable', 'integer'],
        ]);

        $query = Asistencia::query()->with(['matricula.alumno.persona', 'estadoAsistencia']);

        if (! empty($data['curso_id'])) {
            $query->where('curso_id', $data['curso_id']);
        }
        if (! empty($data['fecha'])) {
            $query->where('fecha', $data['fecha']);
        }
        if (! empty($data['matricula_id'])) {
            $query->where('matricula_id', $data['matricula_id']);
        }

        return AsistenciaResource::collection($query->orderBy('fecha')->paginate(50));
    }

    public function update(Request $request, Asistencia $asistencia): AsistenciaResource
    {
        $data = $request->validate([
            'estado_asistencia_id' => ['sometimes', 'integer', 'exists:estados_asistencia,id'],
            'hora_ingreso' => ['nullable', 'date_format:H:i'],
            'hora_salida' => ['nullable', 'date_format:H:i'],
            'observacion' => ['nullable', 'string'],
        ]);

        return new AsistenciaResource($this->service->actualizar($asistencia, $data, $request->user()));
    }

    public function porCursoFecha(Curso $curso, Request $request): JsonResponse
    {
        $data = $request->validate(['fecha' => ['required', 'date']]);
        $curso->load('periodoEscolar');

        $items = $this->service->listarPorCursoFecha($curso, $data['fecha']);

        return response()->json([
            'data' => [
                'fecha' => $data['fecha'],
                'validacion' => $this->service->validarFecha($curso, $data['fecha']),
                'alumnos' => $items->map(fn ($item) => [
                    'matricula' => new MatriculaResource($item['matricula']),
                    'asistencia' => $item['asistencia'] ? new AsistenciaResource($item['asistencia']->load('estadoAsistencia')) : null,
                    'estado_asistencia' => $item['estado_asistencia'] ? new EstadoAsistenciaResource($item['estado_asistencia']) : null,
                ]),
            ],
        ]);
    }

    public function guardarLote(Curso $curso, Request $request): JsonResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'registros' => ['required', 'array', 'min:1'],
            'registros.*.matricula_id' => ['required', 'integer', 'exists:matriculas,id'],
            'registros.*.estado_asistencia_id' => ['required', 'integer', 'exists:estados_asistencia,id'],
            'registros.*.hora_ingreso' => ['nullable', 'date_format:H:i'],
            'registros.*.hora_salida' => ['nullable', 'date_format:H:i'],
            'registros.*.observacion' => ['nullable', 'string'],
        ]);

        $asistencias = $this->service->guardarLote($curso, $data['fecha'], $data['registros'], $request->user());

        return response()->json([
            'data' => AsistenciaResource::collection($asistencias),
            'message' => 'Asistencia guardada correctamente.',
        ], 201);
    }

    public function validarFecha(Curso $curso, Request $request): JsonResponse
    {
        $data = $request->validate(['fecha' => ['required', 'date']]);
        $curso->load('periodoEscolar');

        return response()->json(['data' => $this->service->validarFecha($curso, $data['fecha'])]);
    }

    public function resumenCurso(Curso $curso, Request $request): JsonResponse
    {
        $data = $request->validate([
            'anio' => ['required', 'integer'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        return response()->json([
            'data' => $this->resumenService->resumenCurso($curso, (int) $data['anio'], (int) $data['mes']),
        ]);
    }

    public function resumenMatricula(Matricula $matricula, Request $request): JsonResponse
    {
        $data = $request->validate([
            'anio' => ['required', 'integer'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        return response()->json([
            'data' => $this->resumenService->resumenMatricula($matricula, (int) $data['anio'], (int) $data['mes']),
        ]);
    }

    public function historialAlumno(Alumno $alumno, Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_escolar_id' => ['required', 'integer', 'exists:periodos_escolares,id'],
        ]);

        return response()->json([
            'data' => $this->resumenService->historialAlumno($alumno->id, (int) $data['periodo_escolar_id']),
        ]);
    }
}
