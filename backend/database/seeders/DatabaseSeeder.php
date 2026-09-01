<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@andrea.com',
            'password' => '123456789',
        ]);

        $this->call([
            EstablecimientoSeeder::class,
            PaisSeeder::class,
            NivelSeeder::class,
            AsignaturaSeeder::class,
            EstadoAsistenciaSeeder::class,
            DemoDataSeeder::class,
            CalendarioAsistenciaSeeder::class,
        ]);
    }
}
