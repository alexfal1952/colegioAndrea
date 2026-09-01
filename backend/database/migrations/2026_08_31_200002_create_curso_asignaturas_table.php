<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_asignaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete();
            $table->foreignId('asignatura_id')->constrained('asignaturas')->restrictOnDelete();
            $table->string('estado', 20)->default('activa');
            $table->unsignedSmallInteger('horas_semanales')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['curso_id', 'asignatura_id'], 'curso_asignaturas_unique');
            $table->index(['curso_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_asignaturas');
    }
};
