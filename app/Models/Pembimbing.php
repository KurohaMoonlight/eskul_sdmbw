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
        'last_login', // 2. Tambahkan last_login ke fillable
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'last_login' => 'datetime', // 3. Cast ke datetime agar bisa diformat tanggalnya
        'password' => 'hashed',
    ];
}