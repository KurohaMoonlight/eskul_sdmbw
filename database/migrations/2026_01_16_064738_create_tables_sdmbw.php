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
                $table->integer('id_pembimbing')->autoIncrement();
                $table->string('nama_lengkap', 100);
                $table->string('username', 50)->unique();
                $table->string('password', 255);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });

            // 2. Tabel Admin
            Schema::create('admin', function (Blueprint $table) {
                $table->integer('id_admin')->autoIncrement();
                $table->string('username', 100)->unique();
                $table->string('password', 255);
            });

            // 3. Tabel Peserta (Dibuat dulu sebelum Anggota Eskul)
            Schema::create('peserta', function (Blueprint $table) {
                $table->integer('id_peserta')->autoIncrement();
                $table->string('nama_lengkap', 100);
                $table->string('tingkat_kelas', 20);
                $table->enum('jenis_kelamin', ['L', 'P']);
            });

            // 4. Tabel Eskul (Butuh tabel Pembimbing)
            Schema::create('eskul', function (Blueprint $table) {
                $table->integer('id_eskul')->autoIncrement();
                $table->string('nama_eskul', 50);
                $table->integer('id_pembimbing')->nullable();
                $table->text('deskripsi')->nullable();
                $table->enum('jenjang_kelas_min', ['1', '2', '3', '4', '5', '6'])->nullable();
                $table->enum('jenjang_kelas_max', ['2', '3', '4', '5', '6'])->nullable();
                
                $table->foreign('id_pembimbing')
                    ->references('id_pembimbing')->on('pembimbing')
                    ->onDelete('set null');
            });

            // 5. Tabel Anggota Eskul (Butuh Eskul & Peserta)
            Schema::create('anggota_eskul', function (Blueprint $table) {
                $table->integer('id_anggota')->autoIncrement();
                $table->integer('id_eskul')->nullable();
                $table->integer('id_peserta')->nullable();
                $table->string('tahun_ajaran', 10);
                $table->boolean('status_aktif')->default(1);

                $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
                $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->onDelete('cascade');
            });

            // 6. Tabel Jadwal (Butuh Eskul)
            Schema::create('jadwal', function (Blueprint $table) {
                $table->integer('id_jadwal')->autoIncrement();
                $table->integer('id_eskul')->nullable();
                $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])->nullable();
                $table->time('jam_mulai')->nullable();
                $table->time('jam_selesai')->nullable();
                $table->string('lokasi', 100)->nullable();
                $table->tinyInteger('kelas_min');
                $table->tinyInteger('kelas_max');

                $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
            });

            // 7. Tabel Kegiatan (Butuh Eskul)
            Schema::create('kegiatan', function (Blueprint $table) {
                $table->integer('id_kegiatan')->autoIncrement();
                $table->integer('id_eskul')->nullable();
                $table->date('tanggal');
                $table->text('materi_kegiatan');
                $table->text('catatan_pembimbing')->nullable();

                $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
            });

            // 8. Tabel Absensi (Butuh Kegiatan & Peserta)
            Schema::create('absensi', function (Blueprint $table) {
                $table->integer('id_absensi')->autoIncrement();
                $table->integer('id_kegiatan')->nullable();
                $table->integer('id_peserta')->nullable();
                $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpha']);
                $table->timestamp('waktu_input')->useCurrent();

                $table->foreign('id_kegiatan')->references('id_kegiatan')->on('kegiatan')->onDelete('cascade');
                $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->onDelete('cascade');
            });

            // 9. Tabel Prestasi (Butuh Eskul & Peserta)
            Schema::create('prestasi', function (Blueprint $table) {
                $table->integer('id_prestasi')->autoIncrement();
                $table->integer('id_eskul')->nullable();
                $table->integer('id_peserta')->nullable();
                $table->string('nama_lomba', 150)->nullable();
                $table->enum('tingkat', ['Kecamatan', 'Kabupaten', 'Provinsi', 'Nasional'])->nullable();
                $table->string('juara_ke', 50)->nullable();
                $table->date('tanggal_lomba')->nullable();
                $table->string('foto_prestasi', 255)->nullable();

                $table->foreign('id_eskul')->references('id_eskul')->on('eskul')->onDelete('cascade');
                $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->onDelete('cascade');
            });
            // Tabel Sessions (Wajib ada jika menggunakan driver database)
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
        Schema::dropIfExists('tables_sdmbw');
    }
};
