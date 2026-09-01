<?php

namespace App\Services;

use App\Enums\EstadoVacacion;
use App\Models\Establecimiento;
use App\Models\PeriodoEscolar;
use App\Models\User;
use App\Models\Vacacion;
use Illuminate\Validation\ValidationException;

class VacacionService
{
    public function listar(array $filters = [])
    {
        $query = Vacacion::query()
            ->with('periodoEscolar')
            ->where('establecimiento_id', Establecimiento::default()->id);

        if (! empty($filters['periodo_escolar_id'])) {
            $query->where('periodo_escolar_id', (int) $filters['periodo_escolar_id']);
        }

        return $query->orderBy('fecha_inicio')->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function crear(array $data, User $user): Vacacion
    {
        $periodo = $this->validarPeriodo((int) $data['periodo_escolar_id']);
        $this->validarRango($periodo, $data['fecha_inicio'], $data['fecha_fin']);

        return Vacacion::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'nombre' => $data['nombre'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => EstadoVacacion::from($data['estado'] ?? EstadoVacacion::Activa->value),
            'created_by' => $user->id,
        ]);
    }

    public function actualizar(Vacacion $vacacion, array $data, User $user): Vacacion
    {
        $inicio = $data['fecha_inicio'] ?? $vacacion->fecha_inicio->format('Y-m-d');
        $fin = $data['fecha_fin'] ?? $vacacion->fecha_fin->format('Y-m-d');
        $this->validarRango($vacacion->periodoEscolar, $inicio, $fin, $vacacion->id);

        $vacacion->update([
            'nombre' => $data['nombre'] ?? $vacacion->nombre,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'descripcion' => $data['descripcion'] ?? $vacacion->descripcion,
            'estado' => isset($data['estado']) ? EstadoVacacion::from($data['estado']) : $vacacion->estado,
            'updated_by' => $user->id,
        ]);

        return $vacacion->fresh('periodoEscolar');
    }

    public function eliminar(Vacacion $vacacion, User $user): Vacacion
    {
        $vacacion->update(['estado' => EstadoVacacion::Inactiva, 'updated_by' => $user->id]);

        return $vacacion->fresh();
    }

    private function validarPeriodo(int $periodoId): PeriodoEscolar
    {
        return PeriodoEscolar::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->raices()
            ->findOrFail($periodoId);
    }

    private function validarRango(PeriodoEscolar $periodo, string $inicio, string $fin, ?int $exceptId = null): void
    {
        if ($fin < $inicio) {
            throw ValidationException::withMessages(['fecha_fin' => 'La fecha de término debe ser posterior al inicio.']);
        }

        $pInicio = $periodo->fecha_inicio->format('Y-m-d');
        $pFin = $periodo->fecha_termino->format('Y-m-d');

        if ($inicio < $pInicio || $fin > $pFin) {
            throw ValidationException::withMessages(['fecha_inicio' => 'El rango debe estar dentro del período escolar.']);
        }

        $query = Vacacion::query()
            ->where('periodo_escolar_id', $periodo->id)
            ->where('estado', EstadoVacacion::Activa)
            ->where('fecha_inicio', '<=', $fin)
            ->where('fecha_fin', '>=', $inicio);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['fecha_inicio' => 'El rango se solapa con otras vacaciones.']);
        }
    }
}
