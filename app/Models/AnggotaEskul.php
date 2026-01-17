<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaEskul extends Model
{
    use HasFactory;

    // Definisi Nama Tabel (Wajib karena singular)
    protected $table = 'anggota_eskul';
    
    // Primary Key
    protected $primaryKey = 'id_anggota';
    
    // Matikan Timestamps (Karena tabel tidak punya created_at/updated_at)
    public $timestamps = false;

    protected $fillable = [
        'id_eskul',
        'id_peserta',
        'tahun_ajaran',
        'status_aktif',
    ];

    // Relasi ke Eskul
    public function eskul()
    {
        return $this->belongsTo(Eskul::class, 'id_eskul', 'id_eskul');
    }

    // Relasi ke Peserta
    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id_peserta');
    }
}