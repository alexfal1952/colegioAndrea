<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->nullable()->constrained('establecimientos')->nullOnDelete();
            $table->string('codigo', 30);
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('cuenta_como_presente')->default(false);
            $table->boolean('cuenta_como_ausencia')->default(false);
            $table->boolean('cuenta_como_atraso')->default(false);
            $table->boolean('cuenta_como_justificado')->default(false);
            $table->string('color', 20)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->boolean('es_sistema')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['establecimiento_id', 'codigo'], 'estados_asistencia_est_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_asistencia');
    }
};
