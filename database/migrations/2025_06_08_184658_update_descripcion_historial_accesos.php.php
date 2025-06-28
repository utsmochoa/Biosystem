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
        Schema::table('historial_accesos', function (Blueprint $table) {
            $table->string('descripcion')->nullable()->after('tipo_accion');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_accesos', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
};
