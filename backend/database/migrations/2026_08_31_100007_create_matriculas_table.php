<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('periodo_escolar_id')->constrained('periodos_escolares')->restrictOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->restrictOnDelete();
            $table->string('numero_matricula', 50)->nullable();
            $table->date('fecha_matricula');
            $table->string('estado', 20)->default('activa');
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['alumno_id', 'periodo_escolar_id'], 'matriculas_alumno_periodo_unique');
            $table->index(['periodo_escolar_id', 'curso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
