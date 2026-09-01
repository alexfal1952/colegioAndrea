<?php

namespace App\Services;

use App\Enums\EstadoCurso;
use App\Enums\EstadoPeriodoEscolar;
use App\Enums\JornadaCurso;
use App\Models\Curso;
use App\Models\Establecimiento;
use App\Models\PeriodoEscolar;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CursoService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function listar(array $filters = [])
    {
        $establecimiento = Establecimiento::default();

        $query = Curso::query()
            ->with(['nivel', 'periodoEscolar', 'profesorJefe.persona'])
            ->where('establecimiento_id', $establecimiento->id);

        if (! empty($filters['periodo_escolar_id'])) {
            $query->where('periodo_escolar_id', (int) $filters['periodo_escolar_id']);
        }

        if (! empty($filters['nivel_id'])) {
            $query->where('nivel_id', (int) $filters['nivel_id']);
        }

        return $query->orderBy('nivel_id')->orderBy('paralelo')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function listarPorPeriodo(int $periodoId)
    {
        $this->validarPeriodoRaiz($periodoId);

        return Curso::query()
            ->with(['nivel', 'profesorJefe.persona'])
            ->where('periodo_escolar_id', $periodoId)
            ->orderBy('nivel_id')
            ->orderBy('paralelo')
            ->get();
    }

    public function obtener(int $id): Curso
    {
        return Curso::query()
            ->with([
                'nivel',
                'periodoEscolar',
                'profesorJefe.persona',
                'matriculas.alumno.persona',
                'cursoAsignaturas.asignatura',
                'cursoAsignaturas.asignacionesDocente.profesor.persona',
            ])
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, User $user): Curso
    {
        $periodo = $this->validarPeriodoRaiz((int) $data['periodo_escolar_id']);
        $this->validarPeriodoEditable($periodo);

        $establecimiento = Establecimiento::default();
        $this->validarDuplicado($establecimiento->id, (int) $data['periodo_escolar_id'], (int) $data['nivel_id'], strtoupper(trim($data['paralelo'])));

        $nombre = $data['nombre'] ?? null;

        return Curso::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo->id,
            'nivel_id' => (int) $data['nivel_id'],
            'paralelo' => strtoupper(trim($data['paralelo'])),
            'nombre' => $nombre,
            'cupo_maximo' => $data['cupo_maximo'] ?? null,
            'jornada' => isset($data['jornada']) ? JornadaCurso::from($data['jornada']) : null,
            'estado' => EstadoCurso::from($data['estado'] ?? EstadoCurso::Activo->value),
            'profesor_jefe_id' => $data['profesor_jefe_id'] ?? null,
            'created_by' => $user->id,
        ])->load(['nivel', 'periodoEscolar', 'profesorJefe.persona']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(Curso $curso, array $data, User $user): Curso
    {
        $this->validarPeriodoEditable($curso->periodoEscolar);

        $curso->update([
            'paralelo' => isset($data['paralelo']) ? strtoupper(trim($data['paralelo'])) : $curso->paralelo,
            'nombre' => $data['nombre'] ?? $curso->nombre,
            'jornada' => isset($data['jornada']) ? JornadaCurso::from($data['jornada']) : $curso->jornada,
            'estado' => isset($data['estado']) ? EstadoCurso::from($data['estado']) : $curso->estado,
            'cupo_maximo' => $data['cupo_maximo'] ?? $curso->cupo_maximo,
            'profesor_jefe_id' => array_key_exists('profesor_jefe_id', $data) ? $data['profesor_jefe_id'] : $curso->profesor_jefe_id,
            'updated_by' => $user->id,
        ]);

        return $curso->fresh(['nivel', 'periodoEscolar', 'profesorJefe.persona']);
    }

    public function eliminar(Curso $curso): void
    {
        $this->validarPeriodoEditable($curso->periodoEscolar);

        if ($curso->matriculas()->exists()) {
            throw ValidationException::withMessages([
                'curso' => 'No se puede eliminar un curso con matrículas asociadas.',
            ]);
        }

        $curso->delete();
    }

    private function validarPeriodoRaiz(int $periodoId): PeriodoEscolar
    {
        $periodo = PeriodoEscolar::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->raices()
            ->findOrFail($periodoId);

        return $periodo;
    }

    private function validarPeriodoEditable(PeriodoEscolar $periodo): void
    {
        if ($periodo->estado === EstadoPeriodoEscolar::Cerrado) {
            throw ValidationException::withMessages([
                'periodo_escolar_id' => 'No se pueden modificar cursos de un período cerrado.',
            ]);
        }
    }

    private function validarDuplicado(int $establecimientoId, int $periodoId, int $nivelId, string $paralelo, ?int $exceptId = null): void
    {
        $query = Curso::query()
            ->where('establecimiento_id', $establecimientoId)
            ->where('periodo_escolar_id', $periodoId)
            ->where('nivel_id', $nivelId)
            ->where('paralelo', $paralelo);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'paralelo' => 'Ya existe un curso con este nivel y paralelo en el período seleccionado.',
            ]);
        }
    }
}
