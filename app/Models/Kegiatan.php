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
        'materi_kegiatan',
        'catatan_pembimbing',
    ];

    public $timestamps = false; // Sesuaikan jika tabel tidak punya timestamps (created_at, updated_at)

    public function eskul()
    {
        return $this->belongsTo(Eskul::class, 'id_eskul', 'id_eskul');
    }
}