-- Database untuk Website Puskesmas Kepulauan Seribu Selatan

-- Hapus database jika sudah ada
DROP DATABASE IF EXISTS puskesmas_db;

-- Buat database baru
CREATE DATABASE puskesmas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Gunakan database
USE puskesmas_db;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'operator') NOT NULL DEFAULT 'operator',
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    foto VARCHAR(255) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel log_aktivitas
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tipe VARCHAR(50) NOT NULL,
    deskripsi TEXT NOT NULL,
    ip_address VARCHAR(50) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel kategori_berita
CREATE TABLE kategori_berita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel berita
CREATE TABLE berita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    isi TEXT NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    kategori_id INT NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    tanggal_publikasi DATETIME NOT NULL,
    views INT DEFAULT 0,
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_berita(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel layanan
CREATE TABLE layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    deskripsi_singkat VARCHAR(255) NOT NULL,
    deskripsi_lengkap TEXT NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    urutan INT DEFAULT 0,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel jadwal_pelayanan
CREATE TABLE jadwal_pelayanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_buka TIME NOT NULL,
    jam_tutup TIME NOT NULL,
    keterangan TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel pustu (Puskesmas Pembantu)
CREATE TABLE pustu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    alamat TEXT NOT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    deskripsi TEXT NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel layanan_pustu
CREATE TABLE layanan_pustu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pustu_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pustu_id) REFERENCES pustu(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel jadwal_pustu
CREATE TABLE jadwal_pustu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pustu_id INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_buka TIME NOT NULL,
    jam_tutup TIME NOT NULL,
    keterangan TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pustu_id) REFERENCES pustu(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel slider
CREATE TABLE slider (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(255) DEFAULT NULL,
    gambar VARCHAR(255) NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    urutan INT DEFAULT 0,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel halaman
CREATE TABLE halaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    konten TEXT NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel pengaturan
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_situs VARCHAR(100) NOT NULL,
    deskripsi_situs TEXT DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    favicon VARCHAR(255) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    jam_operasional TEXT DEFAULT NULL,
    facebook VARCHAR(255) DEFAULT NULL,
    twitter VARCHAR(255) DEFAULT NULL,
    instagram VARCHAR(255) DEFAULT NULL,
    youtube VARCHAR(255) DEFAULT NULL,
    footer_text TEXT DEFAULT NULL,
    meta_keywords TEXT DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel laporan
CREATE TABLE laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    file VARCHAR(255) NOT NULL,
    tahun YEAR NOT NULL,
    bulan TINYINT DEFAULT NULL,
    tipe ENUM('bulanan', 'tahunan', 'lainnya') NOT NULL DEFAULT 'bulanan',
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel kunjungan
CREATE TABLE kunjungan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    jumlah_kunjungan INT NOT NULL DEFAULT 0,
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel kunjungan_layanan
CREATE TABLE kunjungan_layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunjungan_id INT NOT NULL,
    layanan_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kunjungan_id) REFERENCES kunjungan(id) ON DELETE CASCADE,
    FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON DELETE CASCADE
);

-- Insert data default

-- Insert admin user
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@puskesmaskepselatan.com', 'admin');

-- Insert kategori berita default
INSERT INTO kategori_berita (nama, slug) VALUES
('Umum', 'umum'),
('Kesehatan', 'kesehatan'),
('Pengumuman', 'pengumuman'),
('Kegiatan', 'kegiatan');

-- Insert pengaturan default
INSERT INTO pengaturan (nama_situs, deskripsi_situs, email, telepon, alamat, footer_text, created_by) VALUES
('Puskesmas Kepulauan Seribu Selatan', 'Website Resmi Puskesmas Kepulauan Seribu Selatan', 'info@puskesmaskepselatan.com', '021-12345678', 'Jl. Pulau Tidung No. 1, Kepulauan Seribu Selatan, DKI Jakarta', '© 2023 Puskesmas Kepulauan Seribu Selatan. All Rights Reserved.', 1);

-- Insert jadwal pelayanan default
INSERT INTO jadwal_pelayanan (hari, jam_buka, jam_tutup, keterangan, created_by) VALUES
('Senin', '08:00:00', '16:00:00', 'Pelayanan normal', 1),
('Selasa', '08:00:00', '16:00:00', 'Pelayanan normal', 1),
('Rabu', '08:00:00', '16:00:00', 'Pelayanan normal', 1),
('Kamis', '08:00:00', '16:00:00', 'Pelayanan normal', 1),
('Jumat', '08:00:00', '16:30:00', 'Pelayanan normal', 1),
('Sabtu', '08:00:00', '12:00:00', 'Pelayanan terbatas', 1),
('Minggu', '00:00:00', '00:00:00', 'Tutup (khusus UGD 24 jam)', 1);

-- Insert pustu default
INSERT INTO pustu (nama, slug, alamat, telepon, email, deskripsi, status, created_by) VALUES
('Pustu Pulau Untung Jawa', 'pustu-pulau-untung-jawa', 'Pulau Untung Jawa, Kepulauan Seribu Selatan', '021-12345679', 'pustu.untungjawa@puskesmaskepselatan.com', 'Puskesmas Pembantu Pulau Untung Jawa melayani masyarakat di wilayah Pulau Untung Jawa dan sekitarnya.', 'aktif', 1),
('Pustu Pulau Pari', 'pustu-pulau-pari', 'Pulau Pari, Kepulauan Seribu Selatan', '021-12345680', 'pustu.pari@puskesmaskepselatan.com', 'Puskesmas Pembantu Pulau Pari melayani masyarakat di wilayah Pulau Pari dan sekitarnya.', 'aktif', 1),
('Pustu Pulau Lancang', 'pustu-pulau-lancang', 'Pulau Lancang, Kepulauan Seribu Selatan', '021-12345681', 'pustu.lancang@puskesmaskepselatan.com', 'Puskesmas Pembantu Pulau Lancang melayani masyarakat di wilayah Pulau Lancang dan sekitarnya.', 'aktif', 1);

-- Insert layanan default
INSERT INTO layanan (nama, slug, deskripsi_singkat, deskripsi_lengkap, urutan, status, created_by) VALUES
('Poli Umum', 'poli-umum', 'Pelayanan kesehatan dasar untuk semua usia', '<p>Poli Umum memberikan pelayanan kesehatan dasar untuk semua usia. Pelayanan meliputi pemeriksaan kesehatan umum, pengobatan penyakit ringan, konsultasi kesehatan, dan rujukan ke fasilitas kesehatan yang lebih tinggi jika diperlukan.</p><p>Dokter umum kami siap melayani Anda dengan profesional dan ramah.</p>', 1, 'aktif', 1),
('Poli Gigi', 'poli-gigi', 'Pelayanan kesehatan gigi dan mulut', '<p>Poli Gigi memberikan pelayanan kesehatan gigi dan mulut. Pelayanan meliputi pemeriksaan gigi rutin, penambalan gigi, pencabutan gigi, pembersihan karang gigi, dan konsultasi kesehatan gigi.</p><p>Dokter gigi kami siap melayani Anda dengan profesional dan ramah.</p>', 2, 'aktif', 1),
('Poli KIA/KB', 'poli-kia-kb', 'Pelayanan kesehatan ibu, anak, dan keluarga berencana', '<p>Poli KIA/KB memberikan pelayanan kesehatan ibu, anak, dan keluarga berencana. Pelayanan meliputi pemeriksaan kehamilan, imunisasi, pemantauan tumbuh kembang anak, dan konsultasi KB.</p><p>Bidan dan dokter kami siap melayani Anda dengan profesional dan ramah.</p>', 3, 'aktif', 1),
('UGD', 'ugd', 'Pelayanan gawat darurat 24 jam', '<p>Unit Gawat Darurat (UGD) memberikan pelayanan gawat darurat 24 jam. Pelayanan meliputi penanganan kasus gawat darurat, stabilisasi pasien, dan rujukan ke rumah sakit jika diperlukan.</p><p>Tim medis kami siap melayani Anda dengan cepat, tepat, dan profesional.</p>', 4, 'aktif', 1),
('Laboratorium', 'laboratorium', 'Pemeriksaan laboratorium dasar', '<p>Laboratorium memberikan pelayanan pemeriksaan laboratorium dasar. Pelayanan meliputi pemeriksaan darah rutin, urin rutin, feses rutin, dan pemeriksaan lainnya sesuai kebutuhan.</p><p>Petugas laboratorium kami siap melayani Anda dengan profesional dan akurat.</p>', 5, 'aktif', 1);