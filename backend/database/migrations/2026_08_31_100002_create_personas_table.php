<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->restrictOnDelete();
            $table->string('tipo_documento', 20);
            $table->string('numero_documento', 50);
            $table->string('pais_documento', 2)->nullable();
            $table->foreign('pais_documento')->references('codigo')->on('paises')->nullOnDelete();
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tipo_documento', 'numero_documento', 'pais_documento'], 'personas_documento_unique');
            $table->index(['establecimiento_id', 'apellido_paterno', 'nombres']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
