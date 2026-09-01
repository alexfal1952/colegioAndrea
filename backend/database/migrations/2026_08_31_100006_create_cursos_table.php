<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('periodo_escolar_id')->constrained('periodos_escolares')->restrictOnDelete();
            $table->foreignId('nivel_id')->constrained('niveles')->restrictOnDelete();
            $table->string('paralelo', 10);
            $table->string('nombre')->nullable();
            $table->unsignedSmallInteger('cupo_maximo')->nullable();
            $table->foreignId('profesor_jefe_id')->nullable()->constrained('profesores')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['periodo_escolar_id', 'nivel_id', 'paralelo'], 'cursos_periodo_nivel_paralelo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
