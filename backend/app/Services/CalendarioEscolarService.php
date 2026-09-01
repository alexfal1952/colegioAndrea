<?php

namespace App\Services;

use App\Enums\EstadoCalendario;
use App\Enums\EstadoVacacion;
use App\Enums\TipoDiaCalendario;
use App\Models\ConfiguracionCalendario;
use App\Models\Curso;
use App\Models\Establecimiento;
use App\Models\Feriado;
use App\Models\PeriodoEscolar;
use App\Models\SuspensionClases;
use App\Models\User;
use App\Models\Vacacion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CalendarioEscolarService
{
    public function obtenerConfig(?int $periodoId = null): ConfiguracionCalendario
    {
        $establecimiento = Establecimiento::default();

        if ($periodoId) {
            $config = ConfiguracionCalendario::query()
                ->where('establecimiento_id', $establecimiento->id)
                ->where('periodo_escolar_id', $periodoId)
                ->first();

            if ($config) {
                return $config;
            }
        }

        $base = ConfiguracionCalendario::query()
            ->where('establecimiento_id', $establecimiento->id)
            ->whereNull('periodo_escolar_id')
            ->first();

        if ($base) {
            return $base;
        }

        return new ConfiguracionCalendario([
            'sabado_lectivo' => false,
            'domingo_lectivo' => false,
            'permitir_asistencia_en_feriado' => false,
            'permitir_asistencia_en_vacaciones' => false,
            'permitir_asistencia_en_suspension' => false,
            'permitir_asistencia_fin_semana' => false,
            'dia_sin_registro_es_ausente' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function evaluarDia(string $fecha, PeriodoEscolar $periodo, ?Curso $curso = null): array
    {
        $config = $this->obtenerConfig($periodo->id);
        $fechaCarbon = Carbon::parse($fecha);
        $inicio = $periodo->fecha_inicio->format('Y-m-d');
        $fin = $periodo->fecha_termino->format('Y-m-d');

        if ($fecha < $inicio || $fecha > $fin) {
            return $this->resultado(TipoDiaCalendario::FueraPeriodo, false, 'La fecha está fuera del período escolar.');
        }

        $vacacion = Vacacion::query()
            ->where('periodo_escolar_id', $periodo->id)
            ->where('estado', EstadoVacacion::Activa)
            ->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->first();

        if ($vacacion) {
            return $this->resultado(
                TipoDiaCalendario::Vacacion,
                $config->permitir_asistencia_en_vacaciones,
                "Vacaciones: {$vacacion->nombre}.",
                ['vacacion' => $vacacion]
            );
        }

        $feriado = $this->buscarFeriado($fecha, $periodo);
        if ($feriado) {
            return $this->resultado(
                TipoDiaCalendario::Feriado,
                $config->permitir_asistencia_en_feriado,
                "Feriado: {$feriado->nombre}.",
                ['feriado' => $feriado]
            );
        }

        $suspension = $this->buscarSuspension($fecha, $periodo, $curso);
        if ($suspension) {
            return $this->resultado(
                TipoDiaCalendario::Suspension,
                $config->permitir_asistencia_en_suspension,
                "Suspensión de clases: {$suspension->nombre}.",
                ['suspension' => $suspension]
            );
        }

        $diaSemana = $fechaCarbon->dayOfWeek;
        if ($diaSemana === Carbon::SATURDAY && ! $config->sabado_lectivo) {
            return $this->resultado(
                TipoDiaCalendario::FinSemana,
                $config->permitir_asistencia_fin_semana,
                'Sábado no lectivo según configuración.'
            );
        }

        if ($diaSemana === Carbon::SUNDAY && ! $config->domingo_lectivo) {
            return $this->resultado(
                TipoDiaCalendario::FinSemana,
                $config->permitir_asistencia_fin_semana,
                'Domingo no lectivo según configuración.'
            );
        }

        return $this->resultado(TipoDiaCalendario::Lectivo, true, null);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function calendarioMensual(int $periodoId, int $anio, int $mes, ?int $cursoId = null): array
    {
        $periodo = $this->validarPeriodoRaiz($periodoId);
        $curso = $cursoId ? Curso::query()->findOrFail($cursoId) : null;

        $inicioMes = Carbon::create($anio, $mes, 1);
        $finMes = $inicioMes->copy()->endOfMonth();
        $dias = [];

        foreach (CarbonPeriod::create($inicioMes, $finMes) as $dia) {
            $fecha = $dia->format('Y-m-d');
            $eval = $this->evaluarDia($fecha, $periodo, $curso);
            $dias[] = array_merge(['fecha' => $fecha, 'dia_semana' => $dia->dayOfWeek], $eval);
        }

        return $dias;
    }

    /**
     * @return Collection<int, string>
     */
    public function diasLectivosEnRango(PeriodoEscolar $periodo, string $desde, string $hasta, ?Curso $curso = null): Collection
    {
        $dias = collect();
        foreach (CarbonPeriod::create($desde, $hasta) as $dia) {
            $fecha = $dia->format('Y-m-d');
            $eval = $this->evaluarDia($fecha, $periodo, $curso);
            if ($eval['tipo_dia'] === TipoDiaCalendario::Lectivo->value) {
                $dias->push($fecha);
            }
        }

        return $dias;
    }

    public function actualizarConfig(array $data, User $user): ConfiguracionCalendario
    {
        $establecimiento = Establecimiento::default();
        $periodoId = $data['periodo_escolar_id'] ?? null;

        if ($periodoId) {
            $this->validarPeriodoRaiz((int) $periodoId);
        }

        return ConfiguracionCalendario::query()->updateOrCreate(
            [
                'establecimiento_id' => $establecimiento->id,
                'periodo_escolar_id' => $periodoId,
            ],
            array_merge($data, [
                'establecimiento_id' => $establecimiento->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ])
        );
    }

    private function buscarFeriado(string $fecha, PeriodoEscolar $periodo): ?Feriado
    {
        $establecimientoId = $periodo->establecimiento_id;

        $especifico = Feriado::query()
            ->where('establecimiento_id', $establecimientoId)
            ->where('recurrente', false)
            ->where('fecha', $fecha)
            ->where('estado', EstadoCalendario::Activo)
            ->first();

        if ($especifico) {
            return $especifico;
        }

        $carbon = Carbon::parse($fecha);

        return Feriado::query()
            ->where('establecimiento_id', $establecimientoId)
            ->where('recurrente', true)
            ->where('mes', $carbon->month)
            ->where('dia', $carbon->day)
            ->where('estado', EstadoCalendario::Activo)
            ->first();
    }

    private function buscarSuspension(string $fecha, PeriodoEscolar $periodo, ?Curso $curso): ?SuspensionClases
    {
        $query = SuspensionClases::query()
            ->where('periodo_escolar_id', $periodo->id)
            ->where('fecha', $fecha)
            ->where('estado', EstadoVacacion::Activa);

        if ($curso) {
            $suspension = (clone $query)->where('curso_id', $curso->id)->first();
            if ($suspension) {
                return $suspension;
            }
        }

        return $query->whereNull('curso_id')->first();
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function resultado(TipoDiaCalendario $tipo, bool $permite, ?string $motivo, array $extra = []): array
    {
        return array_merge([
            'tipo_dia' => $tipo->value,
            'tipo_dia_label' => $tipo->label(),
            'permite_registrar_asistencia' => $permite,
            'motivo' => $motivo,
        ], $extra);
    }

    public function validarPeriodoRaiz(int $periodoId): PeriodoEscolar
    {
        return PeriodoEscolar::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->raices()
            ->findOrFail($periodoId);
    }

    public function asegurarDiaLectivo(string $fecha, PeriodoEscolar $periodo, ?Curso $curso = null): void
    {
        $eval = $this->evaluarDia($fecha, $periodo, $curso);
        if (! $eval['permite_registrar_asistencia']) {
            throw ValidationException::withMessages([
                'fecha' => $eval['motivo'] ?? 'No corresponde registrar asistencia en esta fecha.',
            ]);
        }
    }
}
