<?php

namespace App\Services;

use App\Enums\EstadoVacacion;
use App\Enums\TipoSuspensionClases;
use App\Models\Establecimiento;
use App\Models\PeriodoEscolar;
use App\Models\SuspensionClases;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SuspensionClasesService
{
    public function listar(array $filters = [])
    {
        $query = SuspensionClases::query()
            ->with(['periodoEscolar', 'curso.nivel'])
            ->where('establecimiento_id', Establecimiento::default()->id);

        if (! empty($filters['periodo_escolar_id'])) {
            $query->where('periodo_escolar_id', (int) $filters['periodo_escolar_id']);
        }

        if (! empty($filters['curso_id'])) {
            $query->where('curso_id', (int) $filters['curso_id']);
        }

        return $query->orderByDesc('fecha')->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function crear(array $data, User $user): SuspensionClases
    {
        $periodo = $this->validarPeriodo((int) $data['periodo_escolar_id']);
        $this->validarDuplicado($periodo->id, $data['fecha'], $data['curso_id'] ?? null);

        return SuspensionClases::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'curso_id' => $data['curso_id'] ?? null,
            'fecha' => $data['fecha'],
            'nombre' => $data['nombre'],
            'motivo' => $data['motivo'] ?? null,
            'tipo' => TipoSuspensionClases::from($data['tipo'] ?? TipoSuspensionClases::Total->value),
            'estado' => EstadoVacacion::from($data['estado'] ?? EstadoVacacion::Activa->value),
            'created_by' => $user->id,
        ]);
    }

    public function actualizar(SuspensionClases $suspension, array $data, User $user): SuspensionClases
    {
        $fecha = $data['fecha'] ?? $suspension->fecha->format('Y-m-d');
        $cursoId = array_key_exists('curso_id', $data) ? $data['curso_id'] : $suspension->curso_id;
        $this->validarDuplicado($suspension->periodo_escolar_id, $fecha, $cursoId, $suspension->id);

        $suspension->update([
            'curso_id' => $cursoId,
            'fecha' => $fecha,
            'nombre' => $data['nombre'] ?? $suspension->nombre,
            'motivo' => $data['motivo'] ?? $suspension->motivo,
            'tipo' => isset($data['tipo']) ? TipoSuspensionClases::from($data['tipo']) : $suspension->tipo,
            'estado' => isset($data['estado']) ? EstadoVacacion::from($data['estado']) : $suspension->estado,
            'updated_by' => $user->id,
        ]);

        return $suspension->fresh(['periodoEscolar', 'curso.nivel']);
    }

    public function eliminar(SuspensionClases $suspension, User $user): SuspensionClases
    {
        $suspension->update(['estado' => EstadoVacacion::Inactiva, 'updated_by' => $user->id]);

        return $suspension->fresh();
    }

    private function validarPeriodo(int $periodoId): PeriodoEscolar
    {
        return PeriodoEscolar::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->raices()
            ->findOrFail($periodoId);
    }

    private function validarDuplicado(int $periodoId, string $fecha, ?int $cursoId, ?int $exceptId = null): void
    {
        $query = SuspensionClases::query()
            ->where('periodo_escolar_id', $periodoId)
            ->where('fecha', $fecha)
            ->where('estado', EstadoVacacion::Activa);

        if ($cursoId === null) {
            $query->whereNull('curso_id');
        } else {
            $query->where('curso_id', $cursoId);
        }

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['fecha' => 'Ya existe una suspensión para esta fecha y alcance.']);
        }
    }
}
