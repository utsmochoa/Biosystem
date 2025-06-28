<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateFotoColumnInEstudiantesTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE estudiantes MODIFY foto MEDIUMBLOB NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE estudiantes MODIFY foto BLOB NULL");
    }
}

