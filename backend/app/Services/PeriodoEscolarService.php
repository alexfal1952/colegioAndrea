<?php

namespace App\Services;

use App\Enums\EstadoPeriodoEscolar;
use App\Enums\SubtipoPeriodoEscolar;
use App\Enums\TipoPeriodoEscolar;
use App\Models\Establecimiento;
use App\Models\PeriodoEscolar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeriodoEscolarService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listar(array $filters = [])
    {
        $establecimiento = Establecimiento::default();

        $query = PeriodoEscolar::query()
            ->withCount('subperiodos')
            ->where('establecimiento_id', $establecimiento->id)
            ->raices()
            ->with(['creador:id,name', 'editor:id,name']);

        if (! empty($filters['anio'])) {
            $query->where('anio', (int) $filters['anio']);
        }

        if (! empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        $sort = $filters['sort'] ?? '-anio';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (in_array($column, ['anio', 'fecha_inicio', 'fecha_termino', 'created_at'], true)) {
            $query->orderBy($column, $direction);
        } else {
            $query->orderByDesc('anio');
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function obtener(int $id): PeriodoEscolar
    {
        $establecimiento = Establecimiento::default();

        return PeriodoEscolar::query()
            ->where('establecimiento_id', $establecimiento->id)
            ->raices()
            ->with([
                'subperiodos' => fn ($q) => $q->orderBy('numero_subperiodo'),
                'creador:id,name,email',
                'editor:id,name,email',
            ])
            ->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, User $user): PeriodoEscolar
    {
        $establecimiento = Establecimiento::default();
        $tipo = TipoPeriodoEscolar::from($data['tipo']);
        $subperiodos = $data['subperiodos'] ?? [];

        $this->validarRaiz($data, $tipo, $subperiodos);
        $this->validarDuplicadoRaiz($establecimiento->id, (int) $data['anio'], $tipo);
        $this->validarSubperiodos($data, $tipo, $subperiodos);

        return DB::transaction(function () use ($data, $user, $establecimiento, $tipo, $subperiodos) {
            $estado = EstadoPeriodoEscolar::from($data['estado'] ?? EstadoPeriodoEscolar::Borrador->value);

            if ($estado === EstadoPeriodoEscolar::Activo) {
                $this->cerrarActivosDelAnio($establecimiento->id, (int) $data['anio'], $user);
            }

            $periodo = PeriodoEscolar::query()->create([
                'establecimiento_id' => $establecimiento->id,
                'nombre' => $data['nombre'],
                'anio' => (int) $data['anio'],
                'tipo' => $tipo,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_termino' => $data['fecha_termino'],
                'estado' => $estado,
                'descripcion' => $data['descripcion'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->crearSubperiodos($periodo, $tipo, $subperiodos, $user);

            return $periodo->fresh(['subperiodos', 'creador', 'editor']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(PeriodoEscolar $periodo, array $data, User $user): PeriodoEscolar
    {
        if (! $periodo->esRaiz()) {
            throw ValidationException::withMessages([
                'periodo' => 'Solo se pueden editar períodos raíz.',
            ]);
        }

        if ($periodo->estaCerrado()) {
            throw ValidationException::withMessages([
                'estado' => 'Un período cerrado no puede modificarse.',
            ]);
        }

        $tipo = TipoPeriodoEscolar::from($data['tipo'] ?? $periodo->tipo->value);
        $subperiodos = $data['subperiodos'] ?? null;
        $estructuraEditable = $periodo->estado->esEstructuraEditable();

        if (! $estructuraEditable) {
            unset($data['fecha_inicio'], $data['fecha_termino'], $data['tipo'], $data['anio']);
            $subperiodos = null;
        } else {
            $this->validarRaiz($data, $tipo, $subperiodos ?? []);
            $this->validarDuplicadoRaiz(
                $periodo->establecimiento_id,
                (int) ($data['anio'] ?? $periodo->anio),
                $tipo,
                $periodo->id
            );

            if ($subperiodos !== null) {
                $this->validarSubperiodos($data, $tipo, $subperiodos);
            }
        }

        return DB::transaction(function () use ($periodo, $data, $user, $tipo, $subperiodos, $estructuraEditable) {
            $payload = [
                'nombre' => $data['nombre'] ?? $periodo->nombre,
                'descripcion' => array_key_exists('descripcion', $data) ? $data['descripcion'] : $periodo->descripcion,
                'updated_by' => $user->id,
            ];

            if ($estructuraEditable) {
                $payload['anio'] = (int) ($data['anio'] ?? $periodo->anio);
                $payload['tipo'] = $tipo;
                $payload['fecha_inicio'] = $data['fecha_inicio'] ?? $periodo->fecha_inicio->format('Y-m-d');
                $payload['fecha_termino'] = $data['fecha_termino'] ?? $periodo->fecha_termino->format('Y-m-d');
            }

            if (isset($data['estado']) && $periodo->estado === EstadoPeriodoEscolar::Borrador) {
                $payload['estado'] = EstadoPeriodoEscolar::from($data['estado']);
            }

            $periodo->update($payload);

            if ($estructuraEditable && $subperiodos !== null) {
                $periodo->subperiodos()->delete();
                $this->crearSubperiodos($periodo, $tipo, $subperiodos, $user);
            }

            return $periodo->fresh(['subperiodos', 'creador', 'editor']);
        });
    }

    public function activar(PeriodoEscolar $periodo, User $user): PeriodoEscolar
    {
        if (! $periodo->esRaiz()) {
            throw ValidationException::withMessages([
                'periodo' => 'Solo se pueden activar períodos raíz.',
            ]);
        }

        if ($periodo->estaCerrado()) {
            throw ValidationException::withMessages([
                'estado' => 'Un período cerrado no puede activarse.',
            ]);
        }

        return DB::transaction(function () use ($periodo, $user) {
            $this->cerrarActivosDelAnio($periodo->establecimiento_id, $periodo->anio, $user);

            $periodo->update([
                'estado' => EstadoPeriodoEscolar::Activo,
                'updated_by' => $user->id,
            ]);

            return $periodo->fresh(['subperiodos', 'creador', 'editor']);
        });
    }

    public function cerrar(PeriodoEscolar $periodo, User $user): PeriodoEscolar
    {
        if (! $periodo->esRaiz()) {
            throw ValidationException::withMessages([
                'periodo' => 'Solo se pueden cerrar períodos raíz.',
            ]);
        }

        if ($periodo->estaCerrado()) {
            throw ValidationException::withMessages([
                'estado' => 'El período ya está cerrado.',
            ]);
        }

        $periodo->update([
            'estado' => EstadoPeriodoEscolar::Cerrado,
            'closed_at' => now(),
            'updated_by' => $user->id,
        ]);

        return $periodo->fresh(['subperiodos', 'creador', 'editor']);
    }

    public function eliminar(PeriodoEscolar $periodo): void
    {
        if (! $periodo->esRaiz()) {
            throw ValidationException::withMessages([
                'periodo' => 'Solo se pueden eliminar períodos raíz.',
            ]);
        }

        if ($periodo->estado !== EstadoPeriodoEscolar::Borrador) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden eliminar períodos en borrador.',
            ]);
        }

        DB::transaction(function () use ($periodo) {
            $periodo->subperiodos()->delete();
            $periodo->delete();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $subperiodos
     */
    private function crearSubperiodos(PeriodoEscolar $periodo, TipoPeriodoEscolar $tipo, array $subperiodos, User $user): void
    {
        if ($tipo === TipoPeriodoEscolar::Anual) {
            return;
        }

        $subtipo = $tipo === TipoPeriodoEscolar::Semestral
            ? SubtipoPeriodoEscolar::Semestre
            : SubtipoPeriodoEscolar::Trimestre;

        foreach ($subperiodos as $sub) {
            PeriodoEscolar::query()->create([
                'establecimiento_id' => $periodo->establecimiento_id,
                'parent_id' => $periodo->id,
                'nombre' => $sub['nombre'],
                'anio' => $periodo->anio,
                'tipo' => $tipo,
                'subtipo' => $subtipo,
                'numero_subperiodo' => (int) $sub['numero'],
                'fecha_inicio' => $sub['fecha_inicio'],
                'fecha_termino' => $sub['fecha_termino'],
                'estado' => $periodo->estado,
                'descripcion' => null,
                'created_by' => $user->id,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $subperiodos
     */
    private function validarRaiz(array $data, TipoPeriodoEscolar $tipo, array $subperiodos): void
    {
        $inicio = Carbon::parse($data['fecha_inicio']);
        $termino = Carbon::parse($data['fecha_termino']);

        if ($termino->lt($inicio)) {
            throw ValidationException::withMessages([
                'fecha_termino' => 'La fecha de término debe ser posterior o igual a la fecha de inicio.',
            ]);
        }

        $esperados = $tipo->cantidadSubperiodos();

        if ($esperados === 0 && count($subperiodos) > 0) {
            throw ValidationException::withMessages([
                'subperiodos' => 'Un período anual no debe incluir subperíodos.',
            ]);
        }

        if ($esperados > 0 && count($subperiodos) !== $esperados) {
            throw ValidationException::withMessages([
                'subperiodos' => "Un período {$tipo->label()} requiere exactamente {$esperados} subperíodos.",
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $subperiodos
     */
    private function validarSubperiodos(array $data, TipoPeriodoEscolar $tipo, array $subperiodos): void
    {
        if ($tipo === TipoPeriodoEscolar::Anual) {
            return;
        }

        $padreInicio = Carbon::parse($data['fecha_inicio'])->startOfDay();
        $padreTermino = Carbon::parse($data['fecha_termino'])->startOfDay();

        usort($subperiodos, fn ($a, $b) => ($a['numero'] ?? 0) <=> ($b['numero'] ?? 0));

        $numeros = [];
        $anteriorTermino = null;

        foreach ($subperiodos as $index => $sub) {
            $numero = (int) ($sub['numero'] ?? 0);
            $prefijo = "subperiodos.{$index}";

            if (in_array($numero, $numeros, true)) {
                throw ValidationException::withMessages([
                    "{$prefijo}.numero" => 'Número de subperíodo duplicado.',
                ]);
            }
            $numeros[] = $numero;

            $inicio = Carbon::parse($sub['fecha_inicio'])->startOfDay();
            $termino = Carbon::parse($sub['fecha_termino'])->startOfDay();

            if ($termino->lt($inicio)) {
                throw ValidationException::withMessages([
                    "{$prefijo}.fecha_termino" => 'La fecha de término del subperíodo debe ser posterior o igual a su inicio.',
                ]);
            }

            if ($inicio->lt($padreInicio) || $termino->gt($padreTermino)) {
                throw ValidationException::withMessages([
                    "{$prefijo}.fecha_inicio" => 'Las fechas del subperíodo deben estar dentro del rango del período principal.',
                ]);
            }

            if ($anteriorTermino !== null && $inicio->lte($anteriorTermino)) {
                throw ValidationException::withMessages([
                    "{$prefijo}.fecha_inicio" => 'Los subperíodos no pueden solaparse entre sí.',
                ]);
            }

            $anteriorTermino = $termino;
        }
    }

    private function validarDuplicadoRaiz(int $establecimientoId, int $anio, TipoPeriodoEscolar $tipo, ?int $exceptId = null): void
    {
        $query = PeriodoEscolar::query()
            ->raices()
            ->where('establecimiento_id', $establecimientoId)
            ->where('anio', $anio)
            ->where('tipo', $tipo);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'anio' => "Ya existe un período {$tipo->label()} para el año {$anio}.",
            ]);
        }
    }

    private function cerrarActivosDelAnio(int $establecimientoId, int $anio, User $user): void
    {
        PeriodoEscolar::query()
            ->raices()
            ->where('establecimiento_id', $establecimientoId)
            ->where('anio', $anio)
            ->where('estado', EstadoPeriodoEscolar::Activo)
            ->update([
                'estado' => EstadoPeriodoEscolar::Cerrado,
                'closed_at' => now(),
                'updated_by' => $user->id,
            ]);
    }
}
