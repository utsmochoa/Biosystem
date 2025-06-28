<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('cedula_identidad', 20)->unique();
            $table->string('carrera', 100);
            $table->string('semestre');
            $table->timestamps();

        });

        // Crear índices
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->index('cedula_identidad', 'idx_cedula');
            $table->index(['nombres', 'apellidos'], 'idx_nombres_apellidos');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estudiantes');
    }
};