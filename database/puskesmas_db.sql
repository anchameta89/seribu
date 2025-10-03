-- Database: `puskesmas_db`
--
CREATE DATABASE IF NOT EXISTS `puskesmas_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `puskesmas_db`;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','editor') NOT NULL DEFAULT 'editor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@puskesmas.go.id', 'admin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `berita`
--

CREATE TABLE `berita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `kategori_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `kategori_id` (`kategori_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_berita`
--

CREATE TABLE `kategori_berita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `kategori_berita`
--

INSERT INTO `kategori_berita` (`nama`, `slug`) VALUES
('Kesehatan Umum', 'kesehatan-umum'),
('Kesehatan Ibu dan Anak', 'kesehatan-ibu-dan-anak'),
('Imunisasi', 'imunisasi'),
('Gizi', 'gizi'),
('Penyakit Menular', 'penyakit-menular'),
('Penyakit Tidak Menular', 'penyakit-tidak-menular'),
('Kesehatan Lingkungan', 'kesehatan-lingkungan'),
('Program Puskesmas', 'program-puskesmas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`nama`, `deskripsi`, `icon`, `urutan`) VALUES
('Pemeriksaan Umum', 'Layanan pemeriksaan kesehatan umum untuk semua usia', 'fa-stethoscope', 1),
('Kesehatan Ibu dan Anak', 'Layanan pemeriksaan kehamilan, imunisasi, dan kesehatan anak', 'fa-baby', 2),
('Kesehatan Gigi dan Mulut', 'Layanan pemeriksaan dan perawatan gigi dan mulut', 'fa-tooth', 3),
('Laboratorium', 'Layanan pemeriksaan laboratorium dasar', 'fa-flask', 4),
('Farmasi', 'Layanan pengambilan obat dan konsultasi obat', 'fa-pills', 5),
('Gizi', 'Layanan konsultasi gizi dan pemantauan status gizi', 'fa-apple-alt', 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_pelayanan`
--

CREATE TABLE `jadwal_pelayanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `layanan_id` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_buka` time NOT NULL,
  `jam_tutup` time NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `layanan_id` (`layanan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `slider`
--

CREATE TABLE `slider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(100) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `halaman`
--

CREATE TABLE `halaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `konten` text NOT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pustu`
--

CREATE TABLE `pustu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `kontak` varchar(100) DEFAULT NULL,
  `jam_operasional` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pustu`
--

INSERT INTO `pustu` (`nama`, `slug`, `alamat`, `deskripsi`, `kontak`, `jam_operasional`) VALUES
('Pustu Untung Jawa', 'pustu-untung-jawa', 'Pulau Untung Jawa, Kepulauan Seribu', 'Puskesmas Pembantu yang melayani masyarakat di Pulau Untung Jawa', '021-12345678', 'Senin-Jumat: 08.00-15.00'),
('Pustu Pulau Pari', 'pustu-pulau-pari', 'Pulau Pari, Kepulauan Seribu', 'Puskesmas Pembantu yang melayani masyarakat di Pulau Pari', '021-12345679', 'Senin-Jumat: 08.00-15.00'),
('Pustu Pulau Lancang', 'pustu-pulau-lancang', 'Pulau Lancang, Kepulauan Seribu', 'Puskesmas Pembantu yang melayani masyarakat di Pulau Lancang', '021-12345680', 'Senin-Jumat: 08.00-15.00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file` varchar(255) NOT NULL,
  `kategori` enum('pola_penyakit','kia_kb','imunisasi','gizi','kesling') NOT NULL,
  `periode` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_situs` varchar(100) NOT NULL,
  `deskripsi_situs` text DEFAULT NULL,
  `alamat` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `jam_operasional` text DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_situs`, `deskripsi_situs`, `alamat`, `email`, `telepon`, `jam_operasional`, `facebook`, `twitter`, `instagram`, `youtube`) VALUES
(1, 'Puskesmas', 'Pelayanan Kesehatan Masyarakat', 'Jl. Kesehatan No. 123, Kecamatan Sehat, Kota Sejahtera', 'info@puskesmas.go.id', '021-12345678', 'Senin-Jumat: 08.00 - 16.00', 'https://facebook.com/puskesmas', 'https://twitter.com/puskesmas', 'https://instagram.com/puskesmas', 'https://youtube.com/puskesmas');

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

ALTER TABLE `berita`
  ADD CONSTRAINT `berita_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_berita` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `berita_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `jadwal_pelayanan`
  ADD CONSTRAINT `jadwal_pelayanan_ibfk_1` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`id`) ON DELETE CASCADE;