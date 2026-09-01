<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_calendario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('periodo_escolar_id')->nullable()->constrained('periodos_escolares')->nullOnDelete();
            $table->boolean('sabado_lectivo')->default(false);
            $table->boolean('domingo_lectivo')->default(false);
            $table->boolean('permitir_asistencia_en_feriado')->default(false);
            $table->boolean('permitir_asistencia_en_vacaciones')->default(false);
            $table->boolean('permitir_asistencia_en_suspension')->default(false);
            $table->boolean('permitir_asistencia_fin_semana')->default(false);
            $table->boolean('dia_sin_registro_es_ausente')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['establecimiento_id', 'periodo_escolar_id'], 'config_calendario_est_periodo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_calendario');
    }
};
