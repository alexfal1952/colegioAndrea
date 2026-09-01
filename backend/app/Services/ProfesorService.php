<?php

namespace App\Services;

use App\Models\Establecimiento;
use App\Models\Persona;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfesorService
{
    public function __construct(
        private readonly PersonaService $personaService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listar(array $filters = [])
    {
        $query = Profesor::query()
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

    public function obtener(int $id): Profesor
    {
        return Profesor::query()
            ->with(['persona.pais', 'cursosComoJefe.nivel', 'cursosComoJefe.periodoEscolar'])
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, User $user): Profesor
    {
        return DB::transaction(function () use ($data, $user) {
            $persona = isset($data['persona_id'])
                ? Persona::query()->findOrFail($data['persona_id'])
                : $this->personaService->crear($data['persona'] ?? $data, $user);

            if ($persona->profesor) {
                throw ValidationException::withMessages([
                    'persona_id' => 'Esta persona ya está registrada como profesor.',
                ]);
            }

            return Profesor::query()->create([
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
    public function actualizar(Profesor $profesor, array $data, User $user): Profesor
    {
        $profesor->update([
            'codigo_interno' => $data['codigo_interno'] ?? $profesor->codigo_interno,
            'observaciones' => $data['observaciones'] ?? $profesor->observaciones,
            'activo' => $data['activo'] ?? $profesor->activo,
            'updated_by' => $user->id,
        ]);

        if (isset($data['persona'])) {
            $this->personaService->actualizar($profesor->persona, $data['persona'], $user);
        }

        return $profesor->fresh(['persona.pais']);
    }
}
