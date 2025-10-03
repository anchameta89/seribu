<?php
/**
 * Halaman Manajemen Kategori Berita
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

// Ambil flash message
$successMessage = getFlashMessage('success');
$errorMessage = getFlashMessage('error');

// Inisialisasi variabel
$error = '';
$success = '';
$nama = '';
$deskripsi = '';
$editId = 0;

// Proses hapus kategori
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Cek apakah kategori digunakan oleh berita
    $cekBerita = querySingle($conn, "SELECT COUNT(*) as total FROM berita WHERE kategori_id = $id");
    
    if ($cekBerita['total'] > 0) {
        setFlashMessage('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh berita');
    } else {
        // Ambil data kategori untuk log
        $kategori = querySingle($conn, "SELECT nama FROM kategori_berita WHERE id = $id");
        
        // Hapus kategori
        if (delete($conn, 'kategori_berita', $id)) {
            // Log aktivitas
            $userId = $currentUser['id'];
            $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'delete', 'Menghapus kategori berita: {$kategori['nama']}')");
            
            setFlashMessage('success', 'Kategori berhasil dihapus');
        } else {
            setFlashMessage('error', 'Gagal menghapus kategori');
        }
    }
    
    // Redirect ke halaman kategori
    header('Location: kategori.php');
    exit;
}

// Proses edit kategori
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $kategori = querySingle($conn, "SELECT * FROM kategori_berita WHERE id = $editId");
    
    if ($kategori) {
        $nama = $kategori['nama'];
        $deskripsi = $kategori['deskripsi'];
    } else {
        setFlashMessage('error', 'Kategori tidak ditemukan');
        header('Location: kategori.php');
        exit;
    }
}

// Proses form tambah/edit kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama = $_POST['nama'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // Validasi input
    if (empty($nama)) {
        $error = 'Nama kategori harus diisi';
    } else {
        // Cek apakah nama kategori sudah ada
        $slug = createSlug($nama);
        $cekNama = querySingle($conn, "SELECT id FROM kategori_berita WHERE slug = '$slug' AND id != $editId");
        
        if ($cekNama) {
            $error = 'Nama kategori sudah digunakan';
        } else {
            $data = [
                'nama' => $nama,
                'slug' => $slug,
                'deskripsi' => $deskripsi,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $currentUser['id'];
            
            if ($editId > 0) {
                // Update kategori
                if (update($conn, 'kategori_berita', $editId, $data)) {
                    // Log aktivitas
                    $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'update', 'Memperbarui kategori berita: $nama')");
                    
                    $success = 'Kategori berhasil diperbarui';
                    $nama = $deskripsi = '';
                    $editId = 0;
                } else {
                    $error = 'Gagal memperbarui kategori';
                }
            } else {
                // Tambah data created_at untuk insert
                $data['created_at'] = date('Y-m-d H:i:s');
                
                // Tambah kategori baru
                if (insert($conn, 'kategori_berita', $data)) {
                    // Log aktivitas
                    $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'create', 'Menambahkan kategori berita baru: $nama')");
                    
                    $success = 'Kategori berhasil ditambahkan';
                    $nama = $deskripsi = '';
                } else {
                    $error = 'Gagal menambahkan kategori';
                }
            }
        }
    }
}

// Ambil data kategori
$kategoriList = $conn->query("SELECT k.*, COUNT(b.id) as jumlah_berita 
                             FROM kategori_berita k 
                             LEFT JOIN berita b ON k.id = b.kategori_id 
                             GROUP BY k.id 
                             ORDER BY k.nama ASC");

// Tutup koneksi
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori Berita - Puskesmas Kepulauan Seribu Selatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                    <h1>Manajemen Kategori Berita</h1>
                    <nav class="breadcrumb">
                        <a href="../index.php">Home</a> / 
                        <a href="index.php">Berita</a> / 
                        <span>Kategori</span>
                    </nav>
                </div>
                
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3><?php echo ($editId > 0) ? 'Edit Kategori' : 'Tambah Kategori'; ?></h3>
                            </div>
                            
                            <div class="card-body">
                                <form method="POST" action="">
                                    <?php if ($editId > 0): ?>
                                        <input type="hidden" name="edit_id" value="<?php echo $editId; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <label for="nama">Nama Kategori <span class="text-danger">*</span></label>
                                        <input type="text" id="nama" name="nama" class="form-control" value="<?php echo htmlspecialchars($nama); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="deskripsi">Deskripsi</label>
                                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($deskripsi); ?></textarea>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <?php if ($editId > 0): ?>
                                            <a href="kategori.php" class="btn btn-light">Batal</a>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-primary">
                                            <?php echo ($editId > 0) ? 'Update Kategori' : 'Simpan Kategori'; ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3>Daftar Kategori</h3>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="25%">Nama Kategori</th>
                                                <th width="40%">Deskripsi</th>
                                                <th width="15%">Jumlah Berita</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if ($kategoriList->num_rows > 0) {
                                                $no = 1;
                                                while ($kategori = $kategoriList->fetch_assoc()) {
                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $kategori['nama']; ?></td>
                                                <td><?php echo !empty($kategori['deskripsi']) ? $kategori['deskripsi'] : '-'; ?></td>
                                                <td><?php echo $kategori['jumlah_berita']; ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="kategori.php?action=edit&id=<?php echo $kategori['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($kategori['jumlah_berita'] == 0): ?>
                                                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $kategori['id']; ?>, '<?php echo addslashes($kategori['nama']); ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-secondary" title="Tidak dapat dihapus" disabled>
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data kategori</td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal Konfirmasi Hapus -->
    <div class="modal" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Konfirmasi Hapus</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus kategori <strong id="deleteItemName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <a href="#" id="deleteLink" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
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
        
        // Modal functions
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        // Close modal when clicking on close button or outside
        document.querySelectorAll('[data-dismiss="modal"]').forEach(function(element) {
            element.addEventListener('click', function() {
                const modal = this.closest('.modal');
                hideModal(modal.id);
            });
        });
        
        // Confirm delete
        function confirmDelete(id, name) {
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteLink').href = 'kategori.php?action=delete&id=' + id;
            showModal('deleteModal');
        }
        
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