<?php
/**
 * Halaman Profil Admin
 */

// Mulai session
session_start();

// Include file auth.php dan config.php
require_once 'auth.php';
require_once '../config/config.php';

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

// Proses form update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $jabatan = $_POST['jabatan'] ?? '';
    
    // Validasi input
    if (empty($nama)) {
        $error = 'Nama lengkap harus diisi';
    } elseif (empty($email)) {
        $error = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Cek apakah email sudah digunakan oleh user lain
        $conn = getConnection();
        $userId = $currentUser['id'];
        $emailCheck = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $userId");
        
        if ($emailCheck->num_rows > 0) {
            $error = 'Email sudah digunakan oleh pengguna lain';
        } else {
            // Update profil
            $conn->query("UPDATE users SET nama_lengkap = '$nama', email = '$email', telepon = '$telepon', jabatan = '$jabatan', updated_at = NOW() WHERE id = $userId");
            
            // Update foto profil jika ada
            if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
                $uploadResult = uploadFile($_FILES['foto_profil'], 'users');
                
                if ($uploadResult['status']) {
                    $fotoPath = $uploadResult['path'];
                    $conn->query("UPDATE users SET foto = '$fotoPath' WHERE id = $userId");
                } else {
                    $error = $uploadResult['message'];
                }
            }
            
            if (empty($error)) {
                $success = 'Profil berhasil diperbarui';
                
                // Log aktivitas
                $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'update', 'Memperbarui profil')");
                
                // Refresh data user
                $currentUser = querySingle($conn, "SELECT * FROM users WHERE id = $userId");
            }
            
            closeConnection($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Puskesmas Kepulauan Seribu Selatan</title>
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
                            <img src="<?php echo !empty($currentUser['foto']) ? '../uploads/' . $currentUser['foto'] : 'assets/images/user-avatar.png'; ?>" alt="User Avatar" class="user-avatar">
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
                    <h1>Profil Admin</h1>
                    <nav class="breadcrumb">
                        <a href="index.php">Home</a> / 
                        <span>Profil</span>
                    </nav>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="profile-card">
                            <div class="profile-header">
                                <img src="<?php echo !empty($currentUser['foto']) ? '../uploads/' . $currentUser['foto'] : 'assets/images/user-avatar.png'; ?>" alt="User Avatar" class="profile-avatar">
                                <h3><?php echo $currentUser['nama_lengkap']; ?></h3>
                                <p><?php echo $currentUser['jabatan']; ?></p>
                            </div>
                            
                            <div class="profile-body">
                                <div class="profile-info">
                                    <div class="info-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo $currentUser['email']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo !empty($currentUser['telepon']) ? $currentUser['telepon'] : '-'; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Role: <?php echo $currentUser['role']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Bergabung: <?php echo date('d M Y', strtotime($currentUser['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="profile-footer">
                                <a href="change-password.php" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-key"></i> Ubah Password
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="form-card">
                            <div class="form-header">
                                <h3>Edit Profil</h3>
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
                                
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="nama_lengkap">Nama Lengkap</label>
                                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?php echo $currentUser['nama_lengkap']; ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $currentUser['email']; ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="telepon">Telepon</label>
                                        <input type="text" id="telepon" name="telepon" class="form-control" value="<?php echo $currentUser['telepon']; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="jabatan">Jabatan</label>
                                        <input type="text" id="jabatan" name="jabatan" class="form-control" value="<?php echo $currentUser['jabatan']; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="foto_profil">Foto Profil</label>
                                        <input type="file" id="foto_profil" name="foto_profil" class="form-control" accept="image/*">
                                        <div class="form-text">Format: JPG, PNG, GIF. Maks: 2MB</div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <a href="index.php" class="btn btn-light">Batal</a>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="form-card mt-4">
                            <div class="form-header">
                                <h3>Aktivitas Terakhir</h3>
                            </div>
                            
                            <div class="form-body">
                                <div class="activity-log">
                                    <?php
                                    $conn = getConnection();
                                    $userId = $currentUser['id'];
                                    $activities = $conn->query("SELECT * FROM log_aktivitas WHERE user_id = $userId ORDER BY created_at DESC LIMIT 5");
                                    closeConnection($conn);
                                    
                                    if ($activities->num_rows > 0) {
                                        while ($activity = $activities->fetch_assoc()) {
                                            $icon = 'fa-info-circle';
                                            $color = 'text-info';
                                            
                                            if ($activity['tipe'] == 'create') {
                                                $icon = 'fa-plus-circle';
                                                $color = 'text-success';
                                            } elseif ($activity['tipe'] == 'update') {
                                                $icon = 'fa-edit';
                                                $color = 'text-primary';
                                            } elseif ($activity['tipe'] == 'delete') {
                                                $icon = 'fa-trash';
                                                $color = 'text-danger';
                                            } elseif ($activity['tipe'] == 'login') {
                                                $icon = 'fa-sign-in-alt';
                                                $color = 'text-warning';
                                            } elseif ($activity['tipe'] == 'logout') {
                                                $icon = 'fa-sign-out-alt';
                                                $color = 'text-secondary';
                                            }
                                    ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?php echo $color; ?>">
                                            <i class="fas <?php echo $icon; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p><?php echo $activity['deskripsi']; ?></p>
                                            <small><?php echo date('d M Y H:i', strtotime($activity['created_at'])); ?></small>
                                        </div>
                                    </div>
                                    <?php
                                        }
                                    } else {
                                    ?>
                                    <div class="text-center py-3">
                                        <p>Belum ada aktivitas</p>
                                    </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
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
        
        // Preview image before upload
        document.getElementById('foto_profil').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-avatar').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>