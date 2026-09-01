<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->nullable()->constrained('establecimientos')->cascadeOnDelete();
            $table->string('codigo', 20);
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('activa');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['establecimiento_id', 'codigo'], 'asignaturas_establecimiento_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaturas');
    }
};
