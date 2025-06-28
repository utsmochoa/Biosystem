<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('historial_accesos', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->foreign('estudiante_id')
                  ->references('id')->on('estudiantes')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('historial_accesos', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->foreign('estudiante_id')
                  ->references('id')->on('estudiantes');
        });
    }
};
