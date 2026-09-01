<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feriados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('periodo_escolar_id')->nullable()->constrained('periodos_escolares')->nullOnDelete();
            $table->string('nombre');
            $table->string('tipo', 30)->default('nacional');
            $table->boolean('recurrente')->default(false);
            $table->date('fecha')->nullable();
            $table->unsignedTinyInteger('mes')->nullable();
            $table->unsignedTinyInteger('dia')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('activo');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['establecimiento_id', 'periodo_escolar_id', 'fecha']);
            $table->index(['establecimiento_id', 'mes', 'dia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriados');
    }
};
