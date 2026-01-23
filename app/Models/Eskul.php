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
        'id_pembimbing',
        'deskripsi',
        'jenjang_kelas_min',
        'jenjang_kelas_max',
    ];

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'id_pembimbing', 'id_pembimbing');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_eskul', 'id_eskul');
    }

    // Relasi ke AnggotaEskul
    public function anggota_eskul()
    {
        return $this->hasMany(AnggotaEskul::class, 'id_eskul', 'id_eskul');
    }
}