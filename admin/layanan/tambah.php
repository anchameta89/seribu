<?php
/**
 * Halaman Tambah Layanan
 */

// Mulai session
session_start();

// Include file auth.php dan config.php
require_once '../auth.php';
require_once '../../config/config.php';

// Cek login
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Cek session expired
if (isSessionExpired()) {
    logout();
    setFlashMessage('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
    header('Location: ../login.php');
    exit;
}

// Refresh login time
refreshLoginTime();

// Ambil data user yang sedang login
$currentUser = getCurrentUser();

// Koneksi ke database
$conn = getConnection();

// Inisialisasi variabel
$error = '';
$judul = '';
$slug = '';
$deskripsi_singkat = '';
$deskripsi = '';
$urutan = 0;
$status = 'aktif';
$icon = '';

// Ambil urutan terakhir
$lastOrder = querySingle($conn, "SELECT MAX(urutan) as max_urutan FROM layanan");
$urutan = ($lastOrder && $lastOrder['max_urutan']) ? $lastOrder['max_urutan'] + 1 : 1;

// Proses form tambah layanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $judul = $_POST['judul'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $deskripsi_singkat = $_POST['deskripsi_singkat'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $urutan = isset($_POST['urutan']) ? (int)$_POST['urutan'] : 0;
    $status = $_POST['status'] ?? 'aktif';
    $icon = $_POST['icon'] ?? '';
    
    // Validasi input
    if (empty($judul)) {
        $error = 'Judul layanan harus diisi';
    } elseif (empty($deskripsi_singkat)) {
        $error = 'Deskripsi singkat harus diisi';
    } elseif (empty($deskripsi)) {
        $error = 'Deskripsi lengkap harus diisi';
    } else {
        // Generate slug jika kosong
        if (empty($slug)) {
            $slug = createSlug($judul);
        } else {
            $slug = createSlug($slug);
        }
        
        // Cek apakah slug sudah ada
        $cekSlug = querySingle($conn, "SELECT id FROM layanan WHERE slug = '$slug'");
        
        if ($cekSlug) {
            $error = 'Slug sudah digunakan, silakan gunakan slug lain';
        } else {
            // Upload gambar
            $gambar = '';
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
                $uploadDir = ROOT_PATH . '/uploads/layanan/';
                
                // Buat direktori jika belum ada
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $uploadResult = uploadFile($_FILES['gambar'], $uploadDir, ['jpg', 'jpeg', 'png', 'gif']);
                
                if ($uploadResult['status'] == 'success') {
                    $gambar = $uploadResult['filename'];
                } else {
                    $error = $uploadResult['message'];
                }
            }
            
            if (empty($error)) {
                // Simpan data layanan
                $data = [
                    'judul' => $judul,
                    'slug' => $slug,
                    'deskripsi_singkat' => $deskripsi_singkat,
                    'deskripsi' => $deskripsi,
                    'gambar' => $gambar,
                    'icon' => $icon,
                    'urutan' => $urutan,
                    'status' => $status,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if (insert($conn, 'layanan', $data)) {
                    // Log aktivitas
                    $userId = $currentUser['id'];
                    $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'create', 'Menambahkan layanan baru: $judul')");
                    
                    setFlashMessage('success', 'Layanan berhasil ditambahkan');
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Gagal menyimpan data layanan';
                }
            }
        }
    }
}

// Tutup koneksi
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Layanan - Puskesmas Kepulauan Seribu Selatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#deskripsi',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 400
        });
    </script>
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
                        <a href="../index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="../berita/index.php">
                            <i class="fas fa-newspaper"></i>
                            <span>Berita</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="index.php">
                            <i class="fas fa-hand-holding-medical"></i>
                            <span>Layanan</span>
                        </a>
                    </li>
                    <li>
                        <a href="../jadwal/index.php">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Jadwal</span>
                        </a>
                    </li>
                    <li>
                        <a href="../pustu/index.php">
                            <i class="fas fa-hospital"></i>
                            <span>Pustu</span>
                        </a>
                    </li>
                    <li>
                        <a href="../laporan/index.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Laporan</span>
                        </a>
                    </li>
                    <li>
                        <a href="../halaman/index.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Halaman</span>
                        </a>
                    </li>
                    <li>
                        <a href="../slider/index.php">
                            <i class="fas fa-images"></i>
                            <span>Slider</span>
                        </a>
                    </li>
                    <li>
                        <a href="../users/index.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="../pengaturan/index.php">
                            <i class="fas fa-cog"></i>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../logout.php" class="logout-btn">
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
                            <img src="../assets/images/user-avatar.png" alt="User Avatar" class="user-avatar">
                            <span class="user-name"><?php echo $currentUser['nama_lengkap']; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="user-dropdown-menu">
                            <a href="../profile.php">
                                <i class="fas fa-user"></i>
                                <span>Profil</span>
                            </a>
                            <a href="../change-password.php">
                                <i class="fas fa-key"></i>
                                <span>Ubah Password</span>
                            </a>
                            <a href="../logout.php">
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
                    <h1>Tambah Layanan</h1>
                    <nav class="breadcrumb">
                        <a href="../index.php">Home</a> / 
                        <a href="index.php">Layanan</a> / 
                        <span>Tambah</span>
                    </nav>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Form Tambah Layanan</h3>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="judul">Judul Layanan <span class="text-danger">*</span></label>
                                        <input type="text" id="judul" name="judul" class="form-control" value="<?php echo htmlspecialchars($judul); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="slug">Slug (URL)</label>
                                        <input type="text" id="slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($slug); ?>">
                                        <small class="form-text text-muted">Biarkan kosong untuk generate otomatis dari judul</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="deskripsi_singkat">Deskripsi Singkat <span class="text-danger">*</span></label>
                                        <textarea id="deskripsi_singkat" name="deskripsi_singkat" class="form-control" rows="3" required><?php echo htmlspecialchars($deskripsi_singkat); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="deskripsi">Deskripsi Lengkap <span class="text-danger">*</span></label>
                                        <textarea id="deskripsi" name="deskripsi" class="form-control"><?php echo htmlspecialchars($deskripsi); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gambar">Gambar Layanan</label>
                                        <input type="file" id="gambar" name="gambar" class="form-control-file" accept="image/*" onchange="previewImage(this)">
                                        <div class="mt-2">
                                            <img id="gambar-preview" src="../assets/images/no-image.png" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="icon">Icon (Font Awesome)</label>
                                        <input type="text" id="icon" name="icon" class="form-control" value="<?php echo htmlspecialchars($icon); ?>" placeholder="fas fa-stethoscope">
                                        <small class="form-text text-muted">Contoh: fas fa-stethoscope, fas fa-heartbeat</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="urutan">Urutan</label>
                                        <input type="number" id="urutan" name="urutan" class="form-control" value="<?php echo $urutan; ?>" min="1">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="aktif" <?php echo ($status == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="nonaktif" <?php echo ($status == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <a href="index.php" class="btn btn-light">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Layanan</button>
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
        
        // Preview image before upload
        function previewImage(input) {
            const preview = document.getElementById('gambar-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '../assets/images/no-image.png';
            }
        }
        
        // Auto generate slug from title
        document.getElementById('judul').addEventListener('keyup', function() {
            const judul = this.value;
            const slug = document.getElementById('slug');
            if (!slug.value) {
                slug.value = judul.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });
        
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>