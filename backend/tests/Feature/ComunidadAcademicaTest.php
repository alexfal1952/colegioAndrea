<?php

namespace Tests\Feature;

use App\Enums\EstadoPeriodoEscolar;
use App\Enums\TipoDocumento;
use App\Enums\TipoPeriodoEscolar;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\Establecimiento;
use App\Models\Matricula;
use App\Models\Nivel;
use App\Models\PeriodoEscolar;
use App\Models\User;
use Database\Seeders\EstablecimientoSeeder;
use Database\Seeders\NivelSeeder;
use Database\Seeders\PaisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ComunidadAcademicaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->seed([EstablecimientoSeeder::class, PaisSeeder::class, NivelSeeder::class]);
        Sanctum::actingAs($this->user);
    }

    public function test_crea_persona_con_pasaporte(): void
    {
        $response = $this->postJson('/api/personas', [
            'tipo_documento' => TipoDocumento::Pasaporte->value,
            'numero_documento' => 'AB123456',
            'pais_documento' => 'VE',
            'nombres' => 'María',
            'apellido_paterno' => 'González',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tipo_documento', 'pasaporte')
            ->assertJsonPath('data.pais_documento', 'VE');
    }

    public function test_no_duplica_mismo_documento(): void
    {
        $payload = [
            'tipo_documento' => TipoDocumento::Pasaporte->value,
            'numero_documento' => 'AB123456',
            'pais_documento' => 'VE',
            'nombres' => 'María',
            'apellido_paterno' => 'González',
        ];

        $this->postJson('/api/personas', $payload)->assertCreated();
        $this->postJson('/api/personas', $payload)->assertStatus(422);
    }

    public function test_permite_mismo_numero_con_distinto_tipo(): void
    {
        $this->postJson('/api/personas', [
            'tipo_documento' => TipoDocumento::Rut->value,
            'numero_documento' => '123456785',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
        ])->assertCreated();

        $this->postJson('/api/personas', [
            'tipo_documento' => TipoDocumento::Pasaporte->value,
            'numero_documento' => '123456785',
            'pais_documento' => 'VE',
            'nombres' => 'Pedro',
            'apellido_paterno' => 'López',
        ])->assertCreated();
    }

    public function test_crea_curso_asociado_a_periodo(): void
    {
        $periodo = $this->crearPeriodo(2026);
        $nivel = Nivel::query()->where('codigo', '5B')->firstOrFail();

        $response = $this->postJson('/api/cursos', [
            'periodo_escolar_id' => $periodo->id,
            'nivel_id' => $nivel->id,
            'paralelo' => 'A',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.paralelo', 'A');
    }

    public function test_no_duplica_curso_en_mismo_periodo(): void
    {
        $periodo = $this->crearPeriodo(2026);
        $nivel = Nivel::query()->where('codigo', '5B')->firstOrFail();

        $payload = [
            'periodo_escolar_id' => $periodo->id,
            'nivel_id' => $nivel->id,
            'paralelo' => 'A',
        ];

        $this->postJson('/api/cursos', $payload)->assertCreated();
        $this->postJson('/api/cursos', $payload)->assertStatus(422);
    }

    public function test_permite_mismo_curso_en_distintos_periodos(): void
    {
        $nivel = Nivel::query()->where('codigo', '5B')->firstOrFail();
        $periodo2025 = $this->crearPeriodo(2025);
        $periodo2026 = $this->crearPeriodo(2026);

        $payload = fn (int $periodoId) => [
            'periodo_escolar_id' => $periodoId,
            'nivel_id' => $nivel->id,
            'paralelo' => 'A',
        ];

        $this->postJson('/api/cursos', $payload($periodo2025->id))->assertCreated();
        $this->postJson('/api/cursos', $payload($periodo2026->id))->assertCreated();
    }

    public function test_matricula_vincula_alumno_curso_y_periodo(): void
    {
        $periodo = $this->crearPeriodo(2026);
        $nivel = Nivel::query()->where('codigo', '4B')->firstOrFail();

        $curso = Curso::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'nivel_id' => $nivel->id,
            'paralelo' => 'A',
            'created_by' => $this->user->id,
        ]);

        $alumnoResponse = $this->postJson('/api/alumnos', [
            'tipo_documento' => TipoDocumento::Rut->value,
            'numero_documento' => '187654327',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
        ])->assertCreated();

        $alumnoId = $alumnoResponse->json('data.id');

        $this->postJson('/api/matriculas', [
            'periodo_escolar_id' => $periodo->id,
            'curso_id' => $curso->id,
            'alumno_id' => $alumnoId,
            'fecha_matricula' => '2026-03-01',
        ])->assertCreated();

        $this->assertDatabaseHas('matriculas', [
            'alumno_id' => $alumnoId,
            'periodo_escolar_id' => $periodo->id,
            'curso_id' => $curso->id,
        ]);
    }

    public function test_alumno_no_se_duplica_por_periodo(): void
    {
        $this->assertSame(0, Alumno::query()->count());

        $alumno = $this->postJson('/api/alumnos', [
            'tipo_documento' => TipoDocumento::Rut->value,
            'numero_documento' => '187654327',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
        ])->assertCreated();

        $this->assertDatabaseCount('alumnos', 1);
        $this->assertDatabaseCount('personas', 1);

        $periodo = $this->crearPeriodo(2026);
        $nivel = Nivel::query()->firstOrFail();
        $curso = Curso::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'nivel_id' => $nivel->id,
            'paralelo' => 'A',
            'created_by' => $this->user->id,
        ]);

        $this->postJson('/api/matriculas', [
            'periodo_escolar_id' => $periodo->id,
            'curso_id' => $curso->id,
            'alumno_id' => $alumno->json('data.id'),
        ])->assertCreated();

        $this->assertDatabaseCount('alumnos', 1);
        $this->assertDatabaseCount('matriculas', 1);
    }

    public function test_historial_academico_por_alumno(): void
    {
        $periodo = $this->crearPeriodo(2026);
        $nivel = Nivel::query()->firstOrFail();
        $curso = Curso::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'nivel_id' => $nivel->id,
            'paralelo' => 'A',
            'created_by' => $this->user->id,
        ]);

        $alumnoId = $this->postJson('/api/alumnos', [
            'tipo_documento' => TipoDocumento::Rut->value,
            'numero_documento' => '187654327',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
        ])->json('data.id');

        Matricula::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'curso_id' => $curso->id,
            'alumno_id' => $alumnoId,
            'fecha_matricula' => '2026-03-01',
            'estado' => 'activa',
            'created_by' => $this->user->id,
        ]);

        $this->getJson("/api/alumnos/{$alumnoId}/historial")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    private function crearPeriodo(int $anio): PeriodoEscolar
    {
        return PeriodoEscolar::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'nombre' => (string) $anio,
            'anio' => $anio,
            'tipo' => TipoPeriodoEscolar::Anual,
            'fecha_inicio' => "{$anio}-03-01",
            'fecha_termino' => "{$anio}-12-31",
            'estado' => EstadoPeriodoEscolar::Activo,
            'created_by' => $this->user->id,
        ]);
    }
}
