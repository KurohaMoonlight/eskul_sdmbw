<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';

    protected $fillable = [
        'id_kegiatan',
        'id_peserta',
        'status',
    ];

    public $timestamps = false; 

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id_peserta');
    }

    // Tambahkan relasi ke Nilai Harian (One to One)
    public function nilai_harian()
    {
        return $this->hasOne(NilaiHarian::class, 'id_absensi', 'id_absensi');
    }
}