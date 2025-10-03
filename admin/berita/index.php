<?php
/**
 * Halaman Manajemen Berita
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

// Hapus berita jika ada request hapus
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Ambil data berita untuk mendapatkan gambar
    $berita = querySingle($conn, "SELECT * FROM berita WHERE id = $id");
    
    if ($berita) {
        // Hapus gambar jika ada
        if (!empty($berita['gambar'])) {
            $gambarPath = ROOT_PATH . '/uploads/' . $berita['gambar'];
            if (file_exists($gambarPath)) {
                unlink($gambarPath);
            }
        }
        
        // Hapus berita dari database
        if (delete($conn, 'berita', $id)) {
            // Log aktivitas
            $userId = $currentUser['id'];
            $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'delete', 'Menghapus berita: {$berita['judul']}')");
            
            setFlashMessage('success', 'Berita berhasil dihapus');
        } else {
            setFlashMessage('error', 'Gagal menghapus berita');
        }
    } else {
        setFlashMessage('error', 'Berita tidak ditemukan');
    }
    
    // Redirect ke halaman berita
    header('Location: index.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

// Query untuk menghitung total berita
$whereClause = "WHERE 1=1";
if (!empty($search)) {
    $search = escapeString($conn, $search);
    $whereClause .= " AND (judul LIKE '%$search%' OR isi LIKE '%$search%')";
}
if ($kategori > 0) {
    $whereClause .= " AND kategori_id = $kategori";
}

$totalBerita = querySingle($conn, "SELECT COUNT(*) as total FROM berita $whereClause");
$totalBerita = $totalBerita['total'];

// Query untuk mengambil data berita
$query = "SELECT b.*, k.nama as kategori_nama, u.nama_lengkap as penulis 
          FROM berita b 
          LEFT JOIN kategori_berita k ON b.kategori_id = k.id 
          LEFT JOIN users u ON b.user_id = u.id 
          $whereClause 
          ORDER BY b.created_at DESC 
          LIMIT $offset, $limit";
$beritaList = $conn->query($query);

// Ambil data kategori untuk filter
$kategoriList = $conn->query("SELECT * FROM kategori_berita ORDER BY nama ASC");

// Pagination links
$totalPages = ceil($totalBerita / $limit);
$paginationLinks = getPagination($page, $totalPages);

// Tutup koneksi
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Berita - Puskesmas Kepulauan Seribu Selatan</title>
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
                    <h1>Manajemen Berita</h1>
                    <nav class="breadcrumb">
                        <a href="../index.php">Home</a> / 
                        <span>Berita</span>
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
                
                <div class="content-actions">
                    <a href="tambah.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Berita
                    </a>
                    <a href="kategori.php" class="btn btn-secondary">
                        <i class="fas fa-tags"></i> Kelola Kategori
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Daftar Berita</h3>
                        
                        <form action="" method="GET" class="search-form">
                            <div class="form-group">
                                <select name="kategori" class="form-control">
                                    <option value="0">Semua Kategori</option>
                                    <?php while ($kategori = $kategoriList->fetch_assoc()): ?>
                                        <option value="<?php echo $kategori['id']; ?>" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == $kategori['id']) ? 'selected' : ''; ?>>
                                            <?php echo $kategori['nama']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <div class="search-input">
                                    <input type="text" name="search" placeholder="Cari berita..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" class="form-control">
                                    <button type="submit" class="search-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Gambar</th>
                                        <th width="25%">Judul</th>
                                        <th width="15%">Kategori</th>
                                        <th width="15%">Penulis</th>
                                        <th width="10%">Tanggal</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($beritaList->num_rows > 0) {
                                        $no = $offset + 1;
                                        while ($berita = $beritaList->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <?php if (!empty($berita['gambar'])): ?>
                                                <img src="../../uploads/<?php echo $berita['gambar']; ?>" alt="<?php echo $berita['judul']; ?>" class="table-img">
                                            <?php else: ?>
                                                <img src="../assets/images/no-image.jpg" alt="No Image" class="table-img">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="title-cell">
                                                <strong><?php echo $berita['judul']; ?></strong>
                                                <small><?php echo truncateText(strip_tags($berita['isi']), 100); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo $berita['kategori_nama']; ?></td>
                                        <td><?php echo $berita['penulis']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($berita['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="../../berita-detail.php?id=<?php echo $berita['id']; ?>" class="btn btn-sm btn-info" target="_blank" title="Lihat">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $berita['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $berita['id']; ?>, '<?php echo addslashes($berita['judul']); ?>')" class="btn btn-sm btn-danger" title="Hapus">
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
                                        <td colspan="7" class="text-center">Tidak ada data berita</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($totalBerita > 0): ?>
                            <div class="pagination-info">
                                Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $limit, $totalBerita); ?> dari <?php echo $totalBerita; ?> data
                            </div>
                            
                            <div class="pagination">
                                <?php echo $paginationLinks; ?>
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
                    <p>Apakah Anda yakin ingin menghapus berita <strong id="deleteItemName"></strong>?</p>
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