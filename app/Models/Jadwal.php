<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';
    protected $primaryKey = 'id_jadwal';
    public $timestamps = false; // Matikan timestamp sesuai struktur DB

    protected $fillable = [
        'id_eskul',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'kelas_min',
        'kelas_max',
    ];

    public function eskul()
    {
        return $this->belongsTo(Eskul::class, 'id_eskul', 'id_eskul');
    }
}