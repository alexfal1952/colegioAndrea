<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->nullable()->constrained('establecimientos')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('codigo', 20);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['establecimiento_id', 'codigo'], 'niveles_establecimiento_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};
