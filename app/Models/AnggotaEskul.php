<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaEskul extends Model
{
    use HasFactory;

    protected $table = 'anggota_eskul';
    protected $primaryKey = 'id_anggota';

    protected $fillable = [
        'id_eskul',
        'id_peserta',
        'tahun_ajaran',
        'status_aktif',
    ];

    // Relasi ke Peserta (Siswa)
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id_peserta');
    }

    public function eskul()
    {
        return $this->belongsTo(Eskul::class, 'id_eskul', 'id_eskul');
    }
}