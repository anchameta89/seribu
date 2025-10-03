<?php
/**
 * Installer untuk Website Puskesmas
 * File ini digunakan untuk menginstall database website Puskesmas
 */

// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi variabel
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';
$dbConfig = [];

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Cek persyaratan sistem
    if ($step === 1) {
        $step = 2;
    }
    // Step 2: Konfigurasi database
    elseif ($step === 2) {
        $dbConfig = [
            'host' => $_POST['db_host'] ?? '',
            'user' => $_POST['db_user'] ?? '',
            'pass' => $_POST['db_pass'] ?? '',
            'name' => $_POST['db_name'] ?? ''
        ];
        
        // Validasi input
        if (empty($dbConfig['host']) || empty($dbConfig['user']) || empty($dbConfig['name'])) {
            $error = 'Semua field harus diisi kecuali password (opsional)';
        } else {
            // Coba koneksi ke database
            try {
                $conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass']);
                
                if ($conn->connect_error) {
                    throw new Exception("Koneksi database gagal: " . $conn->connect_error);
                }
                
                // Cek apakah database sudah ada
                $result = $conn->query("SHOW DATABASES LIKE '{$dbConfig['name']}'");
                
                if ($result->num_rows > 0) {
                    // Database sudah ada, konfirmasi overwrite
                    $_SESSION['db_config'] = $dbConfig;
                    $step = 3;
                } else {
                    // Buat database baru
                    if ($conn->query("CREATE DATABASE {$dbConfig['name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                        $_SESSION['db_config'] = $dbConfig;
                        $step = 4;
                    } else {
                        throw new Exception("Gagal membuat database: " . $conn->error);
                    }
                }
                
                $conn->close();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
    // Step 3: Konfirmasi overwrite database
    elseif ($step === 3) {
        $confirm = $_POST['confirm'] ?? '';
        
        if ($confirm === 'yes') {
            $step = 4;
        } else {
            $step = 2;
            $error = 'Instalasi dibatalkan. Silakan masukkan nama database lain.';
        }
    }
    // Step 4: Import database
    elseif ($step === 4) {
        $dbConfig = $_SESSION['db_config'] ?? [];
        
        if (empty($dbConfig)) {
            $error = 'Konfigurasi database tidak ditemukan. Silakan ulangi proses instalasi.';
            $step = 1;
        } else {
            try {
                // Koneksi ke database
                $conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['name']);
                
                if ($conn->connect_error) {
                    throw new Exception("Koneksi database gagal: " . $conn->connect_error);
                }
                
                // Baca file SQL
                $sqlFile = file_get_contents('database/database.sql');
                
                if ($sqlFile === false) {
                    throw new Exception("Gagal membaca file database.sql");
                }
                
                // Hapus komentar dan baris kosong
                $sqlFile = preg_replace('/--.*$/m', '', $sqlFile);
                $sqlFile = preg_replace('/\n\r/m', '\n', $sqlFile);
                $sqlFile = preg_replace('/\r\n/m', '\n', $sqlFile);
                
                // Hapus perintah DROP DATABASE dan CREATE DATABASE
                $sqlFile = preg_replace('/DROP DATABASE.*?;/is', '', $sqlFile);
                $sqlFile = preg_replace('/CREATE DATABASE.*?;/is', '', $sqlFile);
                $sqlFile = preg_replace('/USE.*?;/is', '', $sqlFile);
                
                // Split menjadi query terpisah
                $queries = preg_split('/;\s*\n/is', $sqlFile);
                
                // Eksekusi query
                foreach ($queries as $query) {
                    $query = trim($query);
                    
                    if (!empty($query)) {
                        if (!$conn->query($query)) {
                            throw new Exception("Error executing query: " . $conn->error . "<br>Query: " . $query);
                        }
                    }
                }
                
                // Update file konfigurasi database
                $configFile = 'config/database.php';
                $configContent = file_get_contents($configFile);
                
                if ($configContent === false) {
                    throw new Exception("Gagal membaca file konfigurasi database");
                }
                
                // Update konfigurasi
                $configContent = preg_replace("/define\('DB_HOST', '.*?'\);/", "define('DB_HOST', '{$dbConfig['host']}');", $configContent);
                $configContent = preg_replace("/define\('DB_USER', '.*?'\);/", "define('DB_USER', '{$dbConfig['user']}');", $configContent);
                $configContent = preg_replace("/define\('DB_PASS', '.*?'\);/", "define('DB_PASS', '{$dbConfig['pass']}');", $configContent);
                $configContent = preg_replace("/define\('DB_NAME', '.*?'\);/", "define('DB_NAME', '{$dbConfig['name']}');", $configContent);
                
                // Tulis kembali file konfigurasi
                if (file_put_contents($configFile, $configContent) === false) {
                    throw new Exception("Gagal menulis file konfigurasi database");
                }
                
                $conn->close();
                
                // Hapus session
                unset($_SESSION['db_config']);
                
                $success = 'Database berhasil diinstall. Silakan login dengan username: admin dan password: password';
                $step = 5;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Cek persyaratan sistem
$requirements = [
    'php_version' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'mysqli' => extension_loaded('mysqli'),
    'pdo' => extension_loaded('pdo'),
    'gd' => extension_loaded('gd'),
    'uploads_writable' => is_writable('uploads'),
    'config_writable' => is_writable('config')
];

$allRequirementsMet = !in_array(false, $requirements, true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer - Website Puskesmas Kepulauan Seribu Selatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .installer-container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .installer-header {
            background-color: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .installer-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .installer-header p {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 0;
        }
        
        .installer-body {
            padding: 30px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #dee2e6;
            z-index: 1;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            position: relative;
            z-index: 2;
        }
        
        .step.active {
            background-color: #0d6efd;
            color: white;
        }
        
        .step.completed {
            background-color: #198754;
            color: white;
        }
        
        .step-label {
            position: absolute;
            top: 35px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: #6c757d;
            white-space: nowrap;
        }
        
        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .requirement-item:last-child {
            border-bottom: none;
        }
        
        .requirement-status {
            font-weight: bold;
        }
        
        .requirement-status.passed {
            color: #198754;
        }
        
        .requirement-status.failed {
            color: #dc3545;
        }
        
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .alert {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>Installer Website Puskesmas</h1>
            <p>Kepulauan Seribu Selatan</p>
        </div>
        
        <div class="installer-body">
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    1
                    <span class="step-label">Persyaratan</span>
                </div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    2
                    <span class="step-label">Database</span>
                </div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?> <?php echo $step > 4 ? 'completed' : ''; ?>">
                    3
                    <span class="step-label">Instalasi</span>
                </div>
                <div class="step <?php echo $step >= 5 ? 'active' : ''; ?>">
                    4
                    <span class="step-label">Selesai</span>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step === 1): ?>
                <!-- Step 1: Cek persyaratan sistem -->
                <h3 class="mb-4">Persyaratan Sistem</h3>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="requirement-item">
                            <div>PHP Version (>= 7.4.0)</div>
                            <div class="requirement-status <?php echo $requirements['php_version'] ? 'passed' : 'failed'; ?>">
                                <?php echo PHP_VERSION; ?> <?php echo $requirements['php_version'] ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>'; ?>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <div>MySQLi Extension</div>
                            <div class="requirement-status <?php echo $requirements['mysqli'] ? 'passed' : 'failed'; ?>">
                                <?php echo $requirements['mysqli'] ? 'Tersedia <i class="fas fa-check"></i>' : 'Tidak Tersedia <i class="fas fa-times"></i>'; ?>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <div>PDO Extension</div>
                            <div class="requirement-status <?php echo $requirements['pdo'] ? 'passed' : 'failed'; ?>">
                                <?php echo $requirements['pdo'] ? 'Tersedia <i class="fas fa-check"></i>' : 'Tidak Tersedia <i class="fas fa-times"></i>'; ?>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <div>GD Extension</div>
                            <div class="requirement-status <?php echo $requirements['gd'] ? 'passed' : 'failed'; ?>">
                                <?php echo $requirements['gd'] ? 'Tersedia <i class="fas fa-check"></i>' : 'Tidak Tersedia <i class="fas fa-times"></i>'; ?>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <div>Uploads Directory Writable</div>
                            <div class="requirement-status <?php echo $requirements['uploads_writable'] ? 'passed' : 'failed'; ?>">
                                <?php echo $requirements['uploads_writable'] ? 'Ya <i class="fas fa-check"></i>' : 'Tidak <i class="fas fa-times"></i>'; ?>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <div>Config Directory Writable</div>
                            <div class="requirement-status <?php echo $requirements['config_writable'] ? 'passed' : 'failed'; ?>">
                                <?php echo $requirements['config_writable'] ? 'Ya <i class="fas fa-check"></i>' : 'Tidak <i class="fas fa-times"></i>'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" <?php echo !$allRequirementsMet ? 'disabled' : ''; ?>>
                            <?php echo $allRequirementsMet ? 'Lanjutkan <i class="fas fa-arrow-right"></i>' : 'Persyaratan Belum Terpenuhi'; ?>
                        </button>
                    </div>
                </form>
                
                <?php if (!$allRequirementsMet): ?>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i> Silakan penuhi semua persyaratan sistem sebelum melanjutkan instalasi.
                    </div>
                <?php endif; ?>
            <?php elseif ($step === 2): ?>
                <!-- Step 2: Konfigurasi database -->
                <h3 class="mb-4">Konfigurasi Database</h3>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Database Host</label>
                        <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="db_user" class="form-label">Database Username</label>
                        <input type="text" class="form-control" id="db_user" name="db_user" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="db_pass" class="form-label">Database Password</label>
                        <input type="password" class="form-control" id="db_pass" name="db_pass">
                    </div>
                    
                    <div class="mb-3">
                        <label for="db_name" class="form-label">Database Name</label>
                        <input type="text" class="form-control" id="db_name" name="db_name" value="puskesmas_db" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Lanjutkan <i class="fas fa-arrow-right"></i></button>
                    </div>
                </form>
            <?php elseif ($step === 3): ?>
                <!-- Step 3: Konfirmasi overwrite database -->
                <h3 class="mb-4">Konfirmasi Database</h3>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Database <strong><?php echo htmlspecialchars($dbConfig['name']); ?></strong> sudah ada. Apakah Anda ingin menimpa database yang sudah ada? Semua data yang ada akan dihapus.
                </div>
                
                <form method="POST" action="">
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="confirm" value="no" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Tidak, Kembali
                        </button>
                        
                        <button type="submit" name="confirm" value="yes" class="btn btn-danger">
                            <i class="fas fa-check"></i> Ya, Timpa Database
                        </button>
                    </div>
                </form>
            <?php elseif ($step === 4): ?>
                <!-- Step 4: Import database -->
                <h3 class="mb-4">Instalasi Database</h3>
                
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin"></i> Sedang menginstall database. Mohon tunggu...
                </div>
                
                <form method="POST" action="" id="installForm">
                    <input type="hidden" name="install" value="1">
                </form>
                
                <script>
                    // Submit form otomatis
                    document.getElementById('installForm').submit();
                </script>
            <?php elseif ($step === 5): ?>
                <!-- Step 5: Selesai -->
                <h3 class="mb-4">Instalasi Selesai</h3>
                
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Instalasi website Puskesmas Kepulauan Seribu Selatan berhasil!
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Informasi Login Admin</h5>
                        <div class="requirement-item">
                            <div>Username</div>
                            <div><strong>admin</strong></div>
                        </div>
                        <div class="requirement-item">
                            <div>Password</div>
                            <div><strong>password</strong></div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Segera ubah password default setelah login pertama kali!
                </div>
                
                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Buka Website
                    </a>
                    
                    <a href="admin/login.php" class="btn btn-success">
                        <i class="fas fa-user-shield"></i> Login Admin
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>