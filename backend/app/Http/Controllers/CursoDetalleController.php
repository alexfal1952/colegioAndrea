<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsignacionDocenteResource;
use App\Http\Resources\CursoAsignaturaResource;
use App\Http\Resources\CursoResource;
use App\Http\Resources\MatriculaResource;
use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\CursoAsignatura;
use App\Services\CursoAsignaturaService;
use App\Services\CursoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CursoDetalleController extends Controller
{
    public function __construct(
        private readonly CursoService $cursoService,
        private readonly CursoAsignaturaService $asignaturaService,
    ) {}

    public function show(Curso $curso): CursoResource
    {
        return new CursoResource($this->cursoService->obtener($curso->id));
    }

    public function asignaturas(Curso $curso): AnonymousResourceCollection
    {
        return CursoAsignaturaResource::collection($this->asignaturaService->listarPorCurso($curso));
    }

    public function agregarAsignatura(Request $request, Curso $curso): JsonResponse
    {
        $data = $request->validate([
            'asignatura_id' => ['required', 'integer', 'exists:asignaturas,id'],
            'horas_semanales' => ['nullable', 'integer', 'min:1'],
        ]);

        $ca = $this->asignaturaService->agregar(
            $curso,
            (int) $data['asignatura_id'],
            $request->user(),
            $data['horas_semanales'] ?? null
        );

        return (new CursoAsignaturaResource($ca))->response()->setStatusCode(201);
    }

    public function quitarAsignatura(Curso $curso, CursoAsignatura $cursoAsignatura): JsonResponse
    {
        abort_if($cursoAsignatura->curso_id !== $curso->id, 404);
        $this->asignaturaService->eliminar($cursoAsignatura);

        return response()->json(['message' => 'Asignatura removida del curso.']);
    }

    public function asignarDocente(Request $request, CursoAsignatura $cursoAsignatura): JsonResponse
    {
        $data = $request->validate([
            'profesor_id' => ['required', 'integer', 'exists:profesores,id'],
            'rol' => ['sometimes', Rule::in(['titular', 'reemplazante', 'apoyo', 'co-docente'])],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_termino' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $asignacion = $this->asignaturaService->asignarProfesor($cursoAsignatura, $data, $request->user());

        return (new AsignacionDocenteResource($asignacion))->response()->setStatusCode(201);
    }

    public function actualizarAsignacion(Request $request, AsignacionDocente $asignacionDocente): AsignacionDocenteResource
    {
        $data = $request->validate([
            'rol' => ['sometimes', Rule::in(['titular', 'reemplazante', 'apoyo', 'co-docente'])],
            'estado' => ['sometimes', Rule::in(['activa', 'inactiva', 'finalizada'])],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_termino' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
        ]);

        return new AsignacionDocenteResource(
            $this->asignaturaService->actualizarAsignacion($asignacionDocente, $data, $request->user())
        );
    }

    public function profesores(Curso $curso): JsonResponse
    {
        return response()->json(['data' => $this->asignaturaService->listarProfesoresCurso($curso)]);
    }

    public function alumnos(Curso $curso): AnonymousResourceCollection
    {
        $curso->load(['matriculas.alumno.persona', 'matriculas.periodoEscolar']);

        return MatriculaResource::collection($curso->matriculas);
    }

    public function profesorJefe(Request $request, Curso $curso): CursoResource
    {
        $data = $request->validate([
            'profesor_jefe_id' => ['nullable', 'integer', 'exists:profesores,id'],
        ]);

        return new CursoResource(
            $this->cursoService->actualizar($curso, $data, $request->user())
        );
    }
}
