<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suspensiones_clases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('periodo_escolar_id')->constrained('periodos_escolares')->restrictOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->date('fecha');
            $table->string('nombre');
            $table->text('motivo')->nullable();
            $table->string('tipo', 30)->default('total');
            $table->string('estado', 20)->default('activa');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['periodo_escolar_id', 'fecha']);
            $table->index(['curso_id', 'fecha']);
            $table->unique(['periodo_escolar_id', 'curso_id', 'fecha'], 'suspensiones_periodo_curso_fecha_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suspensiones_clases');
    }
};
