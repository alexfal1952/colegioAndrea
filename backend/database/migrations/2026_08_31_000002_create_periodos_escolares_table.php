<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_escolares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('periodos_escolares')->restrictOnDelete();
            $table->string('nombre');
            $table->unsignedSmallInteger('anio');
            $table->string('tipo', 20);
            $table->string('subtipo', 20)->nullable();
            $table->unsignedTinyInteger('numero_subperiodo')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_termino');
            $table->string('estado', 20)->default('borrador');
            $table->text('descripcion')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['establecimiento_id', 'anio', 'estado']);
            $table->unique(['parent_id', 'numero_subperiodo'], 'periodos_escolares_parent_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_escolares');
    }
};
