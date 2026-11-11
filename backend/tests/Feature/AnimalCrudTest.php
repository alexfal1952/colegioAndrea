<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnimalCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_manage_animals(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $createResponse = $this->postJson('/api/animales', [
            'codigo' => 'ANI-001',
            'nombre' => 'Luna',
            'especie' => 'Canino',
            'raza' => 'Mestiza',
            'sexo' => 'hembra',
            'fecha_nacimiento' => '2022-05-10',
            'peso_kg' => 18.5,
            'estado' => 'activo',
            'observaciones' => 'Vacunas al día',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('codigo', 'ANI-001')
            ->assertJsonPath('nombre', 'Luna');

        $animalId = $createResponse->json('id');

        $this->getJson('/api/animales')
            ->assertOk()
            ->assertJsonCount(1);

        $this->putJson("/api/animales/{$animalId}", [
            'codigo' => 'ANI-001',
            'nombre' => 'Luna actualizada',
            'especie' => 'Canino',
            'raza' => null,
            'sexo' => 'hembra',
            'fecha_nacimiento' => null,
            'peso_kg' => 19.2,
            'estado' => 'adoptado',
            'observaciones' => null,
        ])->assertOk()->assertJsonPath('estado', 'adoptado');

        $this->deleteJson("/api/animales/{$animalId}")
            ->assertOk();

        $this->assertDatabaseMissing('animales', ['id' => $animalId]);
    }

    public function test_animal_data_is_validated(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Animal::create([
            'codigo' => 'ANI-001',
            'nombre' => 'Luna',
            'especie' => 'Canino',
            'sexo' => 'hembra',
            'estado' => 'activo',
        ]);

        $this->postJson('/api/animales', [
            'codigo' => 'ANI-001',
            'nombre' => '',
            'especie' => '',
            'sexo' => 'otro',
            'fecha_nacimiento' => now()->addDay()->toDateString(),
            'peso_kg' => -1,
            'estado' => 'desconocido',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'codigo',
            'nombre',
            'especie',
            'sexo',
            'fecha_nacimiento',
            'peso_kg',
            'estado',
        ]);
    }
}
