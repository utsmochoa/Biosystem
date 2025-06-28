<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historial_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo_accion', ['registro', 'actualizacion', 'inicio_sesion', 'cierre_sesion']);
            $table->string('descripcion')->nullable();
            $table->timestamp('fecha_hora')->useCurrent();

            $table->index('fecha_hora', 'idx_historial_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_usuarios');
    }
};
