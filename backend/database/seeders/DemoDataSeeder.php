<?php

namespace Database\Seeders;

use App\Enums\EstadoAsignacionDocente;
use App\Enums\EstadoCurso;
use App\Enums\EstadoMatricula;
use App\Enums\EstadoPeriodoEscolar;
use App\Enums\JornadaCurso;
use App\Enums\RolAsignacionDocente;
use App\Enums\TipoDocumento;
use App\Enums\TipoPeriodoEscolar;
use App\Models\Alumno;
use App\Models\AsignacionDocente;
use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\CursoAsignatura;
use App\Models\Establecimiento;
use App\Models\Matricula;
use App\Models\Nivel;
use App\Models\PeriodoEscolar;
use App\Models\Persona;
use App\Models\Profesor;
use App\Models\User;
use App\Services\PeriodoEscolarService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@andrea.com')->firstOrFail();
        $establecimiento = Establecimiento::default();
        $periodoService = app(PeriodoEscolarService::class);

        // Período 2025 cerrado (anual)
        $periodo2025 = PeriodoEscolar::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'nombre' => '2025',
            'anio' => 2025,
            'tipo' => TipoPeriodoEscolar::Anual,
            'fecha_inicio' => '2025-03-01',
            'fecha_termino' => '2025-12-31',
            'estado' => EstadoPeriodoEscolar::Cerrado,
            'descripcion' => 'Año escolar 2025',
            'created_by' => $admin->id,
            'closed_at' => now()->subMonths(2),
        ]);

        // Período 2026 activo semestral
        $periodo2026 = $periodoService->crear([
            'nombre' => '2026',
            'anio' => 2026,
            'tipo' => TipoPeriodoEscolar::Semestral->value,
            'fecha_inicio' => '2026-03-01',
            'fecha_termino' => '2026-12-31',
            'estado' => EstadoPeriodoEscolar::Borrador->value,
            'descripcion' => 'Año escolar 2026 semestral',
            'subperiodos' => [
                ['nombre' => 'Primer semestre', 'numero' => 1, 'fecha_inicio' => '2026-03-01', 'fecha_termino' => '2026-07-31'],
                ['nombre' => 'Segundo semestre', 'numero' => 2, 'fecha_inicio' => '2026-08-01', 'fecha_termino' => '2026-12-31'],
            ],
        ], $admin);

        $periodoService->activar($periodo2026, $admin);

        $nivel4B = Nivel::query()->where('codigo', '4B')->firstOrFail();
        $nivel5B = Nivel::query()->where('codigo', '5B')->firstOrFail();
        $nivel1M = Nivel::query()->where('codigo', '1M')->firstOrFail();

        $curso2025_4A = Curso::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo2025->id,
            'nivel_id' => $nivel4B->id,
            'paralelo' => 'A',
            'cupo_maximo' => 38,
            'created_by' => $admin->id,
        ]);

        $profJefe = $this->crearProfesor($admin, $establecimiento->id, [
            'tipo_documento' => TipoDocumento::Rut,
            'numero_documento' => '123456785',
            'pais_documento' => 'CL',
            'nombres' => 'María',
            'apellido_paterno' => 'Soto',
            'apellido_materno' => 'Rivas',
            'email' => 'maria.soto@demo.cl',
        ], 'PROF-001');

        $profJuan = $this->crearProfesor($admin, $establecimiento->id, [
            'tipo_documento' => TipoDocumento::Rut,
            'numero_documento' => '156789462',
            'pais_documento' => 'CL',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Lara',
            'email' => 'juan.perez@demo.cl',
        ], 'PROF-002');

        $profPedro = $this->crearProfesor($admin, $establecimiento->id, [
            'tipo_documento' => TipoDocumento::Rut,
            'numero_documento' => '102345678',
            'pais_documento' => 'CL',
            'nombres' => 'Pedro',
            'apellido_paterno' => 'Díaz',
            'apellido_materno' => 'Muñoz',
            'email' => 'pedro.diaz@demo.cl',
        ], 'PROF-003');

        $profAna = $this->crearProfesor($admin, $establecimiento->id, [
            'tipo_documento' => TipoDocumento::Rut,
            'numero_documento' => '134567892',
            'pais_documento' => 'CL',
            'nombres' => 'Ana',
            'apellido_paterno' => 'López',
            'apellido_materno' => 'Castro',
            'email' => 'ana.lopez@demo.cl',
        ], 'PROF-004');

        $profCarlos = $this->crearProfesor($admin, $establecimiento->id, [
            'tipo_documento' => TipoDocumento::Rut,
            'numero_documento' => '167890123',
            'pais_documento' => 'CL',
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Rojas',
            'apellido_materno' => 'Núñez',
            'email' => 'carlos.rojas@demo.cl',
        ], 'PROF-005');

        $curso2026_5A = Curso::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo2026->id,
            'nivel_id' => $nivel5B->id,
            'paralelo' => 'A',
            'jornada' => JornadaCurso::Manana,
            'estado' => EstadoCurso::Activo,
            'cupo_maximo' => 40,
            'profesor_jefe_id' => $profJefe->id,
            'created_by' => $admin->id,
        ]);

        $curso2026_5B = Curso::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo2026->id,
            'nivel_id' => $nivel5B->id,
            'paralelo' => 'B',
            'jornada' => JornadaCurso::Tarde,
            'estado' => EstadoCurso::Activo,
            'cupo_maximo' => 40,
            'profesor_jefe_id' => $profJefe->id,
            'created_by' => $admin->id,
        ]);

        $curso2026_1MA = Curso::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo2026->id,
            'nivel_id' => $nivel1M->id,
            'paralelo' => 'A',
            'jornada' => JornadaCurso::Completa,
            'estado' => EstadoCurso::Activo,
            'cupo_maximo' => 42,
            'created_by' => $admin->id,
        ]);

        $this->sembrarAsignaturasCurso($admin, $curso2026_5A, [
            ['codigo' => 'MAT', 'profesor' => $profJuan],
            ['codigo' => 'LEN', 'profesor' => $profJefe],
            ['codigo' => 'HIS', 'profesor' => $profPedro],
            ['codigo' => 'CIE', 'profesor' => $profAna],
            ['codigo' => 'ING', 'profesor' => null],
        ]);

        $this->sembrarAsignaturasCurso($admin, $curso2026_5B, [
            ['codigo' => 'MAT', 'profesor' => $profCarlos],
            ['codigo' => 'LEN', 'profesor' => $profJefe],
        ]);

        $alumnoJuan = $this->crearAlumno($admin, $establecimiento->id, [
            'tipo_documento' => TipoDocumento::Rut,
            'numero_documento' => '187654327',
            'pais_documento' => 'CL',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Gómez',
            'email' => 'juan.perez@demo.cl',
        ], 'ALU-001');

        $alumnoMaria = $this->crearAlumno($admin, $establecimiento->id, [
            'tipo_documento' => TipoDocumento::Pasaporte,
            'numero_documento' => 'AB123456',
            'pais_documento' => 'VE',
            'nombres' => 'María',
            'apellido_paterno' => 'González',
            'apellido_materno' => 'Díaz',
            'email' => 'maria.gonzalez@demo.cl',
        ], 'ALU-002');

        $alumnoDiego = $this->crearAlumno($admin, $establecimiento->id, [
            'tipo_documento' => TipoDocumento::Dni,
            'numero_documento' => '87654321',
            'pais_documento' => 'PE',
            'nombres' => 'Diego',
            'apellido_paterno' => 'Quispe',
            'apellido_materno' => 'Vargas',
        ], 'ALU-003');

        // Historial: Juan estuvo en 4°B 2025, ahora en 5°A 2026
        Matricula::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo2025->id,
            'curso_id' => $curso2025_4A->id,
            'alumno_id' => $alumnoJuan->id,
            'numero_matricula' => 'MAT-2025-001',
            'fecha_matricula' => '2025-03-01',
            'estado' => EstadoMatricula::Egresado,
            'created_by' => $admin->id,
        ]);

        Matricula::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo2026->id,
            'curso_id' => $curso2026_5A->id,
            'alumno_id' => $alumnoJuan->id,
            'numero_matricula' => 'MAT-2026-001',
            'fecha_matricula' => '2026-03-01',
            'estado' => EstadoMatricula::Activa,
            'created_by' => $admin->id,
        ]);

        Matricula::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo2026->id,
            'curso_id' => $curso2026_5B->id,
            'alumno_id' => $alumnoMaria->id,
            'numero_matricula' => 'MAT-2026-002',
            'fecha_matricula' => '2026-03-05',
            'estado' => EstadoMatricula::Activa,
            'created_by' => $admin->id,
        ]);

        Matricula::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'periodo_escolar_id' => $periodo2026->id,
            'curso_id' => $curso2026_1MA->id,
            'alumno_id' => $alumnoDiego->id,
            'numero_matricula' => 'MAT-2026-003',
            'fecha_matricula' => '2026-03-05',
            'estado' => EstadoMatricula::Activa,
            'created_by' => $admin->id,
        ]);

        $alumnos5A = [
            ['doc' => '201234561', 'nombres' => 'Sofía', 'ap_pat' => 'Morales', 'ap_mat' => 'Peña', 'codigo' => 'ALU-004'],
            ['doc' => '212345672', 'nombres' => 'Lucas', 'ap_pat' => 'Fernández', 'ap_mat' => 'Silva', 'codigo' => 'ALU-005'],
            ['doc' => '223456783', 'nombres' => 'Valentina', 'ap_pat' => 'Castro', 'ap_mat' => 'Méndez', 'codigo' => 'ALU-006'],
            ['doc' => '234567894', 'nombres' => 'Mateo', 'ap_pat' => 'Herrera', 'ap_mat' => 'Ortiz', 'codigo' => 'ALU-007'],
            ['doc' => '245678905', 'nombres' => 'Isidora', 'ap_pat' => 'Vargas', 'ap_mat' => 'Pino', 'codigo' => 'ALU-008'],
            ['doc' => '256789016', 'nombres' => 'Benjamín', 'ap_pat' => 'Torres', 'ap_mat' => 'Aguilar', 'codigo' => 'ALU-009'],
            ['doc' => '267890127', 'nombres' => 'Emilia', 'ap_pat' => 'Ríos', 'ap_mat' => 'Contreras', 'codigo' => 'ALU-010'],
            ['doc' => '278901238', 'nombres' => 'Tomás', 'ap_pat' => 'Araya', 'ap_mat' => 'Salazar', 'codigo' => 'ALU-011'],
        ];

        foreach ($alumnos5A as $i => $data) {
            $alumno = $this->crearAlumno($admin, $establecimiento->id, [
                'tipo_documento' => TipoDocumento::Rut,
                'numero_documento' => $data['doc'],
                'pais_documento' => 'CL',
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['ap_pat'],
                'apellido_materno' => $data['ap_mat'],
            ], $data['codigo']);

            Matricula::query()->create([
                'establecimiento_id' => $establecimiento->id,
                'periodo_escolar_id' => $periodo2026->id,
                'curso_id' => $curso2026_5A->id,
                'alumno_id' => $alumno->id,
                'numero_matricula' => sprintf('MAT-2026-%03d', 10 + $i),
                'fecha_matricula' => '2026-03-01',
                'estado' => EstadoMatricula::Activa,
                'created_by' => $admin->id,
            ]);
        }

        $alumnos5B = [
            ['doc' => '289012349', 'nombres' => 'Camila', 'ap_pat' => 'Paredes', 'ap_mat' => 'Jara', 'codigo' => 'ALU-012'],
            ['doc' => '290123450', 'nombres' => 'Sebastián', 'ap_pat' => 'Muñoz', 'ap_mat' => 'Leiva', 'codigo' => 'ALU-013'],
            ['doc' => '301234561', 'nombres' => 'Florencia', 'ap_pat' => 'Navarro', 'ap_mat' => 'Cáceres', 'codigo' => 'ALU-014'],
            ['doc' => '312345672', 'nombres' => 'Nicolás', 'ap_pat' => 'Bravo', 'ap_mat' => 'Pizarro', 'codigo' => 'ALU-015'],
            ['doc' => '323456783', 'nombres' => 'Antonia', 'ap_pat' => 'Sepúlveda', 'ap_mat' => 'Molina', 'codigo' => 'ALU-016'],
        ];

        foreach ($alumnos5B as $i => $data) {
            $alumno = $this->crearAlumno($admin, $establecimiento->id, [
                'tipo_documento' => TipoDocumento::Rut,
                'numero_documento' => $data['doc'],
                'pais_documento' => 'CL',
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['ap_pat'],
                'apellido_materno' => $data['ap_mat'],
            ], $data['codigo']);

            Matricula::query()->create([
                'establecimiento_id' => $establecimiento->id,
                'periodo_escolar_id' => $periodo2026->id,
                'curso_id' => $curso2026_5B->id,
                'alumno_id' => $alumno->id,
                'numero_matricula' => sprintf('MAT-2026-%03d', 20 + $i),
                'fecha_matricula' => '2026-03-05',
                'estado' => EstadoMatricula::Activa,
                'created_by' => $admin->id,
            ]);
        }

        $alumnos1M = [
            ['doc' => '334567894', 'nombres' => 'Renata', 'ap_pat' => 'Fuentes', 'ap_mat' => 'Gallardo', 'codigo' => 'ALU-017'],
            ['doc' => '345678905', 'nombres' => 'Martín', 'ap_pat' => 'Espinoza', 'ap_mat' => 'Cordero', 'codigo' => 'ALU-018'],
        ];

        foreach ($alumnos1M as $i => $data) {
            $alumno = $this->crearAlumno($admin, $establecimiento->id, [
                'tipo_documento' => TipoDocumento::Rut,
                'numero_documento' => $data['doc'],
                'pais_documento' => 'CL',
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['ap_pat'],
                'apellido_materno' => $data['ap_mat'],
            ], $data['codigo']);

            Matricula::query()->create([
                'establecimiento_id' => $establecimiento->id,
                'periodo_escolar_id' => $periodo2026->id,
                'curso_id' => $curso2026_1MA->id,
                'alumno_id' => $alumno->id,
                'numero_matricula' => sprintf('MAT-2026-%03d', 30 + $i),
                'fecha_matricula' => '2026-03-05',
                'estado' => EstadoMatricula::Activa,
                'created_by' => $admin->id,
            ]);
        }

        // Persona sin rol aún (para probar conversión a alumno/profesor)
        Persona::query()->create([
            'establecimiento_id' => $establecimiento->id,
            'tipo_documento' => TipoDocumento::Rut,
            'numero_documento' => '998877665',
            'pais_documento' => 'CL',
            'nombres' => 'Ana',
            'apellido_paterno' => 'Silva',
            'apellido_materno' => 'Torres',
            'email' => 'ana.silva@demo.cl',
            'activo' => true,
            'created_by' => $admin->id,
        ]);
    }

    /**
     * @param  array<int, array{codigo: string, profesor: ?Profesor}>  $items
     */
    private function sembrarAsignaturasCurso(User $admin, Curso $curso, array $items): void
    {
        foreach ($items as $item) {
            $asignatura = Asignatura::query()->where('codigo', $item['codigo'])->firstOrFail();
            $ca = CursoAsignatura::query()->create([
                'curso_id' => $curso->id,
                'asignatura_id' => $asignatura->id,
                'estado' => 'activa',
                'created_by' => $admin->id,
            ]);

            if ($item['profesor']) {
                AsignacionDocente::query()->create([
                    'curso_asignatura_id' => $ca->id,
                    'profesor_id' => $item['profesor']->id,
                    'rol' => RolAsignacionDocente::Titular,
                    'estado' => EstadoAsignacionDocente::Activa,
                    'fecha_inicio' => '2026-03-01',
                    'created_by' => $admin->id,
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $personaData
     */
    private function crearPersona(User $admin, int $establecimientoId, array $personaData): Persona
    {
        return Persona::query()->create(array_merge($personaData, [
            'establecimiento_id' => $establecimientoId,
            'activo' => true,
            'created_by' => $admin->id,
        ]));
    }

    /**
     * @param  array<string, mixed>  $personaData
     */
    private function crearAlumno(User $admin, int $establecimientoId, array $personaData, string $codigo): Alumno
    {
        $persona = $this->crearPersona($admin, $establecimientoId, $personaData);

        return Alumno::query()->create([
            'establecimiento_id' => $establecimientoId,
            'persona_id' => $persona->id,
            'codigo_interno' => $codigo,
            'activo' => true,
            'created_by' => $admin->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $personaData
     */
    private function crearProfesor(User $admin, int $establecimientoId, array $personaData, string $codigo): Profesor
    {
        $persona = $this->crearPersona($admin, $establecimientoId, $personaData);

        return Profesor::query()->create([
            'establecimiento_id' => $establecimientoId,
            'persona_id' => $persona->id,
            'codigo_interno' => $codigo,
            'activo' => true,
            'created_by' => $admin->id,
        ]);
    }
}
