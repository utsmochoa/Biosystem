<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historial_accesos', function (Blueprint $table) {
            DB::statement("ALTER TABLE historial_accesos 
            MODIFY COLUMN tipo_accion 
            ENUM('registro', 'actualizacion', 'verificacion', 'habilitacion', 'deshabilitacion') 
            NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_accesos', function (Blueprint $table) {
            DB::statement("ALTER TABLE historial_accesos 
            MODIFY COLUMN tipo_accion 
            ENUM('registro', 'actualizacion', 'verificacion') 
            NOT NULL");
        });
    }
};
