<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use App\Models\EstadoAsistencia;
use App\Models\User;
use Illuminate\Database\Seeder;

class EstadoAsistenciaSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['codigo' => 'presente', 'nombre' => 'Presente', 'cuenta_como_presente' => true, 'color' => '#198754', 'orden' => 1],
            ['codigo' => 'ausente', 'nombre' => 'Ausente', 'cuenta_como_ausencia' => true, 'color' => '#dc3545', 'orden' => 2],
            ['codigo' => 'atrasado', 'nombre' => 'Atrasado', 'cuenta_como_presente' => true, 'cuenta_como_atraso' => true, 'color' => '#ffc107', 'orden' => 3],
            ['codigo' => 'retirado', 'nombre' => 'Retirado durante la jornada', 'cuenta_como_presente' => true, 'color' => '#0dcaf0', 'orden' => 4],
            ['codigo' => 'justificado', 'nombre' => 'Justificado', 'cuenta_como_presente' => true, 'cuenta_como_justificado' => true, 'color' => '#6f42c1', 'orden' => 5],
        ];

        foreach ($estados as $estado) {
            EstadoAsistencia::query()->updateOrCreate(
                ['establecimiento_id' => null, 'codigo' => $estado['codigo']],
                array_merge($estado, ['activo' => true, 'es_sistema' => true])
            );
        }
    }
}
