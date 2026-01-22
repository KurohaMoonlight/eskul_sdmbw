<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';

    // Tambahkan field yang bisa diisi secara massal
    protected $fillable = [
        'id_kegiatan',
        'id_peserta',
        'status',
        // 'waktu_input' biasanya dihandle database (default CURRENT_TIMESTAMP), 
        // tapi jika ingin diisi manual bisa ditambahkan.
    ];

    public $timestamps = false; // Sesuaikan dengan struktur tabel (jika tidak ada created_at/updated_at)

    // Relasi ke Kegiatan
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }

    // Relasi ke Peserta
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id_peserta');
    }
}