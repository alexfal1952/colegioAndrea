<?php

namespace App\Services;

use App\Enums\EstadoAsignatura;
use App\Models\Asignatura;
use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AsignaturaService
{
    public function listar(array $filters = [])
    {
        $establecimiento = Establecimiento::default();

        $query = Asignatura::query()
            ->where(function ($q) use ($establecimiento) {
                $q->whereNull('establecimiento_id')
                    ->orWhere('establecimiento_id', $establecimiento->id);
            });

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($builder) use ($q) {
                $builder->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        return $query->orderBy('nombre')->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function listarActivas()
    {
        $establecimiento = Establecimiento::default();

        return Asignatura::query()
            ->where('estado', EstadoAsignatura::Activa)
            ->where(function ($q) use ($establecimiento) {
                $q->whereNull('establecimiento_id')
                    ->orWhere('establecimiento_id', $establecimiento->id);
            })
            ->orderBy('nombre')
            ->get();
    }

    public function crear(array $data, User $user): Asignatura
    {
        $establecimiento = Establecimiento::default();
        $codigo = strtoupper(trim($data['codigo']));

        $this->validarCodigoUnico($codigo, $establecimiento->id);

        return Asignatura::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'codigo' => $codigo,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => EstadoAsignatura::from($data['estado'] ?? EstadoAsignatura::Activa->value),
            'created_by' => $user->id,
        ]);
    }

    public function actualizar(Asignatura $asignatura, array $data, User $user): Asignatura
    {
        if (isset($data['codigo'])) {
            $codigo = strtoupper(trim($data['codigo']));
            $this->validarCodigoUnico($codigo, $asignatura->establecimiento_id, $asignatura->id);
            $asignatura->codigo = $codigo;
        }

        $asignatura->fill([
            'nombre' => $data['nombre'] ?? $asignatura->nombre,
            'descripcion' => array_key_exists('descripcion', $data) ? $data['descripcion'] : $asignatura->descripcion,
            'estado' => isset($data['estado']) ? EstadoAsignatura::from($data['estado']) : $asignatura->estado,
            'updated_by' => $user->id,
        ])->save();

        return $asignatura->fresh();
    }

    private function validarCodigoUnico(string $codigo, ?int $establecimientoId, ?int $exceptId = null): void
    {
        $query = Asignatura::query()->where('codigo', $codigo)
            ->where(function ($q) use ($establecimientoId) {
                $q->whereNull('establecimiento_id');
                if ($establecimientoId) {
                    $q->orWhere('establecimiento_id', $establecimientoId);
                }
            });

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'codigo' => 'Ya existe una asignatura con este código.',
            ]);
        }
    }
}
