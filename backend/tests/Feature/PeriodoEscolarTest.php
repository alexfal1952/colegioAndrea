<?php

namespace Tests\Feature;

use App\Enums\EstadoPeriodoEscolar;
use App\Enums\TipoPeriodoEscolar;
use App\Models\Establecimiento;
use App\Models\PeriodoEscolar;
use App\Models\User;
use Database\Seeders\EstablecimientoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PeriodoEscolarTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->seed(EstablecimientoSeeder::class);
        Sanctum::actingAs($this->user);
    }

    public function test_puede_crear_periodo_anual(): void
    {
        $response = $this->postJson('/api/periodos-escolares', [
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Anual->value,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'estado' => EstadoPeriodoEscolar::Borrador->value,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tipo', 'anual')
            ->assertJsonPath('data.anio', 2026);

        $this->assertDatabaseCount('periodos_escolares', 1);
    }

    public function test_puede_crear_periodo_semestral_con_dos_subperiodos(): void
    {
        $response = $this->postJson('/api/periodos-escolares', [
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Semestral->value,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'estado' => EstadoPeriodoEscolar::Borrador->value,
            'subperiodos' => [
                [
                    'nombre' => 'Primer semestre',
                    'numero' => 1,
                    'fecha_inicio' => '2026-03-01',
                    'fecha_termino' => '2026-07-31',
                ],
                [
                    'nombre' => 'Segundo semestre',
                    'numero' => 2,
                    'fecha_inicio' => '2026-08-01',
                    'fecha_termino' => '2026-12-31',
                ],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('periodos_escolares', 3);
    }

    public function test_rechaza_trimestral_con_dos_subperiodos(): void
    {
        $response = $this->postJson('/api/periodos-escolares', [
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Trimestral->value,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'subperiodos' => [
                ['nombre' => 'T1', 'numero' => 1, 'fecha_inicio' => '2026-03-01', 'fecha_termino' => '2026-05-31'],
                ['nombre' => 'T2', 'numero' => 2, 'fecha_inicio' => '2026-06-01', 'fecha_termino' => '2026-08-31'],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_rechaza_termino_antes_de_inicio(): void
    {
        $response = $this->postJson('/api/periodos-escolares', [
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Anual->value,
            'fecha_inicio' => '2026-12-31',
            'fecha_termino' => '2026-03-01',
        ]);

        $response->assertStatus(422);
    }

    public function test_rechaza_solapamiento_entre_semestres(): void
    {
        $response = $this->postJson('/api/periodos-escolares', [
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Semestral->value,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'subperiodos' => [
                ['nombre' => 'S1', 'numero' => 1, 'fecha_inicio' => '2026-03-01', 'fecha_termino' => '2026-08-15'],
                ['nombre' => 'S2', 'numero' => 2, 'fecha_inicio' => '2026-08-01', 'fecha_termino' => '2026-12-31'],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_rechaza_subperiodo_fuera_del_rango_padre(): void
    {
        $response = $this->postJson('/api/periodos-escolares', [
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Semestral->value,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'subperiodos' => [
                ['nombre' => 'S1', 'numero' => 1, 'fecha_inicio' => '2026-01-01', 'fecha_termino' => '2026-07-31'],
                ['nombre' => 'S2', 'numero' => 2, 'fecha_inicio' => '2026-08-01', 'fecha_termino' => '2026-12-31'],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_solo_un_periodo_activo_por_anio(): void
    {
        $establecimiento = Establecimiento::default();

        $existente = PeriodoEscolar::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'nombre' => '2026 Anual',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Anual,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'estado' => EstadoPeriodoEscolar::Activo,
            'created_by' => $this->user->id,
        ]);

        $nuevo = PeriodoEscolar::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'nombre' => '2026 Semestral',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Semestral,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'estado' => EstadoPeriodoEscolar::Borrador,
            'created_by' => $this->user->id,
        ]);

        $response = $this->patchJson("/api/periodos-escolares/{$nuevo->id}/activar");

        $response->assertOk()
            ->assertJsonPath('data.estado', 'activo');

        $this->assertDatabaseHas('periodos_escolares', [
            'id' => $existente->id,
            'estado' => EstadoPeriodoEscolar::Cerrado->value,
        ]);
    }

    public function test_no_edita_periodo_cerrado(): void
    {
        $periodo = $this->crearPeriodoCerrado();

        $response = $this->putJson("/api/periodos-escolares/{$periodo->id}", [
            'nombre' => 'Nombre modificado',
        ]);

        $response->assertStatus(422);
    }

    public function test_registra_created_by(): void
    {
        $response = $this->postJson('/api/periodos-escolares', [
            'nombre' => '2027',
            'anio' => 2027,
            'tipo' => TipoPeriodoEscolar::Anual->value,
            'fecha_inicio' => '2027-03-01',
            'fecha_termino' => '2027-12-31',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.auditoria.created_by.id', $this->user->id);
    }

    public function test_elimina_solo_borrador(): void
    {
        $periodo = $this->crearPeriodoBorrador();

        $this->deleteJson("/api/periodos-escolares/{$periodo->id}")
            ->assertOk();

        $this->assertDatabaseMissing('periodos_escolares', ['id' => $periodo->id]);
    }

    private function crearPeriodoBorrador(): PeriodoEscolar
    {
        return PeriodoEscolar::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'nombre' => '2028',
            'anio' => 2028,
            'tipo' => TipoPeriodoEscolar::Anual,
            'fecha_inicio' => '2028-03-01',
            'fecha_termino' => '2028-12-31',
            'estado' => EstadoPeriodoEscolar::Borrador,
            'created_by' => $this->user->id,
        ]);
    }

    private function crearPeriodoCerrado(): PeriodoEscolar
    {
        return PeriodoEscolar::query()->create([
            'establecimiento_id' => Establecimiento::default()->id,
            'nombre' => '2025',
            'anio' => 2025,
            'tipo' => TipoPeriodoEscolar::Anual,
            'fecha_inicio' => '2025-03-01',
            'fecha_termino' => '2025-12-31',
            'estado' => EstadoPeriodoEscolar::Cerrado,
            'created_by' => $this->user->id,
            'closed_at' => now(),
        ]);
    }
}
