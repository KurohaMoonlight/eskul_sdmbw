<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Pembimbing
        Schema::create('pembimbing', function (Blueprint $table) {
            $table->id('id_pembimbing'); // Menggunakan id() lebih standar di Laravel modern
            $table->string('nama_lengkap', 100);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->timestamps(); // created_at & updated_at otomatis
        });

        // 2. Tabel Admin
        Schema::create('admin', function (Blueprint $table) {
            $table->id('id_admin');
            $table->string('username', 100)->unique();
            $table->string('password', 255);
            // Admin biasanya jarang butuh timestamps, tapi boleh ditambahkan jika perlu
            $table->timestamps();
        });

        // 3. Tabel Peserta (Master Data Siswa)
        Schema::create('peserta', function (Blueprint $table) {
            $table->id('id_peserta');
            $table->string('nama_lengkap', 100);
            $table->string('tingkat_kelas', 20); // Misal: "1", "2", "3"
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->timestamps();
        });

        // 4. Tabel Eskul
        Schema::create('eskul', function (Blueprint $table) {
            $table->id('id_eskul');
            $table->string('nama_eskul', 50);
            $table->unsignedBigInteger('id_pembimbing')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('jenjang_kelas_min', ['1', '2', '3', '4', '5', '6'])->nullable();
            $table->enum('jenjang_kelas_max', ['1', '2', '3', '4', '5', '6'])->nullable();
            $table->timestamps();

            $table->foreign('id_pembimbing')
                ->references('id_pembimbing')->on('pembimbing')
                ->onDelete('set null');
        });

        // 5. Tabel Anggota Eskul (Pivot Peserta <-> Eskul)
        Schema::create('anggota_eskul', function (Blueprint $table) {
            $table->id('id_anggota');
            $table->unsignedBigInteger('id_eskul');
            $table->unsignedBigInteger('id_peserta');
            $table->string('tahun_ajaran', 10); // Misal: "2025/2026"
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->onDelete('cascade');
        });

        // 6. Tabel Jadwal
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->unsignedBigInteger('id_eskul');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi', 100)->nullable();
            $table->tinyInteger('kelas_min'); // 1-6
            $table->tinyInteger('kelas_max'); // 1-6
            $table->timestamps();

            $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
        });

        // 7. Tabel Kegiatan (Jurnal Harian)
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id('id_kegiatan');
            $table->unsignedBigInteger('id_eskul');
            $table->date('tanggal');
            $table->text('materi_kegiatan');
            $table->text('catatan_pembimbing')->nullable();
            $table->timestamps();

            $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
        });

        // 8. Tabel Absensi
        Schema::create('absensi', function (Blueprint $table) {
            $table->id('id_absensi');
            $table->unsignedBigInteger('id_kegiatan');
            $table->unsignedBigInteger('id_peserta');
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpha']);
            $table->timestamp('waktu_input')->useCurrent();
            // Timestamps opsional, tapi waktu_input sudah cukup

            $table->foreign('id_kegiatan')->references('id_kegiatan')->on('kegiatan')->onDelete('cascade');
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->onDelete('cascade');
        });

        // 9. Tabel Prestasi
        Schema::create('prestasi', function (Blueprint $table) {
            $table->id('id_prestasi');
            $table->unsignedBigInteger('id_eskul');
            $table->unsignedBigInteger('id_peserta')->nullable(); // Nullable jika Tim
            $table->string('nama_lomba', 150);
            $table->enum('tingkat', ['Kecamatan', 'Kabupaten', 'Provinsi', 'Nasional']);
            $table->string('juara_ke', 50);
            $table->date('tanggal_lomba');
            $table->string('foto_prestasi', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->onDelete('cascade');
        });

        // 10. Tabel Nilai (BARU: Fitur Penilaian Anggota)
        Schema::create('nilai', function (Blueprint $table) {
            $table->id('id_nilai');
            $table->unsignedBigInteger('id_anggota'); // Relasi ke tabel anggota_eskul
            $table->unsignedBigInteger('id_eskul');   // Redundan tapi mempercepat query per eskul
            
            // Kriteria Penilaian
            $table->integer('nilai_disiplin')->default(0); // Skala 0-100
            $table->integer('nilai_teknik')->default(0);   // Skala 0-100
            $table->integer('nilai_kerjasama')->default(0);// Skala 0-100
            
            $table->text('catatan_rapor')->nullable(); // Narasi untuk rapor
            $table->string('semester', 10); // Ganjil/Genap
            $table->string('tahun_ajaran', 10); // 2025/2026
            
            $table->timestamps();

            $table->foreign('id_anggota')->references('id_anggota')->on('anggota_eskul')->onDelete('cascade');
            $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
        });

        // Tabel Sessions (Bawaan Laravel)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop urut dari child ke parent untuk menghindari error constraint
        Schema::dropIfExists('nilai');
        Schema::dropIfExists('prestasi');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('kegiatan');
        Schema::dropIfExists('jadwal');
        Schema::dropIfExists('anggota_eskul');
        Schema::dropIfExists('eskul');
        Schema::dropIfExists('peserta');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('pembimbing');
        Schema::dropIfExists('sessions');
    }
};