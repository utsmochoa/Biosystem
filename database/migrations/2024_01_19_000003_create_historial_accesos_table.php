<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->enum('tipo_accion', ['registro', 'actualizacion', 'verificacion']);
            $table->timestamp('fecha_hora')->useCurrent();

            $table->index('fecha_hora', 'idx_historial_fecha');
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_accesos');
    }
};