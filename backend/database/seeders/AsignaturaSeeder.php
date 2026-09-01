<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\User;
use Illuminate\Database\Seeder;

class AsignaturaSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = User::query()->value('id');

        $asignaturas = [
            ['codigo' => 'MAT', 'nombre' => 'Matemática'],
            ['codigo' => 'LEN', 'nombre' => 'Lenguaje y Comunicación'],
            ['codigo' => 'HIS', 'nombre' => 'Historia, Geografía y Ciencias Sociales'],
            ['codigo' => 'CIE', 'nombre' => 'Ciencias Naturales'],
            ['codigo' => 'ING', 'nombre' => 'Inglés'],
            ['codigo' => 'EDF', 'nombre' => 'Educación Física'],
            ['codigo' => 'ART', 'nombre' => 'Artes Visuales'],
            ['codigo' => 'MUS', 'nombre' => 'Música'],
            ['codigo' => 'TEC', 'nombre' => 'Tecnología'],
            ['codigo' => 'ORI', 'nombre' => 'Orientación'],
        ];

        foreach ($asignaturas as $item) {
            Asignatura::query()->updateOrCreate(
                ['establecimiento_id' => null, 'codigo' => $item['codigo']],
                array_merge($item, ['estado' => 'activa', 'created_by' => $creatorId])
            );
        }
    }
}
