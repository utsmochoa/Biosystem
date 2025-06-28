<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHuellas extends Model
{
    use HasFactory;

    protected $table = 'huellas_users';
    
    protected $fillable = [
        'users_id',
        'huella_data',
        'quality',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}