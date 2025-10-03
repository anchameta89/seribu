<?php
/**
 * File Konfigurasi Utama
 * Berisi konstanta dan fungsi-fungsi umum untuk website
 */

// Konstanta URL dasar
define('BASE_URL', 'http://localhost/websitePuskesmas2025');
define('ADMIN_URL', BASE_URL . '/admin');

// Konstanta direktori
define('ROOT_PATH', dirname(__DIR__));
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Konstanta untuk upload file
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Konstanta untuk session
define('SESSION_LIFETIME', 3600); // 1 jam

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Include file database
require_once ROOT_PATH . '/config/database.php';

/**
 * Fungsi untuk redirect ke URL tertentu
 * @param string $url URL tujuan
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Fungsi untuk mengecek apakah user sudah login
 * @return bool True jika sudah login, false jika belum
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Fungsi untuk mengecek apakah user adalah admin
 * @return bool True jika admin, false jika bukan
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/**
 * Fungsi untuk mengecek apakah session sudah expired
 * @return bool True jika expired, false jika belum
 */
function isSessionExpired() {
    if (!isLoggedIn()) {
        return true;
    }
    
    $lastActivity = $_SESSION['login_time'] ?? 0;
    $currentTime = time();
    
    return ($currentTime - $lastActivity) > SESSION_LIFETIME;
}

/**
 * Fungsi untuk refresh waktu login
 */
function refreshLoginTime() {
    $_SESSION['login_time'] = time();
}

/**
 * Fungsi untuk mendapatkan data user yang sedang login
 * @return array Data user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    return querySingle("SELECT * FROM users WHERE id = $userId");
}

/**
 * Fungsi untuk sanitasi input
 * @param string $input Input yang akan disanitasi
 * @return string Input yang sudah disanitasi
 */
function sanitize($input) {
    $conn = getConnection();
    $input = $conn->real_escape_string($input);
    closeConnection($conn);
    return $input;
}

/**
 * Fungsi untuk menghasilkan slug dari string
 * @param string $string String yang akan dijadikan slug
 * @return string Slug
 */
function generateSlug($string) {
    // Konversi ke lowercase
    $string = strtolower($string);
    
    // Ganti spasi dengan dash
    $string = str_replace(' ', '-', $string);
    
    // Hapus karakter selain alphanumeric dan dash
    $string = preg_replace('/[^a-z0-9-]/', '', $string);
    
    // Hapus multiple dash
    $string = preg_replace('/-+/', '-', $string);
    
    // Trim dash di awal dan akhir
    $string = trim($string, '-');
    
    return $string;
}

/**
 * Fungsi untuk menghasilkan token random
 * @param int $length Panjang token
 * @return string Token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Fungsi untuk set flash message
 * @param string $type Tipe pesan (success, error, warning, info)
 * @param string $message Isi pesan
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Fungsi untuk mendapatkan flash message
 * @param string $type Tipe pesan (success, error, warning, info)
 * @return string|null Isi pesan atau null jika tidak ada
 */
function getFlashMessage($type) {
    if (isset($_SESSION['flash_message']) && $_SESSION['flash_message']['type'] === $type) {
        $message = $_SESSION['flash_message']['message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Fungsi untuk upload file
 * @param array $file File yang akan diupload ($_FILES['nama_field'])
 * @param string $destination Direktori tujuan
 * @param array $allowedExtensions Ekstensi yang diperbolehkan
 * @param int $maxSize Ukuran maksimal file (dalam bytes)
 * @return array Hasil upload (success, message, filename)
 */
function uploadFile($file, $destination, $allowedExtensions = null, $maxSize = null) {
    // Set default values
    $allowedExtensions = $allowedExtensions ?? ALLOWED_EXTENSIONS;
    $maxSize = $maxSize ?? MAX_FILE_SIZE;
    
    // Cek apakah ada error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas maksimal yang diizinkan oleh server.',
            UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas maksimal yang diizinkan oleh form.',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Direktori temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menyimpan file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.'
        ];
        
        return [
            'success' => false,
            'message' => $errorMessages[$file['error']] ?? 'Terjadi kesalahan saat upload file.',
            'filename' => null
        ];
    }
    
    // Cek ukuran file
    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'message' => 'Ukuran file terlalu besar. Maksimal ' . formatBytes($maxSize) . '.',
            'filename' => null
        ];
    }
    
    // Cek ekstensi file
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return [
            'success' => false,
            'message' => 'Ekstensi file tidak diizinkan. Ekstensi yang diizinkan: ' . implode(', ', $allowedExtensions) . '.',
            'filename' => null
        ];
    }
    
    // Buat nama file unik
    $filename = uniqid() . '.' . $fileExtension;
    $targetPath = $destination . '/' . $filename;
    
    // Buat direktori jika belum ada
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'message' => 'File berhasil diupload.',
            'filename' => $filename
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal mengupload file.',
            'filename' => null
        ];
    }
}

/**
 * Fungsi untuk format ukuran file
 * @param int $bytes Ukuran file dalam bytes
 * @param int $precision Presisi desimal
 * @return string Ukuran file yang sudah diformat
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Fungsi untuk melakukan query dan mendapatkan satu hasil
 * @param mysqli $conn Koneksi database
 * @param string $sql Query SQL
 * @return array|null Hasil query atau null jika tidak ada
 */
function querySingle($conn, $sql) {
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Fungsi untuk insert data ke database
 * @param mysqli $conn Koneksi database
 * @param string $table Nama tabel
 * @param array $data Data yang akan diinsert (key => value)
 * @return bool True jika berhasil, false jika gagal
 */
function insert($conn, $table, $data) {
    $columns = implode(', ', array_keys($data));
    $values = implode(', ', array_map(function($value) use ($conn) {
        if ($value === null) {
            return 'NULL';
        } elseif (is_numeric($value)) {
            return $value;
        } else {
            return "'" . $conn->real_escape_string($value) . "'";
        }
    }, array_values($data)));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    
    return $conn->query($sql);
}

/**
 * Fungsi untuk update data di database
 * @param mysqli $conn Koneksi database
 * @param string $table Nama tabel
 * @param int $id ID data yang akan diupdate
 * @param array $data Data yang akan diupdate (key => value)
 * @return bool True jika berhasil, false jika gagal
 */
function update($conn, $table, $id, $data) {
    $set = implode(', ', array_map(function($key, $value) use ($conn) {
        if ($value === null) {
            return "$key = NULL";
        } elseif (is_numeric($value)) {
            return "$key = $value";
        } else {
            return "$key = '" . $conn->real_escape_string($value) . "'";
        }
    }, array_keys($data), array_values($data)));
    
    $sql = "UPDATE $table SET $set WHERE id = $id";
    
    return $conn->query($sql);
}

/**
 * Fungsi untuk delete data dari database
 * @param mysqli $conn Koneksi database
 * @param string $table Nama tabel
 * @param int $id ID data yang akan dihapus
 * @return bool True jika berhasil, false jika gagal
 */
function delete($conn, $table, $id) {
    $sql = "DELETE FROM $table WHERE id = $id";
    
    return $conn->query($sql);
}