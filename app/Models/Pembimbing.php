<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pembimbing extends Authenticatable
{
    use Notifiable;

    protected $table = 'pembimbing';
    protected $primaryKey = 'id_pembimbing';

    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}