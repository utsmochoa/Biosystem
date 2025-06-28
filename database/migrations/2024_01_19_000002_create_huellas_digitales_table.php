<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('huellas_digitales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->binary('huella_data');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->boolean('activo')->default(true);
            $table->binary('quality');
        });
    }

    public function down()
    {
        Schema::dropIfExists('huellas_digitales');
    }
};
