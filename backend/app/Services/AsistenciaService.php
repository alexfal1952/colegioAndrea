<?php

namespace App\Services;

use App\Enums\AccionAsistenciaAuditoria;
use App\Enums\EstadoMatricula;
use App\Enums\EstadoPeriodoEscolar;
use App\Models\Asistencia;
use App\Models\AsistenciaAuditoria;
use App\Models\Curso;
use App\Models\Establecimiento;
use App\Models\EstadoAsistencia;
use App\Models\Matricula;
use App\Models\PeriodoEscolar;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsistenciaService
{
    public function __construct(
        private readonly CalendarioEscolarService $calendarioService,
        private readonly AsistenciaResumenService $resumenService,
    ) {}

    public function listarPorCursoFecha(Curso $curso, string $fecha): Collection
    {
        $matriculas = Matricula::query()
            ->with([
                'alumno.persona',
                'asistencias' => fn ($q) => $q->where('fecha', $fecha)->with('estadoAsistencia'),
            ])
            ->where('curso_id', $curso->id)
            ->where('estado', EstadoMatricula::Activa)
            ->orderBy('id')
            ->get();

        return $matriculas->map(function (Matricula $m) use ($fecha) {
            $asistencia = $m->asistencias->first();

            return [
                'matricula' => $m,
                'asistencia' => $asistencia,
                'estado_asistencia' => $asistencia?->estadoAsistencia,
            ];
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $registros
     * @return Collection<int, Asistencia>
     */
    public function guardarLote(Curso $curso, string $fecha, array $registros, User $user): Collection
    {
        $curso->load('periodoEscolar');
        $periodo = $curso->periodoEscolar;
        $this->validarPeriodoEditable($periodo);
        $this->calendarioService->asegurarDiaLectivo($fecha, $periodo, $curso);

        return DB::transaction(function () use ($curso, $fecha, $registros, $user, $periodo) {
            $resultado = collect();

            foreach ($registros as $item) {
                $matricula = Matricula::query()
                    ->where('id', $item['matricula_id'])
                    ->where('curso_id', $curso->id)
                    ->where('estado', EstadoMatricula::Activa)
                    ->firstOrFail();

                $estado = EstadoAsistencia::query()->findOrFail($item['estado_asistencia_id']);

                $existente = Asistencia::query()
                    ->where('matricula_id', $matricula->id)
                    ->where('fecha', $fecha)
                    ->where('turno', $item['turno'] ?? 'completa')
                    ->first();

                if ($existente) {
                    $resultado->push($this->actualizar($existente, $item, $user));
                } else {
                    $resultado->push($this->crear([
                        'periodo_escolar_id' => $periodo->id,
                        'curso_id' => $curso->id,
                        'matricula_id' => $matricula->id,
                        'fecha' => $fecha,
                        'estado_asistencia_id' => $estado->id,
                        'turno' => $item['turno'] ?? 'completa',
                        'hora_ingreso' => $item['hora_ingreso'] ?? null,
                        'hora_salida' => $item['hora_salida'] ?? null,
                        'observacion' => $item['observacion'] ?? null,
                    ], $user));
                }
            }

            return $resultado;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, User $user): Asistencia
    {
        $matricula = Matricula::query()->with('curso.periodoEscolar')->findOrFail($data['matricula_id']);
        $periodo = $matricula->curso->periodoEscolar;
        $this->validarPeriodoEditable($periodo);
        $this->calendarioService->asegurarDiaLectivo($data['fecha'], $periodo, $matricula->curso);

        $asistencia = Asistencia::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $data['periodo_escolar_id'] ?? $periodo->id,
            'curso_id' => $data['curso_id'] ?? $matricula->curso_id,
            'matricula_id' => $matricula->id,
            'fecha' => $data['fecha'],
            'estado_asistencia_id' => $data['estado_asistencia_id'],
            'turno' => $data['turno'] ?? 'completa',
            'hora_ingreso' => $data['hora_ingreso'] ?? null,
            'hora_salida' => $data['hora_salida'] ?? null,
            'observacion' => $data['observacion'] ?? null,
            'created_by' => $user->id,
        ]);

        $this->registrarAuditoria($asistencia, null, $asistencia->estado_asistencia_id, AccionAsistenciaAuditoria::Creacion, $user);

        return $asistencia->load(['matricula.alumno.persona', 'estadoAsistencia']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(Asistencia $asistencia, array $data, User $user): Asistencia
    {
        $asistencia->load('curso.periodoEscolar');
        $this->validarPeriodoEditable($asistencia->curso->periodoEscolar);

        $estadoAnteriorId = $asistencia->estado_asistencia_id;

        $asistencia->update([
            'estado_asistencia_id' => $data['estado_asistencia_id'] ?? $asistencia->estado_asistencia_id,
            'hora_ingreso' => $data['hora_ingreso'] ?? $asistencia->hora_ingreso,
            'hora_salida' => $data['hora_salida'] ?? $asistencia->hora_salida,
            'observacion' => array_key_exists('observacion', $data) ? $data['observacion'] : $asistencia->observacion,
            'updated_by' => $user->id,
        ]);

        $this->registrarAuditoria(
            $asistencia,
            $estadoAnteriorId,
            $asistencia->estado_asistencia_id,
            AccionAsistenciaAuditoria::Actualizacion,
            $user,
            $data
        );

        return $asistencia->fresh(['matricula.alumno.persona', 'estadoAsistencia']);
    }

    public function validarFecha(Curso $curso, string $fecha): array
    {
        $periodo = $curso->periodoEscolar;

        return $this->calendarioService->evaluarDia($fecha, $periodo, $curso);
    }

    public function obtenerEstadoPresente(): EstadoAsistencia
    {
        return EstadoAsistencia::query()
            ->where('codigo', 'presente')
            ->where(function ($q) {
                $q->whereNull('establecimiento_id')
                    ->orWhere('establecimiento_id', Establecimiento::default()->id);
            })
            ->firstOrFail();
    }

    private function registrarAuditoria(
        Asistencia $asistencia,
        ?int $estadoAnteriorId,
        int $estadoNuevoId,
        AccionAsistenciaAuditoria $accion,
        User $user,
        array $data = []
    ): void {
        AsistenciaAuditoria::query()->create([
            'asistencia_id' => $asistencia->id,
            'estado_anterior_id' => $estadoAnteriorId,
            'estado_nuevo_id' => $estadoNuevoId,
            'hora_ingreso_anterior' => $accion === AccionAsistenciaAuditoria::Actualizacion ? ($data['hora_ingreso_anterior'] ?? null) : null,
            'hora_ingreso_nuevo' => $asistencia->hora_ingreso,
            'observacion_anterior' => $accion === AccionAsistenciaAuditoria::Actualizacion ? ($data['observacion_anterior'] ?? null) : null,
            'observacion_nuevo' => $asistencia->observacion,
            'accion' => $accion,
            'user_id' => $user->id,
            'created_at' => now(),
        ]);
    }

    private function validarPeriodoEditable(PeriodoEscolar $periodo): void
    {
        if ($periodo->estado === EstadoPeriodoEscolar::Cerrado) {
            throw ValidationException::withMessages([
                'periodo' => 'No se puede modificar asistencia de un período cerrado.',
            ]);
        }
    }
}
