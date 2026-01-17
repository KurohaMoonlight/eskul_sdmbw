-- 1. Tabel Master Pembimbing (AKUN LOGIN)
-- Ini pengganti tabel Users. Password wajib di-hash (bcrypt) lewat Laravel.
CREATE TABLE pembimbing (
    id_pembimbing INT PRIMARY KEY AUTO_INCREMENT,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,  -- Untuk Login
    password VARCHAR(255) NOT NULL,        -- Password ter-enkripsi
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE admin (
  id_admin INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL
);
-- 2. Tabel Master Eskul (Diupdate: Terhubung ke Pembimbing)
CREATE TABLE eskul (
    id_eskul INT PRIMARY KEY AUTO_INCREMENT,
    nama_eskul VARCHAR(50) NOT NULL,
    id_pembimbing INT,                     -- Relasi ke tabel pembimbing
    deskripsi TEXT,
    jenjang_kelas_min ENUM('1', '2', '3', '4', '5', '6'),
    jenjang_kelas_max ENUM('2','3','4','5','6'),-- Opsional: Penjelasan singkat eskul
    FOREIGN KEY (id_pembimbing) REFERENCES pembimbing(id_pembimbing) ON DELETE SET NULL
);

-- 3. Tabel Master Peserta (Siswa)
CREATE TABLE peserta (
    id_peserta INT PRIMARY KEY AUTO_INCREMENT,                
    nama_lengkap VARCHAR(100) NOT NULL,
    tingkat_kelas VARCHAR(20) NOT NULL,            
    jenis_kelamin ENUM('L', 'P') NOT NULL          
);

-- 4. Tabel Anggota Eskul (Mendaftar siswa ke eskul)
CREATE TABLE anggota_eskul (
    id_anggota INT PRIMARY KEY AUTO_INCREMENT,
    id_eskul INT,
    id_peserta INT,
    tahun_ajaran VARCHAR(10) NOT NULL,    
    status_aktif BOOLEAN DEFAULT 1,       
    FOREIGN KEY (id_eskul) REFERENCES eskul(id_eskul) ON DELETE CASCADE,
    FOREIGN KEY (id_peserta) REFERENCES peserta(id_peserta) ON DELETE CASCADE
);

-- 5. Tabel Jadwal (Acuan Hari Latihan)
CREATE TABLE jadwal (
    id_jadwal INT PRIMARY KEY AUTO_INCREMENT,
    id_eskul INT,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
    jam_mulai TIME,
    jam_selesai TIME,
    lokasi VARCHAR(100),
    kelas_min TINYINT NOT NULL, -- Contoh isi: 1 (Untuk kelas 1-3)
    kelas_max TINYINT NOT NULL,
    FOREIGN KEY (id_eskul) REFERENCES eskul(id_eskul) ON DELETE CASCADE
);

-- 6. Tabel Kegiatan / Jurnal (Mencatat aktivitas harian)
CREATE TABLE kegiatan (
    id_kegiatan INT PRIMARY KEY AUTO_INCREMENT,
    id_eskul INT,
    tanggal DATE NOT NULL,
    materi_kegiatan TEXT NOT NULL,        
    catatan_pembimbing TEXT,              
    FOREIGN KEY (id_eskul) REFERENCES eskul(id_eskul) ON DELETE CASCADE
);

-- 7. Tabel Absensi (Mengacu ke kegiatan)
CREATE TABLE absensi (
    id_absensi INT PRIMARY KEY AUTO_INCREMENT,
    id_kegiatan INT,                      
    id_peserta INT,
    status ENUM('Hadir', 'Sakit', 'Izin', 'Alpha') NOT NULL,
    waktu_input TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kegiatan) REFERENCES kegiatan(id_kegiatan) ON DELETE CASCADE,
    FOREIGN KEY (id_peserta) REFERENCES peserta(id_peserta) ON DELETE CASCADE
);

-- 8. Tabel Prestasi
CREATE TABLE prestasi (
    id_prestasi INT PRIMARY KEY AUTO_INCREMENT,
    id_eskul INT,
    id_peserta INT,
    nama_lomba VARCHAR(150),
    tingkat ENUM('Kecamatan', 'Kabupaten', 'Provinsi', 'Nasional'), 
    juara_ke VARCHAR(50),
    tanggal_lomba DATE,
    foto_prestasi VARCHAR(255),
    FOREIGN KEY (id_eskul) REFERENCES eskul(id_eskul) ON DELETE CASCADE,
    FOREIGN KEY (id_peserta) REFERENCES peserta(id_peserta) ON DELETE CASCADE
);