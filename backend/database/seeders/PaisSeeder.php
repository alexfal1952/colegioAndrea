<?php

namespace Database\Seeders;

use App\Models\Pais;
use Illuminate\Database\Seeder;

class PaisSeeder extends Seeder
{
    public function run(): void
    {
        $paises = [
            ['codigo' => 'CL', 'nombre' => 'Chile'],
            ['codigo' => 'AR', 'nombre' => 'Argentina'],
            ['codigo' => 'PE', 'nombre' => 'Perú'],
            ['codigo' => 'BO', 'nombre' => 'Bolivia'],
            ['codigo' => 'VE', 'nombre' => 'Venezuela'],
            ['codigo' => 'CO', 'nombre' => 'Colombia'],
            ['codigo' => 'EC', 'nombre' => 'Ecuador'],
            ['codigo' => 'BR', 'nombre' => 'Brasil'],
            ['codigo' => 'MX', 'nombre' => 'México'],
            ['codigo' => 'ES', 'nombre' => 'España'],
            ['codigo' => 'US', 'nombre' => 'Estados Unidos'],
            ['codigo' => 'HT', 'nombre' => 'Haití'],
            ['codigo' => 'CU', 'nombre' => 'Cuba'],
            ['codigo' => 'DO', 'nombre' => 'República Dominicana'],
        ];

        foreach ($paises as $pais) {
            Pais::query()->updateOrCreate(['codigo' => $pais['codigo']], $pais);
        }
    }
}
