<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eskul extends Model
{
    use HasFactory;

    protected $table = 'eskul';
    protected $primaryKey = 'id_eskul';

    protected $fillable = [
        'nama_eskul',
        // 'id_pembimbing', // HAPUS INI karena kolomnya sudah hilang
        'deskripsi',
        'jenjang_kelas_min',
        'jenjang_kelas_max',
    ];

    /**
     * Relasi Many-to-Many ke Pembimbing
     * Menggunakan tabel pivot 'eskul_pembimbing'
     */
    public function pembimbings()
    {
        return $this->belongsToMany(Pembimbing::class, 'eskul_pembimbing', 'id_eskul', 'id_pembimbing');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_eskul');
    }

    public function anggota_eskul()
    {
        return $this->hasMany(AnggotaEskul::class, 'id_eskul');
    }
}