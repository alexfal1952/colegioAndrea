<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PezCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_manage_peces(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $createResponse = $this->postJson('/api/peces', [
            'codigo' => 'PEZ-001',
            'nombre' => 'Nemo',
            'especie' => 'Pez payaso',
            'tipo_agua' => 'salada',
            'longitud_cm' => 8.5,
            'estado' => 'activo',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('nombre', 'Nemo');

        $pezId = $createResponse->json('id');

        $this->getJson('/api/peces')
            ->assertOk()
            ->assertJsonCount(1);

        $this->putJson("/api/peces/{$pezId}", [
            'codigo' => 'PEZ-001',
            'nombre' => 'Nemo actualizado',
            'especie' => 'Pez payaso',
            'tipo_agua' => 'salada',
            'longitud_cm' => 9,
            'estado' => 'inactivo',
        ])->assertOk()->assertJsonPath('estado', 'inactivo');

        $this->deleteJson("/api/peces/{$pezId}")
            ->assertOk();

        $this->assertDatabaseMissing('peces', ['id' => $pezId]);
    }

    public function test_pez_data_is_validated(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/peces', [
            'codigo' => '',
            'nombre' => '',
            'especie' => '',
            'tipo_agua' => 'desconocida',
            'longitud_cm' => -1,
            'estado' => 'desconocido',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'codigo',
            'nombre',
            'especie',
            'tipo_agua',
            'longitud_cm',
            'estado',
        ]);
    }
}
