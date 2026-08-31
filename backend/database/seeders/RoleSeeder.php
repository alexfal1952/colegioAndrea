<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Acceso total al sistema',
            ],
            [
                'name' => 'Supervisor',
                'slug' => 'supervisor',
                'description' => 'Puede gestionar funcionarios y revisar información',
            ],
            [
                'name' => 'Funcionario',
                'slug' => 'funcionario',
                'description' => 'Usuario operativo estándar',
            ],
            [
                'name' => 'Consulta',
                'slug' => 'consulta',
                'description' => 'Solo lectura',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
