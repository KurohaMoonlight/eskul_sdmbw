<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiHarian extends Model
{
    use HasFactory;

    protected $table = 'nilai_harian';
    protected $primaryKey = 'id_nilai_harian';

    protected $fillable = [
        'id_absensi',
        'skor_teknik',
        'skor_disiplin',
        'skor_kerjasama',
        'catatan_harian',
    ];

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }
}