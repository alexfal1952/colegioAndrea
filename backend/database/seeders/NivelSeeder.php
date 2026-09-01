<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Seeder;

class NivelSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            ['codigo' => 'PK', 'nombre' => 'Pre-kinder', 'orden' => 1],
            ['codigo' => 'K', 'nombre' => 'Kinder', 'orden' => 2],
            ['codigo' => '1B', 'nombre' => '1° Básico', 'orden' => 3],
            ['codigo' => '2B', 'nombre' => '2° Básico', 'orden' => 4],
            ['codigo' => '3B', 'nombre' => '3° Básico', 'orden' => 5],
            ['codigo' => '4B', 'nombre' => '4° Básico', 'orden' => 6],
            ['codigo' => '5B', 'nombre' => '5° Básico', 'orden' => 7],
            ['codigo' => '6B', 'nombre' => '6° Básico', 'orden' => 8],
            ['codigo' => '7B', 'nombre' => '7° Básico', 'orden' => 9],
            ['codigo' => '8B', 'nombre' => '8° Básico', 'orden' => 10],
            ['codigo' => '1M', 'nombre' => '1° Medio', 'orden' => 11],
            ['codigo' => '2M', 'nombre' => '2° Medio', 'orden' => 12],
            ['codigo' => '3M', 'nombre' => '3° Medio', 'orden' => 13],
            ['codigo' => '4M', 'nombre' => '4° Medio', 'orden' => 14],
        ];

        foreach ($niveles as $nivel) {
            Nivel::query()->updateOrCreate(
                ['establecimiento_id' => null, 'codigo' => $nivel['codigo']],
                array_merge($nivel, ['activo' => true])
            );
        }
    }
}
