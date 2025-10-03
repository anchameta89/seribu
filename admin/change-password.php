<?php
/**
 * Halaman Ubah Password
 */

// Mulai session
session_start();

// Include file auth.php
require_once 'auth.php';

// Cek login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Cek session expired
if (isSessionExpired()) {
    logout();
    setFlashMessage('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
    header('Location: login.php');
    exit;
}

// Refresh login time
refreshLoginTime();

// Ambil data user yang sedang login
$currentUser = getCurrentUser();

// Inisialisasi variabel
$error = '';
$success = '';

// Proses form ubah password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validasi input
    if (empty($oldPassword)) {
        $error = 'Password lama harus diisi';
    } elseif (empty($newPassword)) {
        $error = 'Password baru harus diisi';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password minimal 8 karakter';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Konfirmasi password tidak sesuai';
    } else {
        // Proses ubah password
        if (changePassword($currentUser['id'], $oldPassword, $newPassword)) {
            $success = 'Password berhasil diubah';
            
            // Log aktivitas
            $conn = getConnection();
            $userId = $currentUser['id'];
            $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'update', 'Mengubah password')");
            closeConnection($conn);
        } else {
            $error = 'Password lama tidak sesuai';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - Puskesmas Kepulauan Seribu Selatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>Puskesmas Kep. Seribu Selatan</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="berita/index.php">
                            <i class="fas fa-newspaper"></i>
                            <span>Berita</span>
                        </a>
                    </li>
                    <li>
                        <a href="layanan/index.php">
                            <i class="fas fa-hand-holding-medical"></i>
                            <span>Layanan</span>
                        </a>
                    </li>
                    <li>
                        <a href="jadwal/index.php">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Jadwal</span>
                        </a>
                    </li>
                    <li>
                        <a href="pustu/index.php">
                            <i class="fas fa-hospital"></i>
                            <span>Pustu</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan/index.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Laporan</span>
                        </a>
                    </li>
                    <li>
                        <a href="halaman/index.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Halaman</span>
                        </a>
                    </li>
                    <li>
                        <a href="slider/index.php">
                            <i class="fas fa-images"></i>
                            <span>Slider</span>
                        </a>
                    </li>
                    <li>
                        <a href="users/index.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="pengaturan/index.php">
                            <i class="fas fa-cog"></i>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="user-info">
                    <div class="notifications">
                        <a href="#" class="notification-icon">
                            <i class="fas fa-bell"></i>
                            <span class="badge">3</span>
                        </a>
                    </div>
                    
                    <div class="user-dropdown">
                        <a href="#" class="user-dropdown-toggle">
                            <img src="assets/images/user-avatar.png" alt="User Avatar" class="user-avatar">
                            <span class="user-name"><?php echo $currentUser['nama_lengkap']; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="user-dropdown-menu">
                            <a href="profile.php">
                                <i class="fas fa-user"></i>
                                <span>Profil</span>
                            </a>
                            <a href="change-password.php">
                                <i class="fas fa-key"></i>
                                <span>Ubah Password</span>
                            </a>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="content">
                <div class="content-header">
                    <h1>Ubah Password</h1>
                    <nav class="breadcrumb">
                        <a href="index.php">Home</a> / 
                        <span>Ubah Password</span>
                    </nav>
                </div>
                
                <div class="form-card">
                    <div class="form-header">
                        <h3>Form Ubah Password</h3>
                    </div>
                    
                    <div class="form-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="old_password">Password Lama</label>
                                <input type="password" id="old_password" name="old_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Password Baru</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                <div class="form-text">Password minimal 8 karakter</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Password Baru</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <div class="form-actions">
                                <a href="index.php" class="btn btn-light">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Toggle sidebar
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
        });
        
        // User dropdown
        document.querySelector('.user-dropdown-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.user-dropdown-menu').classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown')) {
                const dropdown = document.querySelector('.user-dropdown-menu');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>
</body>
</html>