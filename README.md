# Website Puskesmas Kepulauan Seribu Selatan

Website resmi Puskesmas Kepulauan Seribu Selatan yang dibangun dengan PHP, MySQL, HTML, CSS, dan JavaScript.

## Fitur

### Frontend
- Halaman Beranda dengan slider gambar
- Halaman Profil Puskesmas (Visi Misi, Tata Nilai, Struktur Organisasi)
- Halaman Berita dengan artikel kesehatan
- Halaman Informasi (Informasi Pelayanan, Jam Pelayanan, Alur Pendaftaran)
- Halaman Jejaring Puskesmas (Pustu Untung Jawa, Pustu Pulau Pari, Pustu Pulau Lancang)
- Halaman Data (Laporan, Aturan)
- Tampilan responsif untuk semua perangkat

### Backend (Admin Panel)
- Sistem login dan autentikasi
- Manajemen Berita dan Kategori
- Manajemen Layanan
- Manajemen Jadwal Pelayanan
- Manajemen Pustu (Puskesmas Pembantu)
- Manajemen Slider
- Manajemen Halaman Statis
- Manajemen Laporan
- Manajemen Pengguna
- Pengaturan Website

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Browser modern (Chrome, Firefox, Safari, Edge)

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/websitePuskesmas2025.git
cd websitePuskesmas2025
```

### 2. Import Database

Anda dapat menggunakan salah satu dari dua cara berikut:

#### Cara 1: Menggunakan Installer Otomatis

1. Buka browser dan akses `http://localhost/websitePuskesmas2025/install.php`
2. Ikuti langkah-langkah instalasi yang ditampilkan:
   - Cek persyaratan sistem
   - Masukkan konfigurasi database
   - Proses instalasi akan berjalan otomatis

#### Cara 2: Import Manual

1. Buat database baru di MySQL dengan nama `puskesmas_db`
2. Import file `database/database.sql` ke database yang telah dibuat

```bash
mysql -u username -p puskesmas_db < database/database.sql
```

### 3. Konfigurasi Database

Edit file `config/database.php` dan sesuaikan dengan konfigurasi database Anda:

```php
// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'username'); // Ganti dengan username MySQL Anda
define('DB_PASS', 'password'); // Ganti dengan password MySQL Anda
define('DB_NAME', 'puskesmas_db');
```

### 4. Konfigurasi URL

Edit file `config/config.php` dan sesuaikan dengan URL website Anda:

```php
// URL dasar
define('BASE_URL', 'http://localhost/websitePuskesmas2025');
define('ADMIN_URL', BASE_URL . '/admin');
```

### 5. Folder Upload

Pastikan folder `uploads` dan subfolder-nya memiliki izin tulis (writable):

```bash
chmod -R 755 uploads/
```

## Akses Admin Panel

Setelah instalasi, Anda dapat mengakses admin panel melalui:

```
http://localhost/websitePuskesmas2025/admin/
```

Gunakan kredensial default:
- Username: admin
- Password: password

**PENTING**: Segera ubah password default setelah login pertama kali!

## Struktur Direktori

```
websitePuskesmas2025/
├── admin/               # Admin panel
│   ├── berita/          # Manajemen berita
│   ├── layanan/         # Manajemen layanan
│   ├── jadwal/          # Manajemen jadwal
│   ├── pustu/           # Manajemen pustu
│   ├── laporan/         # Manajemen laporan
│   ├── pengguna/        # Manajemen pengguna
│   ├── pengaturan/      # Pengaturan website
│   └── ...              # File admin lainnya
├── assets/              # Asset statis (CSS, JS, images)
│   ├── css/             # File CSS
│   ├── js/              # File JavaScript
│   ├── img/             # Gambar statis
│   └── vendor/          # Library pihak ketiga
├── config/              # File konfigurasi
├── database/            # File SQL database
├── includes/            # File include PHP
├── uploads/             # Folder upload
│   ├── berita/          # Upload gambar berita
│   ├── layanan/         # Upload gambar layanan
│   ├── slider/          # Upload gambar slider
│   └── users/           # Upload foto profil user
└── ...                  # File utama website
```

## Keamanan

- Semua password di-hash menggunakan `password_hash()` dengan algoritma default PHP
- Validasi input untuk mencegah SQL Injection
- Sanitasi output untuk mencegah XSS
- CSRF Protection untuk form
- Session timeout untuk keamanan admin panel

## Pengembangan

Website ini dikembangkan dengan:
- PHP Native (tanpa framework)
- MySQL untuk database
- Bootstrap 5 untuk frontend
- jQuery untuk manipulasi DOM dan AJAX
- Font Awesome untuk ikon
- TinyMCE untuk editor WYSIWYG

## Lisensi

Hak Cipta © 2023 Puskesmas Kepulauan Seribu Selatan. Hak Cipta Dilindungi.