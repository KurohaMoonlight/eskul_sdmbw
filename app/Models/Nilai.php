<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilai';
    protected $primaryKey = 'id_nilai';

    protected $fillable = [
        'id_anggota',
        'id_eskul',
        'nilai_disiplin',
        'nilai_teknik',
        'nilai_kerjasama',
        'catatan_rapor',
        'semester',
        'tahun_ajaran',
    ];

    // Relasi ke Anggota Eskul (agar bisa ambil nama peserta)
    public function anggota_eskul()
    {
        return $this->belongsTo(AnggotaEskul::class, 'id_anggota', 'id_anggota');
    }

    // Relasi ke Eskul (opsional, tapi bagus untuk validasi)
    public function eskul()
    {
        return $this->belongsTo(Eskul::class, 'id_eskul', 'id_eskul');
    }
}