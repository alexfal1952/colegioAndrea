<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificaciones_asistencia', function (Blueprint $table) {
            $table->id();
            $table->string('motivo');
            $table->text('descripcion')->nullable();
            $table->string('documento_path', 500)->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('periodo_escolar_id')->constrained('periodos_escolares')->restrictOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->restrictOnDelete();
            $table->date('fecha');
            $table->foreignId('estado_asistencia_id')->constrained('estados_asistencia')->restrictOnDelete();
            $table->string('turno', 20)->default('completa');
            $table->time('hora_ingreso')->nullable();
            $table->time('hora_salida')->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('justificacion_id')->nullable()->constrained('justificaciones_asistencia')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['matricula_id', 'fecha', 'turno'], 'asistencias_matricula_fecha_turno_unique');
            $table->index(['curso_id', 'fecha']);
            $table->index(['periodo_escolar_id', 'fecha']);
            $table->index(['matricula_id', 'fecha']);
        });

        Schema::create('asistencias_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')->constrained('asistencias')->restrictOnDelete();
            $table->foreignId('estado_anterior_id')->nullable()->constrained('estados_asistencia')->nullOnDelete();
            $table->foreignId('estado_nuevo_id')->nullable()->constrained('estados_asistencia')->nullOnDelete();
            $table->time('hora_ingreso_anterior')->nullable();
            $table->time('hora_ingreso_nuevo')->nullable();
            $table->text('observacion_anterior')->nullable();
            $table->text('observacion_nuevo')->nullable();
            $table->string('accion', 20);
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_auditoria');
        Schema::dropIfExists('asistencias');
        Schema::dropIfExists('justificaciones_asistencia');
    }
};
