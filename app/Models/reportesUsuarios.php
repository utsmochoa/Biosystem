<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reportesUsuarios extends Model
{
    protected $table = 'historial_usuarios';

    protected $fillable = [
        'users_id',
        'tipo_accion',
        'descripcion',
        'fecha_hora',
    ];
    public $timestamps = false;
    
}
