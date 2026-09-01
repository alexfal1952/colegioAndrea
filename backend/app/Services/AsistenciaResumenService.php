<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\EstadoAsistencia;
use App\Models\Matricula;
use App\Models\PeriodoEscolar;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AsistenciaResumenService
{
    public function __construct(private readonly CalendarioEscolarService $calendarioService) {}

    /**
     * @return array<string, mixed>
     */
    public function resumenMatricula(Matricula $matricula, int $anio, int $mes): array
    {
        $matricula->load(['alumno.persona', 'curso.periodoEscolar']);
        $periodo = $matricula->curso->periodoEscolar;
        $curso = $matricula->curso;

        $desde = Carbon::create($anio, $mes, 1)->format('Y-m-d');
        $hasta = Carbon::create($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

        $diasLectivos = $this->calendarioService->diasLectivosEnRango($periodo, $desde, $hasta, $curso);
        $config = $this->calendarioService->obtenerConfig($periodo->id);

        $asistencias = Asistencia::query()
            ->with('estadoAsistencia')
            ->where('matricula_id', $matricula->id)
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')
            ->get();

        $estados = $this->contarEstados($asistencias, $diasLectivos, $config->dia_sin_registro_es_ausente);

        return [
            'matricula_id' => $matricula->id,
            'alumno' => $matricula->alumno,
            'curso' => $curso,
            'anio' => $anio,
            'mes' => $mes,
            'dias_lectivos' => $diasLectivos->count(),
            'registros' => $asistencias,
            'presentes' => $estados['presentes'],
            'ausentes' => $estados['ausentes'],
            'atrasos' => $estados['atrasos'],
            'justificados' => $estados['justificados'],
            'porcentaje_asistencia' => $estados['porcentaje'],
            'detalle' => $asistencias->map(fn ($a) => [
                'fecha' => $a->fecha->format('Y-m-d'),
                'estado' => $a->estadoAsistencia->nombre,
                'codigo' => $a->estadoAsistencia->codigo,
                'observacion' => $a->observacion,
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resumenCurso(Curso $curso, int $anio, int $mes): array
    {
        $curso->load('periodoEscolar');
        $matriculas = Matricula::query()
            ->with('alumno.persona')
            ->where('curso_id', $curso->id)
            ->where('estado', 'activa')
            ->get();

        $alumnos = $matriculas->map(fn (Matricula $m) => $this->resumenMatricula($m, $anio, $mes));

        $totalPresentes = $alumnos->sum('presentes');
        $totalAusentes = $alumnos->sum('ausentes');
        $totalAtrasos = $alumnos->sum('atrasos');
        $promedio = $alumnos->count() > 0 ? round($alumnos->avg('porcentaje_asistencia'), 1) : 0;

        return [
            'curso' => $curso,
            'anio' => $anio,
            'mes' => $mes,
            'total_alumnos' => $alumnos->count(),
            'promedio_asistencia' => $promedio,
            'total_presentes' => $totalPresentes,
            'total_ausentes' => $totalAusentes,
            'total_atrasos' => $totalAtrasos,
            'alumnos' => $alumnos,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function historialAlumno(int $alumnoId, int $periodoId): array
    {
        $matricula = Matricula::query()
            ->with(['alumno.persona', 'curso.nivel'])
            ->where('alumno_id', $alumnoId)
            ->where('periodo_escolar_id', $periodoId)
            ->firstOrFail();

        $periodo = PeriodoEscolar::query()->findOrFail($periodoId);
        $inicio = $periodo->fecha_inicio->format('Y-m-d');
        $fin = min(now()->format('Y-m-d'), $periodo->fecha_termino->format('Y-m-d'));

        $meses = collect();
        $cursor = Carbon::parse($inicio)->startOfMonth();
        $finCarbon = Carbon::parse($fin);

        while ($cursor <= $finCarbon) {
            $resumen = $this->resumenMatricula($matricula, $cursor->year, $cursor->month);
            if ($resumen['dias_lectivos'] > 0) {
                $meses->push([
                    'anio' => $cursor->year,
                    'mes' => $cursor->month,
                    'nombre_mes' => $cursor->translatedFormat('F'),
                    'porcentaje_asistencia' => $resumen['porcentaje_asistencia'],
                    'presentes' => $resumen['presentes'],
                    'ausentes' => $resumen['ausentes'],
                ]);
            }
            $cursor->addMonth();
        }

        $acumulado = $meses->count() > 0 ? round($meses->avg('porcentaje_asistencia'), 1) : 0;

        return [
            'matricula' => $matricula,
            'meses' => $meses,
            'acumulado_anual' => $acumulado,
        ];
    }

    /**
     * @param  Collection<int, string>  $diasLectivos
     * @return array{presentes: int, ausentes: int, atrasos: int, justificados: int, porcentaje: float}
     */
    private function contarEstados(Collection $asistencias, Collection $diasLectivos, bool $sinRegistroEsAusente): array
    {
        $porFecha = $asistencias->keyBy(fn ($a) => $a->fecha->format('Y-m-d'));
        $presentes = 0;
        $ausentes = 0;
        $atrasos = 0;
        $justificados = 0;
        $hoy = now()->format('Y-m-d');

        foreach ($diasLectivos as $fecha) {
            $registro = $porFecha->get($fecha);
            if (! $registro) {
                if ($sinRegistroEsAusente && $fecha < $hoy) {
                    $ausentes++;
                }
                continue;
            }

            $estado = $registro->estadoAsistencia;
            if ($estado->cuenta_como_presente) {
                $presentes++;
            }
            if ($estado->cuenta_como_ausencia) {
                $ausentes++;
            }
            if ($estado->cuenta_como_atraso) {
                $atrasos++;
            }
            if ($estado->cuenta_como_justificado) {
                $justificados++;
            }
        }

        $totalLectivos = max($diasLectivos->count(), 1);
        $porcentaje = round(($presentes / $totalLectivos) * 100, 1);

        return compact('presentes', 'ausentes', 'atrasos', 'justificados', 'porcentaje');
    }
}
