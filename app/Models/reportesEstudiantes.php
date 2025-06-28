<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reportesEstudiantes extends Model
{
    protected $table = 'historial_accesos';

    protected $fillable = [
        'estudiante_id',
        'tipo_accion',
        'descripcion',
        'fecha_hora',
    ];

    public $timestamps = false;
}
