<?php

namespace App\Services;

use App\Enums\EstadoCalendario;
use App\Enums\TipoFeriado;
use App\Models\Establecimiento;
use App\Models\Feriado;
use App\Models\PeriodoEscolar;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class FeriadoService
{
    public function listar(array $filters = [])
    {
        $query = Feriado::query()
            ->where('establecimiento_id', Establecimiento::default()->id);

        if (! empty($filters['periodo_escolar_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('periodo_escolar_id', (int) $filters['periodo_escolar_id'])
                    ->orWhere('recurrente', true);
            });
        }

        if (! empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where('nombre', 'like', "%{$q}%");
        }

        return $query->orderBy('recurrente')->orderBy('fecha')->orderBy('mes')->paginate((int) ($filters['per_page'] ?? 50));
    }

    public function crear(array $data, User $user): Feriado
    {
        $this->validarDatos($data);

        return Feriado::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $data['periodo_escolar_id'] ?? null,
            'nombre' => $data['nombre'],
            'tipo' => TipoFeriado::from($data['tipo'] ?? TipoFeriado::Nacional->value),
            'recurrente' => (bool) ($data['recurrente'] ?? false),
            'fecha' => ($data['recurrente'] ?? false) ? null : ($data['fecha'] ?? null),
            'mes' => ($data['recurrente'] ?? false) ? ($data['mes'] ?? null) : null,
            'dia' => ($data['recurrente'] ?? false) ? ($data['dia'] ?? null) : null,
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => EstadoCalendario::from($data['estado'] ?? EstadoCalendario::Activo->value),
            'created_by' => $user->id,
        ]);
    }

    public function actualizar(Feriado $feriado, array $data, User $user): Feriado
    {
        $merged = array_merge([
            'recurrente' => $feriado->recurrente,
            'fecha' => $feriado->fecha?->format('Y-m-d'),
            'mes' => $feriado->mes,
            'dia' => $feriado->dia,
        ], $data);
        $this->validarDatos($merged);

        $feriado->update([
            'nombre' => $data['nombre'] ?? $feriado->nombre,
            'tipo' => isset($data['tipo']) ? TipoFeriado::from($data['tipo']) : $feriado->tipo,
            'recurrente' => $data['recurrente'] ?? $feriado->recurrente,
            'fecha' => ($merged['recurrente']) ? null : ($merged['fecha'] ?? null),
            'mes' => $merged['recurrente'] ? ($merged['mes'] ?? null) : null,
            'dia' => $merged['recurrente'] ? ($merged['dia'] ?? null) : null,
            'descripcion' => $data['descripcion'] ?? $feriado->descripcion,
            'estado' => isset($data['estado']) ? EstadoCalendario::from($data['estado']) : $feriado->estado,
            'periodo_escolar_id' => $data['periodo_escolar_id'] ?? $feriado->periodo_escolar_id,
            'updated_by' => $user->id,
        ]);

        return $feriado->fresh();
    }

    /**
     * @return array{anio: int, creados: int, actualizados: int, total: int}
     */
    public function sincronizarDesdeBoostr(int $anio, User $user, ?int $periodoEscolarId = null): array
    {
        if ($periodoEscolarId) {
            PeriodoEscolar::query()
                ->where('establecimiento_id', Establecimiento::default()->id)
                ->raices()
                ->where('id', $periodoEscolarId)
                ->where('anio', $anio)
                ->firstOrFail();
        }

        $url = str_replace('{anio}', (string) $anio, config('school.boostr_holidays_url'));
        $response = Http::timeout(20)->acceptJson()->get($url);

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'anio' => 'No se pudo obtener los feriados desde Boostr.cl.',
            ]);
        }

        $payload = $response->json();
        if (($payload['status'] ?? '') !== 'success' || empty($payload['data']) || ! is_array($payload['data'])) {
            throw ValidationException::withMessages([
                'anio' => 'La API de feriados devolvió una respuesta inválida.',
            ]);
        }

        $establecimientoId = Establecimiento::default()->id;
        $creados = 0;
        $actualizados = 0;

        foreach ($payload['data'] as $item) {
            if (empty($item['date']) || empty($item['title'])) {
                continue;
            }

            $fecha = $item['date'];
            $descripcion = trim(($item['extra'] ?? $item['type'] ?? '').' · Fuente: Boostr.cl');

            $existente = Feriado::query()
                ->where('establecimiento_id', $establecimientoId)
                ->where('recurrente', false)
                ->where('fecha', $fecha)
                ->first();

            $datos = [
                'nombre' => $item['title'],
                'tipo' => TipoFeriado::Nacional,
                'recurrente' => false,
                'fecha' => $fecha,
                'mes' => null,
                'dia' => null,
                'descripcion' => $descripcion,
                'estado' => EstadoCalendario::Activo,
                'periodo_escolar_id' => $periodoEscolarId,
                'updated_by' => $user->id,
            ];

            if ($existente) {
                $existente->update($datos);
                $actualizados++;
            } else {
                Feriado::query()->create(array_merge($datos, [
                    'establecimiento_id' => $establecimientoId,
                    'created_by' => $user->id,
                ]));
                $creados++;
            }
        }

        return [
            'anio' => $anio,
            'creados' => $creados,
            'actualizados' => $actualizados,
            'total' => count($payload['data']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validarDatos(array $data): void
    {
        if ($data['recurrente'] ?? false) {
            if (empty($data['mes']) || empty($data['dia'])) {
                throw ValidationException::withMessages(['mes' => 'Mes y día son obligatorios para feriados recurrentes.']);
            }
        } elseif (empty($data['fecha'])) {
            throw ValidationException::withMessages(['fecha' => 'La fecha es obligatoria para feriados no recurrentes.']);
        }
    }
}
