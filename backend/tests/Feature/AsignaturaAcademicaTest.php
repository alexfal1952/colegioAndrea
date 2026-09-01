<?php

namespace Tests\Feature;

use App\Enums\EstadoPeriodoEscolar;
use App\Enums\RolAsignacionDocente;
use App\Enums\TipoDocumento;
use App\Enums\TipoPeriodoEscolar;
use App\Models\AsignacionDocente;
use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\CursoAsignatura;
use App\Models\Establecimiento;
use App\Models\Nivel;
use App\Models\PeriodoEscolar;
use App\Models\Profesor;
use App\Models\User;
use Database\Seeders\AsignaturaSeeder;
use Database\Seeders\EstablecimientoSeeder;
use Database\Seeders\NivelSeeder;
use Database\Seeders\PaisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsignaturaAcademicaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->seed([
            EstablecimientoSeeder::class,
            PaisSeeder::class,
            NivelSeeder::class,
            AsignaturaSeeder::class,
        ]);
        Sanctum::actingAs($this->user);
    }

    public function test_crea_asignatura_en_catalogo(): void
    {
        $response = $this->postJson('/api/asignaturas', [
            'codigo' => 'REL',
            'nombre' => 'Religión',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.codigo', 'REL')
            ->assertJsonPath('data.estado', 'activa');
    }

    public function test_no_duplica_codigo_asignatura(): void
    {
        $this->postJson('/api/asignaturas', ['codigo' => 'REL', 'nombre' => 'Religión'])->assertCreated();
        $this->postJson('/api/asignaturas', ['codigo' => 'REL', 'nombre' => 'Otra'])->assertStatus(422);
    }

    public function test_lista_asignaturas_activas(): void
    {
        $response = $this->getJson('/api/asignaturas-activas');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'codigo', 'nombre', 'estado']]]);
    }

    public function test_agrega_asignatura_a_curso(): void
    {
        [$curso, $asignatura] = $this->prepararCursoYAsignatura();

        $response = $this->postJson("/api/cursos/{$curso->id}/asignaturas", [
            'asignatura_id' => $asignatura->id,
            'horas_semanales' => 4,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.asignatura.codigo', 'MAT');

        $this->assertDatabaseHas('curso_asignaturas', [
            'curso_id' => $curso->id,
            'asignatura_id' => $asignatura->id,
        ]);
    }

    public function test_no_duplica_asignatura_en_mismo_curso(): void
    {
        [$curso, $asignatura] = $this->prepararCursoYAsignatura();

        $this->postJson("/api/cursos/{$curso->id}/asignaturas", ['asignatura_id' => $asignatura->id])->assertCreated();
        $this->postJson("/api/cursos/{$curso->id}/asignaturas", ['asignatura_id' => $asignatura->id])->assertStatus(422);
    }

    public function test_asigna_profesor_titular_a_asignatura(): void
    {
        [$curso, $asignatura] = $this->prepararCursoYAsignatura();
        $profesor = $this->crearProfesor();

        $ca = CursoAsignatura::query()->create([
            'curso_id' => $curso->id,
            'asignatura_id' => $asignatura->id,
            'estado' => 'activa',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/curso-asignaturas/{$ca->id}/docentes", [
            'profesor_id' => $profesor->id,
            'rol' => RolAsignacionDocente::Titular->value,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.rol', 'titular')
            ->assertJsonPath('data.profesor.id', $profesor->id);
    }

    public function test_no_permite_dos_titulares_activos(): void
    {
        [$curso, $asignatura] = $this->prepararCursoYAsignatura();
        $prof1 = $this->crearProfesor('111111111');
        $prof2 = $this->crearProfesor('222222222');

        $ca = CursoAsignatura::query()->create([
            'curso_id' => $curso->id,
            'asignatura_id' => $asignatura->id,
            'estado' => 'activa',
            'created_by' => $this->user->id,
        ]);

        $this->postJson("/api/curso-asignaturas/{$ca->id}/docentes", [
            'profesor_id' => $prof1->id,
            'rol' => 'titular',
        ])->assertCreated();

        $this->postJson("/api/curso-asignaturas/{$ca->id}/docentes", [
            'profesor_id' => $prof2->id,
            'rol' => 'titular',
        ])->assertStatus(422);
    }

    public function test_no_quita_asignatura_con_docentes_activos(): void
    {
        [$curso, $asignatura] = $this->prepararCursoYAsignatura();
        $profesor = $this->crearProfesor();

        $ca = CursoAsignatura::query()->create([
            'curso_id' => $curso->id,
            'asignatura_id' => $asignatura->id,
            'estado' => 'activa',
            'created_by' => $this->user->id,
        ]);

        AsignacionDocente::query()->create([
            'curso_asignatura_id' => $ca->id,
            'profesor_id' => $profesor->id,
            'rol' => 'titular',
            'estado' => 'activa',
            'fecha_inicio' => '2026-03-01',
            'created_by' => $this->user->id,
        ]);

        $this->deleteJson("/api/cursos/{$curso->id}/asignaturas/{$ca->id}")
            ->assertStatus(422);
    }

    public function test_detalle_curso_incluye_asignaturas(): void
    {
        [$curso, $asignatura] = $this->prepararCursoYAsignatura();

        CursoAsignatura::query()->create([
            'curso_id' => $curso->id,
            'asignatura_id' => $asignatura->id,
            'estado' => 'activa',
            'created_by' => $this->user->id,
        ]);

        $this->getJson("/api/cursos/{$curso->id}/detalle")
            ->assertOk()
            ->assertJsonPath('data.id', $curso->id)
            ->assertJsonCount(1, 'data.asignaturas');
    }

    /**
     * @return array{0: Curso, 1: Asignatura}
     */
    private function prepararCursoYAsignatura(): array
    {
        $periodo = PeriodoEscolar::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Anual,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'estado' => EstadoPeriodoEscolar::Activo,
            'created_by' => $this->user->id,
        ]);

        $nivel = Nivel::query()->where('codigo', '5B')->firstOrFail();

        $curso = Curso::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'periodo_escolar_id' => $periodo->id,
            'nivel_id' => $nivel->id,
            'paralelo' => 'A',
            'estado' => 'activo',
            'created_by' => $this->user->id,
        ]);

        $asignatura = Asignatura::query()->where('codigo', 'MAT')->firstOrFail();

        return [$curso, $asignatura];
    }

    private function crearProfesor(string $rut = '123456785'): Profesor
    {
        $response = $this->postJson('/api/profesores', [
            'tipo_documento' => TipoDocumento::Rut->value,
            'numero_documento' => $rut,
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
        ])->assertCreated();

        return Profesor::query()->findOrFail($response->json('data.id'));
    }
}
