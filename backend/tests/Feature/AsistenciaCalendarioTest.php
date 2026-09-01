<?php

namespace Tests\Feature;

use App\Enums\EstadoPeriodoEscolar;
use App\Enums\TipoPeriodoEscolar;
use App\Models\Curso;
use App\Models\Establecimiento;
use App\Models\EstadoAsistencia;
use App\Models\Feriado;
use App\Models\Matricula;
use App\Models\Nivel;
use App\Models\PeriodoEscolar;
use App\Models\User;
use App\Models\Vacacion;
use Database\Seeders\EstablecimientoSeeder;
use Database\Seeders\EstadoAsistenciaSeeder;
use Database\Seeders\NivelSeeder;
use Database\Seeders\PaisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsistenciaCalendarioTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->seed([EstablecimientoSeeder::class, PaisSeeder::class, NivelSeeder::class, EstadoAsistenciaSeeder::class]);
        Sanctum::actingAs($this->user);
    }

    public function test_crea_feriado_recurrente(): void
    {
        $this->postJson('/api/feriados', [
            'nombre' => 'Año Nuevo',
            'tipo' => 'nacional',
            'recurrente' => true,
            'mes' => 1,
            'dia' => 1,
        ])->assertCreated()->assertJsonPath('data.recurrente', true);
    }

    public function test_bloquea_asistencia_en_feriado(): void
    {
        [$curso, $matricula] = $this->prepararCurso();

        Feriado::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'nombre' => 'Feriado test',
            'tipo' => 'nacional',
            'recurrente' => false,
            'fecha' => '2026-03-31',
            'estado' => 'activo',
            'created_by' => $this->user->id,
        ]);

        $presente = EstadoAsistencia::query()->where('codigo', 'presente')->firstOrFail();

        $this->postJson("/api/cursos/{$curso->id}/asistencias", [
            'fecha' => '2026-03-31',
            'registros' => [['matricula_id' => $matricula->id, 'estado_asistencia_id' => $presente->id]],
        ])->assertStatus(422);
    }

    public function test_registra_asistencia_lote(): void
    {
        [$curso, $matricula] = $this->prepararCurso();
        $presente = EstadoAsistencia::query()->where('codigo', 'presente')->firstOrFail();

        $this->postJson("/api/cursos/{$curso->id}/asistencias", [
            'fecha' => '2026-03-02',
            'registros' => [['matricula_id' => $matricula->id, 'estado_asistencia_id' => $presente->id]],
        ])->assertCreated();

        $this->assertDatabaseHas('asistencias', ['matricula_id' => $matricula->id, 'fecha' => '2026-03-02']);
    }

    public function test_no_duplica_asistencia_misma_fecha(): void
    {
        [$curso, $matricula] = $this->prepararCurso();
        $presente = EstadoAsistencia::query()->where('codigo', 'presente')->firstOrFail();
        $ausente = EstadoAsistencia::query()->where('codigo', 'ausente')->firstOrFail();

        $payload = [
            'fecha' => '2026-03-03',
            'registros' => [['matricula_id' => $matricula->id, 'estado_asistencia_id' => $presente->id]],
        ];

        $this->postJson("/api/cursos/{$curso->id}/asistencias", $payload)->assertCreated();
        $this->postJson("/api/cursos/{$curso->id}/asistencias", [
            'fecha' => '2026-03-03',
            'registros' => [['matricula_id' => $matricula->id, 'estado_asistencia_id' => $ausente->id]],
        ])->assertCreated();

        $this->assertDatabaseCount('asistencias', 1);
        $this->assertDatabaseHas('asistencias', ['estado_asistencia_id' => $ausente->id]);
    }

    public function test_bloquea_asistencia_en_vacaciones(): void
    {
        [$curso, $matricula] = $this->prepararCurso();
        $periodo = $curso->periodoEscolar;

        Vacacion::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'nombre' => 'Invierno',
            'fecha_inicio' => '2026-07-06',
            'fecha_fin' => '2026-07-17',
            'estado' => 'activa',
            'created_by' => $this->user->id,
        ]);

        $presente = EstadoAsistencia::query()->where('codigo', 'presente')->firstOrFail();

        $this->postJson("/api/cursos/{$curso->id}/asistencias", [
            'fecha' => '2026-07-10',
            'registros' => [['matricula_id' => $matricula->id, 'estado_asistencia_id' => $presente->id]],
        ])->assertStatus(422);
    }

    public function test_calendario_mensual_marca_feriado(): void
    {
        $periodo = $this->crearPeriodo();

        Feriado::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'nombre' => 'Día del Trabajo',
            'tipo' => 'nacional',
            'recurrente' => true,
            'mes' => 5,
            'dia' => 1,
            'estado' => 'activo',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/calendario-escolar?periodo_escolar_id={$periodo->id}&anio=2026&mes=5");

        $response->assertOk();
        $mayo1 = collect($response->json('data'))->firstWhere('fecha', '2026-05-01');
        $this->assertSame('feriado', $mayo1['tipo_dia']);
    }

    public function test_sincroniza_feriados_desde_boostr(): void
    {
        $periodo = $this->crearPeriodo();

        \Illuminate\Support\Facades\Http::fake([
            'api.boostr.cl/holidays/2026.json' => \Illuminate\Support\Facades\Http::response([
                'status' => 'success',
                'data' => [
                    ['date' => '2026-01-01', 'title' => 'Año Nuevo', 'type' => 'Civil', 'extra' => 'Civil e Irrenunciable'],
                    ['date' => '2026-05-01', 'title' => 'Día Nacional del Trabajo', 'type' => 'Civil', 'extra' => 'Civil e Irrenunciable'],
                ],
            ]),
        ]);

        $this->postJson('/api/feriados/sincronizar-boostr', [
            'anio' => 2026,
            'periodo_escolar_id' => $periodo->id,
        ])->assertOk()
            ->assertJsonPath('data.creados', 2)
            ->assertJsonPath('data.total', 2);

        $this->assertDatabaseHas('feriados', ['nombre' => 'Año Nuevo', 'fecha' => '2026-01-01']);
    }

    public function test_resumen_mensual_alumno(): void
    {
        [$curso, $matricula] = $this->prepararCurso();
        $presente = EstadoAsistencia::query()->where('codigo', 'presente')->firstOrFail();

        $this->postJson("/api/cursos/{$curso->id}/asistencias", [
            'fecha' => '2026-03-02',
            'registros' => [['matricula_id' => $matricula->id, 'estado_asistencia_id' => $presente->id]],
        ])->assertCreated();

        $this->getJson("/api/matriculas/{$matricula->id}/asistencias/resumen?anio=2026&mes=3")
            ->assertOk()
            ->assertJsonPath('data.presentes', 1);
    }

    /**
     * @return array{0: Curso, 1: Matricula}
     */
    private function prepararCurso(): array
    {
        $periodo = $this->crearPeriodo();
        $nivel = Nivel::query()->firstOrFail();

        $curso = Curso::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'nivel_id' => $nivel->id,
            'paralelo' => 'A',
            'estado' => 'activo',
            'created_by' => $this->user->id,
        ]);

        $alumnoId = $this->postJson('/api/alumnos', [
            'tipo_documento' => 'rut',
            'numero_documento' => '123456785',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
        ])->json('data.id');

        $matricula = Matricula::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'curso_id' => $curso->id,
            'alumno_id' => $alumnoId,
            'fecha_matricula' => '2026-03-01',
            'estado' => 'activa',
            'created_by' => $this->user->id,
        ]);

        return [$curso->load('periodoEscolar'), $matricula];
    }

    private function crearPeriodo(): PeriodoEscolar
    {
        return PeriodoEscolar::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Anual,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'estado' => EstadoPeriodoEscolar::Activo,
            'created_by' => $this->user->id,
        ]);
    }
}
