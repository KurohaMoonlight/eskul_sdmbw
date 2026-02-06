<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';
    protected $primaryKey = 'id_kegiatan';

    protected $fillable = [
        'id_eskul',
        'tanggal',
        'jam_mulai',   // <--- TAMBAHKAN INI
        'jam_selesai', // <--- TAMBAHKAN INI
        'materi_kegiatan',
        'catatan_pembimbing',
    ];

    public $timestamps = false;

    public function eskul()
    {
        return $this->belongsTo(Eskul::class, 'id_eskul', 'id_eskul');
    }
}