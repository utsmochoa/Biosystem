<?php
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("ALTER TABLE historial_usuarios MODIFY COLUMN tipo_accion ENUM('registro', 'actualizacion', 'inicio_sesion', 'cierre_sesion', 'habilitacion', 'deshabilitacion')");
    }

    public function down()
    {
        DB::statement("ALTER TABLE historial_usuarios MODIFY COLUMN tipo_accion ENUM('registro', 'actualizacion', 'inicio_sesion', 'cierre_sesion')");
    }
};
