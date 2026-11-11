<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('especie', 80);
            $table->string('raza', 100)->nullable();
            $table->enum('sexo', ['macho', 'hembra', 'desconocido'])->default('desconocido');
            $table->date('fecha_nacimiento')->nullable();
            $table->decimal('peso_kg', 8, 2)->nullable();
            $table->enum('estado', ['activo', 'adoptado', 'en_tratamiento', 'inactivo'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animales');
    }
};
