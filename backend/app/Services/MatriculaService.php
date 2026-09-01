<?php

namespace App\Services;

use App\Enums\EstadoMatricula;
use App\Enums\EstadoPeriodoEscolar;
use App\Models\Curso;
use App\Models\Establecimiento;
use App\Models\Matricula;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class MatriculaService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function listar(array $filters = [])
    {
        $query = Matricula::query()
            ->with(['alumno.persona', 'curso.nivel', 'periodoEscolar'])
            ->where('establecimiento_id', Establecimiento::default()->id);

        if (! empty($filters['periodo_escolar_id'])) {
            $query->where('periodo_escolar_id', (int) $filters['periodo_escolar_id']);
        }

        if (! empty($filters['curso_id'])) {
            $query->where('curso_id', (int) $filters['curso_id']);
        }

        if (! empty($filters['alumno_id'])) {
            $query->where('alumno_id', (int) $filters['alumno_id']);
        }

        return $query->orderByDesc('fecha_matricula')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function obtener(int $id): Matricula
    {
        return Matricula::query()
            ->with(['alumno.persona', 'curso.nivel', 'periodoEscolar'])
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, User $user): Matricula
    {
        $curso = Curso::query()
            ->with('periodoEscolar')
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($data['curso_id']);

        $periodoId = (int) ($data['periodo_escolar_id'] ?? $curso->periodo_escolar_id);

        if ($curso->periodo_escolar_id !== $periodoId) {
            throw ValidationException::withMessages([
                'curso_id' => 'El curso no pertenece al período escolar indicado.',
            ]);
        }

        if ($curso->periodoEscolar->estado === EstadoPeriodoEscolar::Cerrado) {
            throw ValidationException::withMessages([
                'periodo_escolar_id' => 'No se pueden crear matrículas en un período cerrado.',
            ]);
        }

        if ($curso->cupo_maximo && $curso->matriculas()->where('estado', EstadoMatricula::Activa)->count() >= $curso->cupo_maximo) {
            throw ValidationException::withMessages([
                'curso_id' => 'El curso ha alcanzado su cupo máximo.',
            ]);
        }

        $existente = Matricula::query()
            ->where('alumno_id', $data['alumno_id'])
            ->where('periodo_escolar_id', $periodoId)
            ->exists();

        if ($existente) {
            throw ValidationException::withMessages([
                'alumno_id' => 'El alumno ya tiene una matrícula en este período escolar.',
            ]);
        }

        return Matricula::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodoId,
            'curso_id' => $curso->id,
            'alumno_id' => (int) $data['alumno_id'],
            'numero_matricula' => $data['numero_matricula'] ?? null,
            'fecha_matricula' => $data['fecha_matricula'] ?? now()->toDateString(),
            'estado' => EstadoMatricula::from($data['estado'] ?? EstadoMatricula::Activa->value),
            'observaciones' => $data['observaciones'] ?? null,
            'created_by' => $user->id,
        ])->load(['alumno.persona', 'curso.nivel', 'periodoEscolar']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(Matricula $matricula, array $data, User $user): Matricula
    {
        if ($matricula->periodoEscolar->estado === EstadoPeriodoEscolar::Cerrado) {
            throw ValidationException::withMessages([
                'matricula' => 'No se puede modificar una matrícula de un período cerrado.',
            ]);
        }

        if (isset($data['curso_id'])) {
            $curso = Curso::query()->findOrFail($data['curso_id']);
            if ($curso->periodo_escolar_id !== $matricula->periodo_escolar_id) {
                throw ValidationException::withMessages([
                    'curso_id' => 'El nuevo curso debe pertenecer al mismo período escolar.',
                ]);
            }
            $matricula->curso_id = $curso->id;
        }

        $matricula->fill([
            'numero_matricula' => $data['numero_matricula'] ?? $matricula->numero_matricula,
            'fecha_matricula' => $data['fecha_matricula'] ?? $matricula->fecha_matricula,
            'estado' => isset($data['estado']) ? EstadoMatricula::from($data['estado']) : $matricula->estado,
            'observaciones' => $data['observaciones'] ?? $matricula->observaciones,
            'updated_by' => $user->id,
        ])->save();

        return $matricula->fresh(['alumno.persona', 'curso.nivel', 'periodoEscolar']);
    }
}
