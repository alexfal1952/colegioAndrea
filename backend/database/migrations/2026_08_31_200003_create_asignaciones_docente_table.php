<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_asignatura_id')->constrained('curso_asignaturas')->restrictOnDelete();
            $table->foreignId('profesor_id')->constrained('profesores')->restrictOnDelete();
            $table->string('rol', 20)->default('titular');
            $table->string('estado', 20)->default('activa');
            $table->date('fecha_inicio');
            $table->date('fecha_termino')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['curso_asignatura_id', 'estado']);
            $table->index(['profesor_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_docente');
    }
};
