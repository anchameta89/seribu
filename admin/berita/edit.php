<?php
/**
 * Halaman Edit Berita
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

// Cek ID berita
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('error', 'ID berita tidak valid');
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Ambil data berita
$berita = querySingle($conn, "SELECT * FROM berita WHERE id = $id");

if (!$berita) {
    setFlashMessage('error', 'Berita tidak ditemukan');
    header('Location: index.php');
    exit;
}

// Inisialisasi variabel
$error = '';
$success = '';
$judul = $berita['judul'];
$isi = $berita['isi'];
$kategori_id = $berita['kategori_id'];
$tags = $berita['tags'];
$status = $berita['status'];
$gambar_lama = $berita['gambar'];

// Ambil data kategori
$kategoriList = $conn->query("SELECT * FROM kategori_berita ORDER BY nama ASC");

// Proses form edit berita
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $judul = $_POST['judul'] ?? '';
    $slug = createSlug($judul);
    $isi = $_POST['isi'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $user_id = $currentUser['id'];
    
    // Validasi input
    if (empty($judul)) {
        $error = 'Judul berita harus diisi';
    } elseif (empty($isi)) {
        $error = 'Isi berita harus diisi';
    } elseif (empty($kategori_id)) {
        $error = 'Kategori berita harus dipilih';
    } else {
        // Upload gambar jika ada
        $gambar = $gambar_lama;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $uploadResult = uploadFile($_FILES['gambar'], 'berita');
            
            if ($uploadResult['status']) {
                $gambar = $uploadResult['path'];
                
                // Hapus gambar lama jika ada
                if (!empty($gambar_lama)) {
                    $gambarPath = ROOT_PATH . '/uploads/' . $gambar_lama;
                    if (file_exists($gambarPath)) {
                        unlink($gambarPath);
                    }
                }
            } else {
                $error = $uploadResult['message'];
            }
        }
        
        if (empty($error)) {
            // Update berita di database
            $data = [
                'judul' => $judul,
                'slug' => $slug,
                'isi' => $isi,
                'gambar' => $gambar,
                'kategori_id' => $kategori_id,
                'tags' => $tags,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (update($conn, 'berita', $id, $data)) {
                // Log aktivitas
                $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($user_id, 'update', 'Memperbarui berita: $judul')");
                
                setFlashMessage('success', 'Berita berhasil diperbarui');
                header('Location: index.php');
                exit;
            } else {
                $error = 'Gagal memperbarui berita';
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
    <title>Edit Berita - Puskesmas Kepulauan Seribu Selatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
                    <li class="active">
                        <a href="index.php">
                            <i class="fas fa-newspaper"></i>
                            <span>Berita</span>
                        </a>
                    </li>
                    <li>
                        <a href="../layanan/index.php">
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
                    <h1>Edit Berita</h1>
                    <nav class="breadcrumb">
                        <a href="../index.php">Home</a> / 
                        <a href="index.php">Berita</a> / 
                        <span>Edit</span>
                    </nav>
                </div>
                
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
                
                <div class="card">
                    <div class="card-header">
                        <h3>Form Edit Berita</h3>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="judul">Judul Berita <span class="text-danger">*</span></label>
                                        <input type="text" id="judul" name="judul" class="form-control" value="<?php echo htmlspecialchars($judul); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="isi">Isi Berita <span class="text-danger">*</span></label>
                                        <textarea id="isi" name="isi" class="form-control editor"><?php echo htmlspecialchars($isi); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kategori_id">Kategori <span class="text-danger">*</span></label>
                                        <select id="kategori_id" name="kategori_id" class="form-control" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php while ($kategori = $kategoriList->fetch_assoc()): ?>
                                                <option value="<?php echo $kategori['id']; ?>" <?php echo ($kategori_id == $kategori['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $kategori['nama']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="gambar">Gambar Berita</label>
                                        <?php if (!empty($gambar_lama)): ?>
                                            <div class="current-image mb-2">
                                                <img src="../../uploads/<?php echo $gambar_lama; ?>" alt="Gambar Berita" class="img-fluid">
                                                <div class="form-text">Gambar saat ini</div>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                                        <div class="form-text">Format: JPG, PNG, GIF. Maks: 2MB</div>
                                        <div class="image-preview mt-2" id="imagePreview"></div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="tags">Tags</label>
                                        <input type="text" id="tags" name="tags" class="form-control" value="<?php echo htmlspecialchars($tags); ?>">
                                        <div class="form-text">Pisahkan dengan koma (,)</div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="published" <?php echo ($status == 'published') ? 'selected' : ''; ?>>Published</option>
                                            <option value="draft" <?php echo ($status == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        </select>
                                    </div>
                                </div>
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
        
        // Initialize TinyMCE
        tinymce.init({
            selector: '.editor',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 400,
            images_upload_url: '../../config/upload_tinymce.php',
            images_upload_base_path: '../../uploads/',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true
        });
        
        // Image preview
        document.getElementById('gambar').addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
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