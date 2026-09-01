<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\Establecimiento;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlumnoService
{
    public function __construct(
        private readonly PersonaService $personaService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listar(array $filters = [])
    {
        $query = Alumno::query()
            ->with(['persona.pais'])
            ->where('establecimiento_id', Establecimiento::default()->id);

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->whereHas('persona', function ($builder) use ($q) {
                $builder->where('nombres', 'like', "%{$q}%")
                    ->orWhere('apellido_paterno', 'like', "%{$q}%")
                    ->orWhere('numero_documento', 'like', "%{$q}%");
            });
        }

        return $query->orderByDesc('id')->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function obtener(int $id): Alumno
    {
        return Alumno::query()
            ->with(['persona.pais', 'matriculas.curso.nivel', 'matriculas.periodoEscolar'])
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, User $user): Alumno
    {
        return DB::transaction(function () use ($data, $user) {
            $persona = isset($data['persona_id'])
                ? Persona::query()->findOrFail($data['persona_id'])
                : $this->personaService->crear($data['persona'] ?? $data, $user);

            if ($persona->alumno) {
                throw ValidationException::withMessages([
                    'persona_id' => 'Esta persona ya está registrada como alumno.',
                ]);
            }

            return Alumno::query()->create([
                'establecimiento_id' => Establecimiento::default()->id,
                'persona_id' => $persona->id,
                'codigo_interno' => $data['codigo_interno'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'activo' => $data['activo'] ?? true,
                'created_by' => $user->id,
            ])->load('persona.pais');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(Alumno $alumno, array $data, User $user): Alumno
    {
        $alumno->update([
            'codigo_interno' => $data['codigo_interno'] ?? $alumno->codigo_interno,
            'observaciones' => $data['observaciones'] ?? $alumno->observaciones,
            'activo' => $data['activo'] ?? $alumno->activo,
            'updated_by' => $user->id,
        ]);

        if (isset($data['persona'])) {
            $this->personaService->actualizar($alumno->persona, $data['persona'], $user);
        }

        return $alumno->fresh(['persona.pais']);
    }

    public function historial(Alumno $alumno)
    {
        return $alumno->matriculas()
            ->with(['periodoEscolar', 'curso.nivel'])
            ->join('periodos_escolares', 'periodos_escolares.id', '=', 'matriculas.periodo_escolar_id')
            ->orderByDesc('periodos_escolares.anio')
            ->select('matriculas.*')
            ->get();
    }
}
