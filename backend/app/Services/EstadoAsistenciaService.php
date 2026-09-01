<?php

namespace App\Services;

use App\Models\Establecimiento;
use App\Models\EstadoAsistencia;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EstadoAsistenciaService
{
    public function listarActivos()
    {
        return EstadoAsistencia::query()
            ->where(function ($q) {
                $q->whereNull('establecimiento_id')
                    ->orWhere('establecimiento_id', Establecimiento::default()->id);
            })
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    public function listar(array $filters = [])
    {
        return EstadoAsistencia::query()
            ->where(function ($q) {
                $q->whereNull('establecimiento_id')
                    ->orWhere('establecimiento_id', Establecimiento::default()->id);
            })
            ->orderBy('orden')
            ->paginate((int) ($filters['per_page'] ?? 50));
    }

    public function crear(array $data, User $user): EstadoAsistencia
    {
        $this->validarCodigoUnico($data['codigo']);

        return EstadoAsistencia::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'codigo' => strtolower(trim($data['codigo'])),
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'cuenta_como_presente' => (bool) ($data['cuenta_como_presente'] ?? false),
            'cuenta_como_ausencia' => (bool) ($data['cuenta_como_ausencia'] ?? false),
            'cuenta_como_atraso' => (bool) ($data['cuenta_como_atraso'] ?? false),
            'cuenta_como_justificado' => (bool) ($data['cuenta_como_justificado'] ?? false),
            'color' => $data['color'] ?? null,
            'orden' => (int) ($data['orden'] ?? 0),
            'activo' => true,
            'es_sistema' => false,
            'created_by' => $user->id,
        ]);
    }

    public function actualizar(EstadoAsistencia $estado, array $data, User $user): EstadoAsistencia
    {
        if ($estado->es_sistema && isset($data['codigo']) && $data['codigo'] !== $estado->codigo) {
            throw ValidationException::withMessages(['codigo' => 'No se puede cambiar el código de un estado del sistema.']);
        }

        $estado->update([
            'nombre' => $data['nombre'] ?? $estado->nombre,
            'descripcion' => $data['descripcion'] ?? $estado->descripcion,
            'cuenta_como_presente' => $data['cuenta_como_presente'] ?? $estado->cuenta_como_presente,
            'cuenta_como_ausencia' => $data['cuenta_como_ausencia'] ?? $estado->cuenta_como_ausencia,
            'cuenta_como_atraso' => $data['cuenta_como_atraso'] ?? $estado->cuenta_como_atraso,
            'cuenta_como_justificado' => $data['cuenta_como_justificado'] ?? $estado->cuenta_como_justificado,
            'color' => $data['color'] ?? $estado->color,
            'orden' => $data['orden'] ?? $estado->orden,
            'activo' => $data['activo'] ?? $estado->activo,
            'updated_by' => $user->id,
        ]);

        return $estado->fresh();
    }

    private function validarCodigoUnico(string $codigo, ?int $exceptId = null): void
    {
        $query = EstadoAsistencia::query()
            ->where('codigo', strtolower(trim($codigo)))
            ->where(function ($q) {
                $q->whereNull('establecimiento_id')
                    ->orWhere('establecimiento_id', Establecimiento::default()->id);
            });

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['codigo' => 'Ya existe un estado con este código.']);
        }
    }
}
