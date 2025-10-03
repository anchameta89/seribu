<?php
/**
 * Halaman Manajemen Layanan
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

// Proses hapus layanan
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Ambil data layanan untuk log dan hapus gambar
    $layanan = querySingle($conn, "SELECT judul, gambar FROM layanan WHERE id = $id");
    
    if ($layanan) {
        // Hapus gambar jika ada
        if (!empty($layanan['gambar'])) {
            $gambarPath = ROOT_PATH . '/uploads/layanan/' . $layanan['gambar'];
            if (file_exists($gambarPath)) {
                unlink($gambarPath);
            }
        }
        
        // Hapus layanan
        if (delete($conn, 'layanan', $id)) {
            // Log aktivitas
            $userId = $currentUser['id'];
            $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'delete', 'Menghapus layanan: {$layanan['judul']}')");
            
            setFlashMessage('success', 'Layanan berhasil dihapus');
        } else {
            setFlashMessage('error', 'Gagal menghapus layanan');
        }
    } else {
        setFlashMessage('error', 'Layanan tidak ditemukan');
    }
    
    // Redirect ke halaman layanan
    header('Location: index.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$whereClause = '';

if (!empty($search)) {
    $searchEscaped = escapeString($conn, $search);
    $whereClause = "WHERE judul LIKE '%$searchEscaped%' OR deskripsi_singkat LIKE '%$searchEscaped%'";
}

// Hitung total data
$totalData = querySingle($conn, "SELECT COUNT(*) as total FROM layanan $whereClause");
$totalRows = $totalData['total'];
$totalPages = ceil($totalRows / $limit);

// Ambil data layanan
$layananList = $conn->query("SELECT * FROM layanan $whereClause ORDER BY urutan ASC, created_at DESC LIMIT $offset, $limit");

// Tutup koneksi
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Layanan - Puskesmas Kepulauan Seribu Selatan</title>
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
                    <h1>Manajemen Layanan</h1>
                    <nav class="breadcrumb">
                        <a href="../index.php">Home</a> / 
                        <span>Layanan</span>
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
                
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h3>Daftar Layanan</h3>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="tambah.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Layanan
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form action="" method="GET" class="search-form">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Cari layanan..." value="<?php echo htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Gambar</th>
                                        <th width="20%">Judul</th>
                                        <th width="30%">Deskripsi Singkat</th>
                                        <th width="10%">Urutan</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($layananList->num_rows > 0) {
                                        $no = $offset + 1;
                                        while ($layanan = $layananList->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <?php if (!empty($layanan['gambar'])): ?>
                                                <img src="../../uploads/layanan/<?php echo $layanan['gambar']; ?>" alt="<?php echo $layanan['judul']; ?>" class="img-thumbnail" style="max-width: 80px;">
                                            <?php else: ?>
                                                <img src="../assets/images/no-image.png" alt="No Image" class="img-thumbnail" style="max-width: 80px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $layanan['judul']; ?></td>
                                        <td><?php echo truncateText($layanan['deskripsi_singkat'], 100); ?></td>
                                        <td><?php echo $layanan['urutan']; ?></td>
                                        <td>
                                            <span class="badge <?php echo ($layanan['status'] == 'aktif') ? 'badge-success' : 'badge-secondary'; ?>">
                                                <?php echo ucfirst($layanan['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit.php?id=<?php echo $layanan['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $layanan['id']; ?>, '<?php echo addslashes($layanan['judul']); ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data layanan</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($totalPages > 1): ?>
                            <div class="pagination">
                                <?php echo getPagination($page, $totalPages, 'index.php', ['search' => $search]); ?>
                            </div>
                        <?php endif; ?>
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
                    <p>Apakah Anda yakin ingin menghapus layanan <strong id="deleteItemName"></strong>?</p>
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
            document.getElementById('deleteLink').href = 'index.php?action=delete&id=' + id;
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