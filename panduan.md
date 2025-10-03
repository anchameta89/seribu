# Panduan Penggunaan Website Puskesmas Kepulauan Seribu Selatan

Dokumen ini berisi panduan lengkap untuk penggunaan website Puskesmas Kepulauan Seribu Selatan, baik untuk pengguna frontend maupun administrator backend.

## Daftar Isi

1. [Instalasi](#instalasi)
2. [Penggunaan Backend (Admin)](#penggunaan-backend-admin)
3. [Manajemen Konten](#manajemen-konten)
4. [Pengaturan Website](#pengaturan-website)
5. [Manajemen Pengguna](#manajemen-pengguna)
6. [Troubleshooting](#troubleshooting)

## Instalasi

### Menggunakan Installer Otomatis

1. Buka browser dan akses `http://localhost/websitePuskesmas2025/install.php`
2. Ikuti langkah-langkah instalasi:
   - **Langkah 1**: Cek persyaratan sistem (PHP, ekstensi, folder writable)
   - **Langkah 2**: Masukkan konfigurasi database (host, username, password, nama database)
   - **Langkah 3**: Konfirmasi jika database sudah ada
   - **Langkah 4**: Proses instalasi database
   - **Langkah 5**: Selesai, Anda akan mendapatkan informasi login admin

### Import Database Manual

1. Buat database baru di MySQL dengan nama `puskesmas_db`
2. Import file `database/database.sql` ke database
   ```bash
   mysql -u username -p puskesmas_db < database/database.sql
   ```
3. Edit file `config/database.php` sesuai konfigurasi database Anda

## Penggunaan Backend (Admin)

### Login Admin

1. Akses halaman admin di `http://localhost/websitePuskesmas2025/admin/`
2. Masukkan kredensial default:
   - Username: `admin`
   - Password: `password`
3. Centang "Ingat saya" jika ingin tetap login
4. Klik tombol "Login"

### Lupa Password

1. Klik link "Lupa password?" pada halaman login
2. Masukkan alamat email yang terdaftar
3. Cek email untuk mendapatkan link reset password
4. Klik link dan masukkan password baru

### Dashboard Admin

Dashboard admin menampilkan:
- Statistik website (jumlah berita, layanan, pustu, pengguna)
- Berita terbaru
- Log aktivitas terbaru
- Menu navigasi untuk semua fitur admin

## Manajemen Konten

### Manajemen Berita

#### Menambah Berita Baru

1. Klik menu "Berita" > "Tambah Berita"
2. Isi formulir berita:
   - Judul berita
   - Kategori
   - Isi berita (gunakan editor WYSIWYG)
   - Gambar thumbnail (opsional)
   - Status publikasi
3. Klik "Simpan"

#### Mengedit Berita

1. Klik menu "Berita" > "Daftar Berita"
2. Cari berita yang ingin diedit
3. Klik ikon "Edit"
4. Ubah informasi yang diperlukan
5. Klik "Simpan"

#### Menghapus Berita

1. Klik menu "Berita" > "Daftar Berita"
2. Cari berita yang ingin dihapus
3. Klik ikon "Hapus"
4. Konfirmasi penghapusan

### Manajemen Kategori Berita

1. Klik menu "Berita" > "Kategori"
2. Untuk menambah kategori, isi form di bagian atas
3. Untuk mengedit, klik ikon "Edit" pada kategori
4. Untuk menghapus, klik ikon "Hapus" pada kategori

### Manajemen Layanan

#### Menambah Layanan

1. Klik menu "Layanan" > "Tambah Layanan"
2. Isi formulir layanan:
   - Nama layanan
   - Deskripsi
   - Ikon (opsional)
   - Status
3. Klik "Simpan"

#### Mengedit/Menghapus Layanan

1. Klik menu "Layanan" > "Daftar Layanan"
2. Gunakan ikon "Edit" atau "Hapus" sesuai kebutuhan

### Manajemen Jadwal Pelayanan

1. Klik menu "Jadwal" > "Jadwal Pelayanan"
2. Tambah jadwal baru dengan mengisi form
3. Edit jadwal dengan mengklik ikon "Edit"
4. Hapus jadwal dengan mengklik ikon "Hapus"

### Manajemen Pustu (Puskesmas Pembantu)

1. Klik menu "Pustu" > "Daftar Pustu"
2. Tambah Pustu baru dengan mengklik "Tambah Pustu"
3. Kelola layanan dan jadwal Pustu melalui submenu

### Manajemen Slider

1. Klik menu "Slider"
2. Tambah slider baru dengan mengklik "Tambah Slider"
3. Isi judul, deskripsi, dan upload gambar
4. Atur urutan tampilan dengan mengubah nilai "Urutan"

### Manajemen Halaman Statis

1. Klik menu "Halaman"
2. Tambah halaman baru atau edit halaman yang sudah ada
3. Gunakan editor WYSIWYG untuk mengedit konten

## Pengaturan Website

1. Klik menu "Pengaturan"
2. Ubah informasi website:
   - Nama website
   - Deskripsi
   - Alamat
   - Kontak (telepon, email)
   - Media sosial
   - Logo
   - Favicon
3. Klik "Simpan Pengaturan"

## Manajemen Pengguna

### Menambah Pengguna Baru

1. Klik menu "Pengguna" > "Tambah Pengguna"
2. Isi formulir pengguna:
   - Nama lengkap
   - Username
   - Email
   - Password
   - Role (Admin/Editor)
   - Status
3. Klik "Simpan"

### Mengedit Pengguna

1. Klik menu "Pengguna" > "Daftar Pengguna"
2. Klik ikon "Edit" pada pengguna
3. Ubah informasi yang diperlukan
4. Klik "Simpan"

### Menghapus Pengguna

1. Klik menu "Pengguna" > "Daftar Pengguna"
2. Klik ikon "Hapus" pada pengguna
3. Konfirmasi penghapusan

### Mengubah Password

1. Klik nama pengguna di pojok kanan atas
2. Pilih "Profil"
3. Isi form ubah password
4. Klik "Ubah Password"

## Troubleshooting

### Masalah Login

1. **Lupa Password**: Gunakan fitur "Lupa Password" di halaman login
2. **Tidak Bisa Login**: Pastikan username dan password benar
3. **Session Expired**: Login kembali jika sesi telah berakhir

### Masalah Database

1. **Koneksi Database Gagal**:
   - Periksa file `config/database.php`
   - Pastikan server MySQL berjalan
   - Verifikasi username dan password database

2. **Error Saat Import Database**:
   - Gunakan installer otomatis
   - Pastikan versi MySQL kompatibel
   - Cek hak akses user database

### Masalah Upload File

1. **Gagal Upload**:
   - Periksa izin folder `uploads/`
   - Pastikan ukuran file tidak melebihi batas
   - Verifikasi tipe file diizinkan

2. **File Tidak Tampil**:
   - Periksa path file di database
   - Pastikan file ada di folder uploads

### Kontak Support

Jika Anda mengalami masalah yang tidak tercantum di atas, silakan hubungi tim support:

- Email: support@puskesmaskepulauanseribu.go.id
- Telepon: (021) 12345678

---

© 2025 Puskesmas Kepulauan Seribu Selatan. Hak Cipta Dilindungi.