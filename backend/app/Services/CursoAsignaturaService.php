<?php

namespace App\Services;

use App\Enums\EstadoAsignacionDocente;
use App\Enums\EstadoAsignatura;
use App\Enums\EstadoPeriodoEscolar;
use App\Enums\RolAsignacionDocente;
use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\CursoAsignatura;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CursoAsignaturaService
{
    public function listarPorCurso(Curso $curso): Collection
    {
        return CursoAsignatura::query()
            ->with([
                'asignatura',
                'asignacionesDocente.profesor.persona',
            ])
            ->where('curso_id', $curso->id)
            ->orderBy('id')
            ->get();
    }

    public function agregar(Curso $curso, int $asignaturaId, User $user, ?int $horasSemanales = null): CursoAsignatura
    {
        $this->validarPeriodoEditable($curso);

        if (CursoAsignatura::query()->where('curso_id', $curso->id)->where('asignatura_id', $asignaturaId)->exists()) {
            throw ValidationException::withMessages([
                'asignatura_id' => 'Esta asignatura ya está asociada al curso.',
            ]);
        }

        return CursoAsignatura::query()->create([
            'curso_id' => $curso->id,
            'asignatura_id' => $asignaturaId,
            'estado' => EstadoAsignatura::Activa,
            'horas_semanales' => $horasSemanales,
            'created_by' => $user->id,
        ])->load(['asignatura', 'asignacionesDocente.profesor.persona']);
    }

    public function cambiarEstado(CursoAsignatura $cursoAsignatura, string $estado, User $user): CursoAsignatura
    {
        $this->validarPeriodoEditable($cursoAsignatura->curso);

        $cursoAsignatura->update([
            'estado' => EstadoAsignatura::from($estado),
            'updated_by' => $user->id,
        ]);

        return $cursoAsignatura->fresh(['asignatura', 'asignacionesDocente.profesor.persona']);
    }

    public function eliminar(CursoAsignatura $cursoAsignatura): void
    {
        $this->validarPeriodoEditable($cursoAsignatura->curso);

        if ($cursoAsignatura->asignacionesDocente()->where('estado', EstadoAsignacionDocente::Activa)->exists()) {
            throw ValidationException::withMessages([
                'curso_asignatura' => 'No se puede quitar una asignatura con profesores activos asignados.',
            ]);
        }

        $cursoAsignatura->delete();
    }

    public function asignarProfesor(CursoAsignatura $cursoAsignatura, array $data, User $user): AsignacionDocente
    {
        $this->validarPeriodoEditable($cursoAsignatura->curso);

        $rol = RolAsignacionDocente::from($data['rol'] ?? RolAsignacionDocente::Titular->value);

        if ($rol === RolAsignacionDocente::Titular) {
            $titularActivo = AsignacionDocente::query()
                ->where('curso_asignatura_id', $cursoAsignatura->id)
                ->where('rol', RolAsignacionDocente::Titular)
                ->where('estado', EstadoAsignacionDocente::Activa)
                ->exists();

            if ($titularActivo) {
                throw ValidationException::withMessages([
                    'rol' => 'Ya existe un profesor titular activo para esta asignatura.',
                ]);
            }
        }

        return AsignacionDocente::query()->create([
            'curso_asignatura_id' => $cursoAsignatura->id,
            'profesor_id' => (int) $data['profesor_id'],
            'rol' => $rol,
            'estado' => EstadoAsignacionDocente::Activa,
            'fecha_inicio' => $data['fecha_inicio'] ?? now()->toDateString(),
            'fecha_termino' => $data['fecha_termino'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'created_by' => $user->id,
        ])->load('profesor.persona');
    }

    public function actualizarAsignacion(AsignacionDocente $asignacion, array $data, User $user): AsignacionDocente
    {
        $cursoAsignatura = $asignacion->cursoAsignatura()->with('curso.periodoEscolar')->first();
        $this->validarPeriodoEditable($cursoAsignatura->curso);

        if (isset($data['rol'])) {
            $rol = RolAsignacionDocente::from($data['rol']);
            if ($rol === RolAsignacionDocente::Titular && $data['rol'] !== $asignacion->rol->value) {
                $exists = AsignacionDocente::query()
                    ->where('curso_asignatura_id', $asignacion->curso_asignatura_id)
                    ->where('id', '!=', $asignacion->id)
                    ->where('rol', RolAsignacionDocente::Titular)
                    ->where('estado', EstadoAsignacionDocente::Activa)
                    ->exists();
                if ($exists) {
                    throw ValidationException::withMessages(['rol' => 'Ya existe un titular activo.']);
                }
            }
            $asignacion->rol = $rol;
        }

        $asignacion->fill([
            'estado' => isset($data['estado']) ? EstadoAsignacionDocente::from($data['estado']) : $asignacion->estado,
            'fecha_inicio' => $data['fecha_inicio'] ?? $asignacion->fecha_inicio,
            'fecha_termino' => array_key_exists('fecha_termino', $data) ? $data['fecha_termino'] : $asignacion->fecha_termino,
            'observaciones' => $data['observaciones'] ?? $asignacion->observaciones,
            'updated_by' => $user->id,
        ])->save();

        return $asignacion->fresh('profesor.persona');
    }

    public function listarProfesoresCurso(Curso $curso): array
    {
        $items = CursoAsignatura::query()
            ->with(['asignatura', 'asignacionesDocente' => fn ($q) => $q->where('estado', 'activa')->with('profesor.persona')])
            ->where('curso_id', $curso->id)
            ->get();

        $profesorJefe = $curso->profesorJefe?->load('persona');
        $porProfesor = [];

        foreach ($items as $ca) {
            foreach ($ca->asignacionesDocente as $asig) {
                $pid = $asig->profesor_id;
                if (! isset($porProfesor[$pid])) {
                    $porProfesor[$pid] = [
                        'profesor' => $asig->profesor,
                        'asignaturas' => [],
                    ];
                }
                $porProfesor[$pid]['asignaturas'][] = [
                    'asignatura' => $ca->asignatura->nombre,
                    'codigo' => $ca->asignatura->codigo,
                    'rol' => $asig->rol->label(),
                ];
            }
        }

        return [
            'profesor_jefe' => $profesorJefe,
            'profesores' => array_values($porProfesor),
        ];
    }

    private function validarPeriodoEditable(Curso $curso): void
    {
        $curso->loadMissing('periodoEscolar');
        if ($curso->periodoEscolar->estado === EstadoPeriodoEscolar::Cerrado) {
            throw ValidationException::withMessages([
                'periodo' => 'No se pueden modificar asignaturas de un período cerrado.',
            ]);
        }
    }
}
