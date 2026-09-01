<?php

namespace Database\Seeders;

use App\Enums\EstadoCalendario;
use App\Enums\EstadoMatricula;
use App\Enums\EstadoVacacion;
use App\Enums\TipoFeriado;
use App\Enums\TipoSuspensionClases;
use App\Models\Asistencia;
use App\Models\ConfiguracionCalendario;
use App\Models\Curso;
use App\Models\Establecimiento;
use App\Models\EstadoAsistencia;
use App\Models\Feriado;
use App\Models\Matricula;
use App\Models\PeriodoEscolar;
use App\Models\SuspensionClases;
use App\Models\User;
use App\Models\Vacacion;
use App\Services\CalendarioEscolarService;
use Illuminate\Database\Seeder;

class CalendarioAsistenciaSeeder extends Seeder
{
    /** Fecha simulada como "hoy" para dejar sin registrar y probar el flujo manual. */
    private const FECHA_HOY_DEMO = '2026-08-31';

    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@andrea.com')->first()
            ?? User::query()->first();
        if (! $admin) {
            return;
        }

        $establecimiento = Establecimiento::default();
        $periodo2026 = PeriodoEscolar::query()
            ->where('establecimiento_id', $establecimiento->id)
            ->where('anio', 2026)
            ->raices()
            ->first();

        if (! $periodo2026) {
            return;
        }

        ConfiguracionCalendario::query()->updateOrCreate(
            ['establecimiento_id' => $establecimiento->id, 'periodo_escolar_id' => null],
            [
                'sabado_lectivo' => false,
                'domingo_lectivo' => false,
                'dia_sin_registro_es_ausente' => true,
                'created_by' => $admin->id,
            ]
        );

        $feriadosRecurrentes = [
            ['nombre' => 'Año Nuevo', 'mes' => 1, 'dia' => 1],
            ['nombre' => 'Día del Trabajo', 'mes' => 5, 'dia' => 1],
            ['nombre' => 'Glorias Navales', 'mes' => 5, 'dia' => 21],
            ['nombre' => 'Fiestas Patrias', 'mes' => 9, 'dia' => 18],
            ['nombre' => 'Día de las Glorias del Ejército', 'mes' => 9, 'dia' => 19],
            ['nombre' => 'Navidad', 'mes' => 12, 'dia' => 25],
        ];

        foreach ($feriadosRecurrentes as $f) {
            Feriado::query()->updateOrCreate(
                ['establecimiento_id' => $establecimiento->id, 'nombre' => $f['nombre'], 'recurrente' => true],
                array_merge($f, [
                    'tipo' => TipoFeriado::Nacional,
                    'recurrente' => true,
                    'estado' => EstadoCalendario::Activo,
                    'created_by' => $admin->id,
                ])
            );
        }

        Feriado::query()->updateOrCreate(
            [
                'establecimiento_id' => $establecimiento->id,
                'nombre' => 'Asunción de la Virgen',
                'recurrente' => false,
                'fecha' => '2026-08-15',
            ],
            [
                'tipo' => TipoFeriado::Nacional,
                'fecha' => '2026-08-15',
                'recurrente' => false,
                'estado' => EstadoCalendario::Activo,
                'descripcion' => 'Feriado agosto — demo calendario',
                'created_by' => $admin->id,
            ]
        );

        Vacacion::query()->updateOrCreate(
            ['periodo_escolar_id' => $periodo2026->id, 'nombre' => 'Vacaciones de invierno'],
            [
                'establecimiento_id' => $establecimiento->id,
                'fecha_inicio' => '2026-07-06',
                'fecha_fin' => '2026-07-17',
                'estado' => EstadoVacacion::Activa,
                'created_by' => $admin->id,
            ]
        );

        SuspensionClases::query()->updateOrCreate(
            ['periodo_escolar_id' => $periodo2026->id, 'fecha' => '2026-10-15', 'curso_id' => null],
            [
                'establecimiento_id' => $establecimiento->id,
                'nombre' => 'Jornada de capacitación docente',
                'motivo' => 'Capacitación interna',
                'tipo' => TipoSuspensionClases::Total,
                'estado' => EstadoVacacion::Activa,
                'created_by' => $admin->id,
            ]
        );

        $estados = $this->cargarEstados();
        if ($estados === null) {
            return;
        }

        $calendario = app(CalendarioEscolarService::class);
        $cursos = Curso::query()
            ->where('periodo_escolar_id', $periodo2026->id)
            ->get();

        foreach ($cursos as $curso) {
            $this->sembrarAsistenciaCurso(
                $calendario,
                $periodo2026,
                $curso,
                $establecimiento,
                $estados,
                $admin
            );
        }
    }

    /**
     * @return array<string, EstadoAsistencia>|null
     */
    private function cargarEstados(): ?array
    {
        $codigos = ['presente', 'ausente', 'atrasado', 'justificado'];
        $estados = [];

        foreach ($codigos as $codigo) {
            $estado = EstadoAsistencia::query()->where('codigo', $codigo)->first();
            if (! $estado) {
                return null;
            }
            $estados[$codigo] = $estado;
        }

        return $estados;
    }

    /**
     * @param  array<string, EstadoAsistencia>  $estados
     */
    private function sembrarAsistenciaCurso(
        CalendarioEscolarService $calendario,
        PeriodoEscolar $periodo,
        Curso $curso,
        Establecimiento $establecimiento,
        array $estados,
        User $admin
    ): void {
        $matriculas = Matricula::query()
            ->where('curso_id', $curso->id)
            ->where('estado', EstadoMatricula::Activa)
            ->orderBy('id')
            ->get();

        if ($matriculas->isEmpty()) {
            return;
        }

        $diasLectivos = $calendario
            ->diasLectivosEnRango($periodo, '2026-03-01', '2026-08-31', $curso)
            ->reject(fn (string $fecha) => $fecha === self::FECHA_HOY_DEMO);

        foreach ($matriculas as $indice => $matricula) {
            $perfil = $indice % 4;

            foreach ($diasLectivos as $fecha) {
                $estado = $this->resolverEstado($perfil, $matricula->id, $fecha, $estados);
                if ($estado === null) {
                    continue;
                }

                Asistencia::query()->updateOrCreate(
                    ['matricula_id' => $matricula->id, 'fecha' => $fecha, 'turno' => 'completa'],
                    [
                        'establecimiento_id' => $establecimiento->id,
                        'periodo_escolar_id' => $periodo->id,
                        'curso_id' => $curso->id,
                        'estado_asistencia_id' => $estado->id,
                        'observacion' => $estado->codigo === 'justificado' ? 'Licencia médica demo' : null,
                        'created_by' => $admin->id,
                    ]
                );
            }
        }
    }

    /**
     * Perfiles de asistencia para probar resúmenes y alertas:
     * 0 → excelente (~92 %), 1 → en riesgo (~35 %), 2 → regular (~75 %), 3 → límite (~85 %).
     *
     * @param  array<string, EstadoAsistencia>  $estados
     */
    private function resolverEstado(int $perfil, int $matriculaId, string $fecha, array $estados): ?EstadoAsistencia
    {
        $h = crc32("{$matriculaId}-{$fecha}") % 100;

        return match ($perfil) {
            0 => $h < 8 ? $estados['atrasado'] : $estados['presente'],
            1 => match (true) {
                $h < 35 => $estados['presente'],
                $h < 55 => $estados['atrasado'],
                $h < 75 => $estados['ausente'],
                default => $estados['justificado'],
            },
            2 => match (true) {
                $h < 72 => $estados['presente'],
                $h < 88 => $estados['atrasado'],
                default => $estados['ausente'],
            },
            default => $h < 82 ? $estados['presente'] : $estados['ausente'],
        };
    }
}
