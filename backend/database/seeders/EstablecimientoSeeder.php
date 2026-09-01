<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use Illuminate\Database\Seeder;

class EstablecimientoSeeder extends Seeder
{
    public function run(): void
    {
        Establecimiento::query()->firstOrCreate(
            ['codigo' => 'DEFAULT'],
            [
                'nombre' => 'Establecimiento Demo',
                'activo' => true,
            ]
        );
    }
}
