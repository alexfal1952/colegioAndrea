<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\CalendarioEscolarController;
use App\Http\Controllers\EstadoAsistenciaController;
use App\Http\Controllers\FeriadoController;
use App\Http\Controllers\SuspensionClasesController;
use App\Http\Controllers\VacacionController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\CursoDetalleController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PeriodoEscolarController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\ProfesorController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/catalogos/paises', [CatalogoController::class, 'paises']);
    Route::get('/catalogos/niveles', [CatalogoController::class, 'niveles']);

    Route::apiResource('periodos-escolares', PeriodoEscolarController::class)
        ->parameters(['periodos-escolares' => 'periodoEscolar']);
    Route::patch('periodos-escolares/{periodoEscolar}/activar', [PeriodoEscolarController::class, 'activar']);
    Route::patch('periodos-escolares/{periodoEscolar}/cerrar', [PeriodoEscolarController::class, 'cerrar']);
    Route::get('periodos-escolares/{periodoEscolar}/cursos', [MatriculaController::class, 'cursosPorPeriodo']);

    Route::apiResource('personas', PersonaController::class);

    Route::get('alumnos/{alumno}/historial', [AlumnoController::class, 'historial']);
    Route::apiResource('alumnos', AlumnoController::class);

    Route::apiResource('profesores', ProfesorController::class);
    Route::apiResource('asignaturas', AsignaturaController::class)->except(['show', 'destroy']);
    Route::get('asignaturas-activas', [AsignaturaController::class, 'activas']);

    Route::get('cursos/{curso}/detalle', [CursoDetalleController::class, 'show']);
    Route::get('cursos/{curso}/asignaturas', [CursoDetalleController::class, 'asignaturas']);
    Route::post('cursos/{curso}/asignaturas', [CursoDetalleController::class, 'agregarAsignatura']);
    Route::delete('cursos/{curso}/asignaturas/{cursoAsignatura}', [CursoDetalleController::class, 'quitarAsignatura']);
    Route::get('cursos/{curso}/profesores', [CursoDetalleController::class, 'profesores']);
    Route::get('cursos/{curso}/alumnos', [CursoDetalleController::class, 'alumnos']);
    Route::patch('cursos/{curso}/profesor-jefe', [CursoDetalleController::class, 'profesorJefe']);
    Route::post('curso-asignaturas/{cursoAsignatura}/docentes', [CursoDetalleController::class, 'asignarDocente']);
    Route::put('asignaciones-docente/{asignacionDocente}', [CursoDetalleController::class, 'actualizarAsignacion']);

    Route::apiResource('cursos', CursoController::class);
    Route::apiResource('matriculas', MatriculaController::class);

    Route::get('configuracion-calendario', [CalendarioEscolarController::class, 'configuracion']);
    Route::put('configuracion-calendario', [CalendarioEscolarController::class, 'actualizarConfiguracion']);
    Route::get('calendario-escolar', [CalendarioEscolarController::class, 'index']);
    Route::get('calendario-escolar/{fecha}', [CalendarioEscolarController::class, 'show']);

    Route::post('feriados/sincronizar-boostr', [FeriadoController::class, 'sincronizarBoostr']);
    Route::apiResource('feriados', FeriadoController::class)->except(['show', 'destroy']);
    Route::apiResource('vacaciones', VacacionController::class)->except(['show']);
    Route::apiResource('suspensiones-clases', SuspensionClasesController::class)
        ->parameters(['suspensiones-clases' => 'suspensionClase'])
        ->except(['show']);
    Route::apiResource('estados-asistencia', EstadoAsistenciaController::class)
        ->parameters(['estados-asistencia' => 'estadoAsistencia'])
        ->except(['show', 'destroy']);

    Route::get('cursos/{curso}/asistencias/validar-fecha', [AsistenciaController::class, 'validarFecha']);
    Route::get('cursos/{curso}/asistencias', [AsistenciaController::class, 'porCursoFecha']);
    Route::post('cursos/{curso}/asistencias', [AsistenciaController::class, 'guardarLote']);
    Route::get('cursos/{curso}/asistencias/resumen', [AsistenciaController::class, 'resumenCurso']);
    Route::get('matriculas/{matricula}/asistencias/resumen', [AsistenciaController::class, 'resumenMatricula']);
    Route::get('alumnos/{alumno}/asistencias', [AsistenciaController::class, 'historialAlumno']);
    Route::get('asistencias', [AsistenciaController::class, 'index']);
    Route::put('asistencias/{asistencia}', [AsistenciaController::class, 'update']);
});
