<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory;

    protected $table = 'peserta';
    protected $primaryKey = 'id_peserta';

    // Menambahkan kolom yang diizinkan untuk Mass Assignment
    protected $fillable = [
        'nama_lengkap',
        'tingkat_kelas',
        'jenis_kelamin',
    ];
}