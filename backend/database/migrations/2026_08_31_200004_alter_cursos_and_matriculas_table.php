<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->string('jornada', 20)->nullable()->after('nombre');
            $table->string('estado', 20)->default('activo')->after('jornada');
        });

        DB::table('cursos')->where('activo', true)->update(['estado' => 'activo']);
        DB::table('cursos')->where('activo', false)->update(['estado' => 'inactivo']);

        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn('activo');
        });

        Schema::table('matriculas', function (Blueprint $table) {
            $table->date('fecha_retiro')->nullable()->after('fecha_matricula');
        });
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn('fecha_retiro');
        });

        Schema::table('cursos', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('profesor_jefe_id');
        });

        DB::table('cursos')->where('estado', 'activo')->update(['activo' => true]);
        DB::table('cursos')->where('estado', '!=', 'activo')->update(['activo' => false]);

        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['jornada', 'estado']);
        });
    }
};
