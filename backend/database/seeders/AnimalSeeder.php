<?php

namespace Database\Seeders;

use App\Models\Animal;
use Illuminate\Database\Seeder;

class AnimalSeeder extends Seeder
{
    public function run(): void
    {
        $animales = [
            [
                'codigo' => 'ANI-001',
                'nombre' => 'Luna',
                'especie' => 'Canino',
                'raza' => 'Mestiza',
                'sexo' => 'hembra',
                'fecha_nacimiento' => '2022-05-10',
                'peso_kg' => 18.50,
                'estado' => 'activo',
                'observaciones' => 'Vacunas al día.',
            ],
            [
                'codigo' => 'ANI-002',
                'nombre' => 'Simba',
                'especie' => 'Felino',
                'raza' => 'Doméstico de pelo corto',
                'sexo' => 'macho',
                'fecha_nacimiento' => '2021-11-03',
                'peso_kg' => 5.20,
                'estado' => 'en_tratamiento',
                'observaciones' => 'Control veterinario pendiente.',
            ],
            [
                'codigo' => 'ANI-003',
                'nombre' => 'Canela',
                'especie' => 'Canino',
                'raza' => 'Beagle',
                'sexo' => 'hembra',
                'fecha_nacimiento' => '2020-02-18',
                'peso_kg' => 12.80,
                'estado' => 'adoptado',
                'observaciones' => null,
            ],
        ];

        foreach ($animales as $animal) {
            Animal::updateOrCreate(
                ['codigo' => $animal['codigo']],
                $animal,
            );
        }
    }
}
