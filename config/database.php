<?php
/**
 * Konfigurasi Database
 * File ini berisi konfigurasi untuk koneksi ke database MySQL
 */

// Konstanta untuk konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', ''); // Ganti dengan password database Anda
define('DB_NAME', 'puskesmas_db');

/**
 * Fungsi untuk membuat koneksi ke database
 * @return mysqli Object koneksi database
 */
function getConnection() {
    // Membuat koneksi baru
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Memeriksa koneksi
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
    
    // Set karakter encoding
    $conn->set_charset("utf8");
    
    return $conn;
}

/**
 * Fungsi untuk menutup koneksi database
 * @param mysqli $conn Object koneksi database
 */
function closeConnection($conn) {
    $conn->close();
}

/**
 * Fungsi untuk melakukan query dan mendapatkan semua hasil dalam bentuk array
 * @param string $sql Query SQL yang akan dijalankan
 * @return array Hasil query dalam bentuk array asosiatif
 */
function query($sql) {
    $conn = getConnection();
    $result = $conn->query($sql);
    
    $data = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    closeConnection($conn);
    return $data;
}

/**
 * Fungsi untuk melakukan query dan mendapatkan satu hasil
 * @param string $sql Query SQL yang akan dijalankan
 * @return array|null Hasil query dalam bentuk array asosiatif atau null jika tidak ada hasil
 */
function querySingle($sql) {
    $conn = getConnection();
    $result = $conn->query($sql);
    
    $data = null;
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
    }
    
    closeConnection($conn);
    return $data;
}

/**
 * Fungsi untuk melakukan insert data ke database
 * @param string $table Nama tabel
 * @param array $data Data yang akan diinsert dalam bentuk array asosiatif
 * @return int|bool ID dari data yang diinsert atau false jika gagal
 */
function insert($table, $data) {
    $conn = getConnection();
    
    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_fill(0, count($data), "?"));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $types = '';
        $values = [];
        
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $values[] = $value;
        }
        
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        
        $insertId = $stmt->insert_id;
        $stmt->close();
        closeConnection($conn);
        
        return $insertId;
    }
    
    closeConnection($conn);
    return false;
}

/**
 * Fungsi untuk melakukan update data di database
 * @param string $table Nama tabel
 * @param array $data Data yang akan diupdate dalam bentuk array asosiatif
 * @param string $where Kondisi WHERE untuk update
 * @return bool True jika berhasil, false jika gagal
 */
function update($table, $data, $where) {
    $conn = getConnection();
    
    $set = [];
    foreach ($data as $column => $value) {
        $set[] = "$column = ?";
    }
    
    $sql = "UPDATE $table SET " . implode(", ", $set) . " WHERE $where";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $types = '';
        $values = [];
        
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $values[] = $value;
        }
        
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        
        $stmt->close();
        closeConnection($conn);
        
        return $result;
    }
    
    closeConnection($conn);
    return false;
}

/**
 * Fungsi untuk melakukan delete data dari database
 * @param string $table Nama tabel
 * @param string $where Kondisi WHERE untuk delete
 * @return bool True jika berhasil, false jika gagal
 */
function delete($table, $where) {
    $conn = getConnection();
    
    $sql = "DELETE FROM $table WHERE $where";
    $result = $conn->query($sql);
    
    closeConnection($conn);
    return $result;
}

/**
 * Fungsi untuk melakukan escape string untuk mencegah SQL Injection
 * @param string $string String yang akan di-escape
 * @return string String yang sudah di-escape
 */
function escapeString($string) {
    $conn = getConnection();
    $escaped = $conn->real_escape_string($string);
    closeConnection($conn);
    return $escaped;
}

/**
 * Fungsi untuk mendapatkan jumlah baris dari hasil query
 * @param string $table Nama tabel
 * @param string $where Kondisi WHERE (opsional)
 * @return int Jumlah baris
 */
function count_rows($table, $where = '') {
    $conn = getConnection();
    
    $sql = "SELECT COUNT(*) as total FROM $table";
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    
    closeConnection($conn);
    return (int) $data['total'];
}
?>