<?php
/**
 * File Autentikasi
 * Berisi fungsi-fungsi untuk autentikasi user
 */

// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';

/**
 * Fungsi untuk login user
 * @param string $username Username
 * @param string $password Password
 * @param bool $remember Apakah menggunakan fitur remember me
 * @return bool True jika berhasil login, false jika gagal
 */
function login($username, $password, $remember = false) {
    // Sanitasi input
    $username = sanitize($username);
    
    // Koneksi ke database
    $conn = getConnection();
    
    // Cek user di database
    $user = querySingle($conn, "SELECT * FROM users WHERE username = '$username' AND status = 'aktif'");
    
    if (!$user) {
        closeConnection($conn);
        return false;
    }
    
    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // Set cookie jika remember me dicentang
        if ($remember) {
            $token = generateToken();
            $userId = $user['id'];
            
            // Simpan token ke database
            $conn->query("UPDATE users SET remember_token = '$token' WHERE id = $userId");
            
            // Set cookie selama 30 hari
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
        }
        
        // Update last login time
        $userId = $user['id'];
        $conn->query("UPDATE users SET last_login = NOW() WHERE id = $userId");
        
        // Log aktivitas login
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi, ip_address, user_agent) 
                     VALUES ($userId, 'login', 'Login ke sistem', '$ip', '$userAgent')");
        
        closeConnection($conn);
        return true;
    }
    
    closeConnection($conn);
    return false;
}

/**
 * Fungsi untuk logout user
 */
function logout() {
    // Log aktivitas logout jika user sedang login
    if (isLoggedIn()) {
        $conn = getConnection();
        $userId = $_SESSION['user_id'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi, ip_address, user_agent) 
                     VALUES ($userId, 'logout', 'Logout dari sistem', '$ip', '$userAgent')");
        closeConnection($conn);
        
        // Hapus cookie remember me
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    
    // Hapus semua data session
    $_SESSION = [];
    
    // Hapus cookie session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Hancurkan session
    session_destroy();
}

/**
 * Fungsi untuk mengecek login dengan remember me
 * @return bool True jika berhasil login dengan remember me, false jika gagal
 */
function checkRememberMe() {
    if (isLoggedIn()) {
        return true;
    }
    
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $conn = getConnection();
        
        $user = querySingle($conn, "SELECT * FROM users WHERE remember_token = '$token' AND status = 'aktif'");
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Update last login time
            $userId = $user['id'];
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = $userId");
            
            // Log aktivitas login
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi, ip_address, user_agent) 
                         VALUES ($userId, 'login', 'Login otomatis dengan remember me', '$ip', '$userAgent')");
            
            closeConnection($conn);
            return true;
        }
        
        closeConnection($conn);
    }
    
    return false;
}

/**
 * Fungsi untuk mengirim email reset password
 * @param string $email Email user
 * @return bool True jika berhasil mengirim email, false jika gagal
 */
function resetPassword($email) {
    // Sanitasi input
    $email = sanitize($email);
    
    // Koneksi ke database
    $conn = getConnection();
    
    // Cek email di database
    $user = querySingle($conn, "SELECT * FROM users WHERE email = '$email' AND status = 'aktif'");
    
    if (!$user) {
        closeConnection($conn);
        return false;
    }
    
    // Generate token reset password
    $token = generateToken();
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $userId = $user['id'];
    
    // Simpan token ke database
    $conn->query("UPDATE users SET reset_token = '$token', reset_token_expires_at = '$expires' WHERE id = $userId");
    
    // Kirim email reset password
    $resetUrl = ADMIN_URL . '/reset-password.php?token=' . $token;
    $subject = 'Reset Password - Puskesmas Kepulauan Seribu Selatan';
    $message = "Halo {$user['nama_lengkap']},\n\n";
    $message .= "Anda telah meminta untuk mereset password akun Anda.\n";
    $message .= "Silakan klik link berikut untuk mereset password Anda:\n";
    $message .= "$resetUrl\n\n";
    $message .= "Link ini akan kadaluarsa dalam 1 jam.\n\n";
    $message .= "Jika Anda tidak meminta reset password, abaikan email ini.\n\n";
    $message .= "Terima kasih,\nTim Puskesmas Kepulauan Seribu Selatan";
    
    $headers = "From: noreply@puskesmaskepselatan.com\r\n";
    $headers .= "Reply-To: noreply@puskesmaskepselatan.com\r\n";
    
    // Log aktivitas reset password
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi, ip_address, user_agent) 
                 VALUES ($userId, 'reset_password', 'Meminta reset password', '$ip', '$userAgent')");
    
    closeConnection($conn);
    
    // Kirim email
    return mail($email, $subject, $message, $headers);
}

/**
 * Fungsi untuk validasi token reset password
 * @param string $token Token reset password
 * @return array|bool Data user jika token valid, false jika tidak valid
 */
function validateResetToken($token) {
    // Sanitasi input
    $token = sanitize($token);
    
    // Koneksi ke database
    $conn = getConnection();
    
    // Cek token di database
    $user = querySingle($conn, "SELECT * FROM users WHERE reset_token = '$token' AND reset_token_expires_at > NOW() AND status = 'aktif'");
    
    closeConnection($conn);
    
    return $user ?: false;
}

/**
 * Fungsi untuk reset password dengan token
 * @param string $token Token reset password
 * @param string $password Password baru
 * @return bool True jika berhasil reset password, false jika gagal
 */
function resetPasswordWithToken($token, $password) {
    // Sanitasi input
    $token = sanitize($token);
    
    // Koneksi ke database
    $conn = getConnection();
    
    // Cek token di database
    $user = querySingle($conn, "SELECT * FROM users WHERE reset_token = '$token' AND reset_token_expires_at > NOW() AND status = 'aktif'");
    
    if (!$user) {
        closeConnection($conn);
        return false;
    }
    
    // Hash password baru
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $userId = $user['id'];
    
    // Update password dan hapus token
    $conn->query("UPDATE users SET password = '$hashedPassword', reset_token = NULL, reset_token_expires_at = NULL WHERE id = $userId");
    
    // Log aktivitas reset password
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi, ip_address, user_agent) 
                 VALUES ($userId, 'reset_password', 'Reset password berhasil', '$ip', '$userAgent')");
    
    closeConnection($conn);
    
    return true;
}

/**
 * Fungsi untuk mengubah password
 * @param int $userId ID user
 * @param string $oldPassword Password lama
 * @param string $newPassword Password baru
 * @return bool True jika berhasil mengubah password, false jika gagal
 */
function changePassword($userId, $oldPassword, $newPassword) {
    // Koneksi ke database
    $conn = getConnection();
    
    // Cek user di database
    $user = querySingle($conn, "SELECT * FROM users WHERE id = $userId");
    
    if (!$user) {
        closeConnection($conn);
        return false;
    }
    
    // Verifikasi password lama
    if (!password_verify($oldPassword, $user['password'])) {
        closeConnection($conn);
        return false;
    }
    
    // Hash password baru
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $conn->query("UPDATE users SET password = '$hashedPassword' WHERE id = $userId");
    
    // Log aktivitas ubah password
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi, ip_address, user_agent) 
                 VALUES ($userId, 'update', 'Mengubah password', '$ip', '$userAgent')");
    
    closeConnection($conn);
    
    return true;
}
?>