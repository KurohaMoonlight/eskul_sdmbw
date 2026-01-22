<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestasi extends Model
{
    use HasFactory;

    protected $table = 'prestasi';
    protected $primaryKey = 'id_prestasi';

    // Disable timestamps karena di tabel SQL tidak ada created_at/updated_at
    public $timestamps = false; 

    protected $fillable = [
        'id_eskul',
        'id_peserta', // Nullable (jika tim)
        'nama_lomba',
        'tingkat', // Kecamatan, Kabupaten, Provinsi, Nasional
        'juara_ke',
        'tanggal_lomba',
        'foto_prestasi',
    ];

    // Relasi ke Eskul
    public function eskul()
    {
        return $this->belongsTo(Eskul::class, 'id_eskul', 'id_eskul');
    }

    // Relasi ke Peserta (Siswa)
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id_peserta');
    }
}