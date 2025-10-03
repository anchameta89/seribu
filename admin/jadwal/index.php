<?php
/**
 * Halaman Manajemen Jadwal Pelayanan
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
$hari = '';
$jam_buka = '';
$jam_tutup = '';
$keterangan = '';
$status = 'aktif';
$editId = 0;

// Proses hapus jadwal
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Ambil data jadwal untuk log
    $jadwal = querySingle($conn, "SELECT hari FROM jadwal_pelayanan WHERE id = $id");
    
    if ($jadwal) {
        // Hapus jadwal
        if (delete($conn, 'jadwal_pelayanan', $id)) {
            // Log aktivitas
            $userId = $currentUser['id'];
            $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'delete', 'Menghapus jadwal pelayanan hari: {$jadwal['hari']}')");
            
            setFlashMessage('success', 'Jadwal pelayanan berhasil dihapus');
        } else {
            setFlashMessage('error', 'Gagal menghapus jadwal pelayanan');
        }
    } else {
        setFlashMessage('error', 'Jadwal pelayanan tidak ditemukan');
    }
    
    // Redirect ke halaman jadwal
    header('Location: index.php');
    exit;
}

// Proses edit jadwal
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $jadwal = querySingle($conn, "SELECT * FROM jadwal_pelayanan WHERE id = $editId");
    
    if ($jadwal) {
        $hari = $jadwal['hari'];
        $jam_buka = $jadwal['jam_buka'];
        $jam_tutup = $jadwal['jam_tutup'];
        $keterangan = $jadwal['keterangan'];
        $status = $jadwal['status'];
    } else {
        setFlashMessage('error', 'Jadwal pelayanan tidak ditemukan');
        header('Location: index.php');
        exit;
    }
}

// Proses form tambah/edit jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $hari = $_POST['hari'] ?? '';
    $jam_buka = $_POST['jam_buka'] ?? '';
    $jam_tutup = $_POST['jam_tutup'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // Validasi input
    if (empty($hari)) {
        $error = 'Hari harus diisi';
    } elseif (empty($jam_buka)) {
        $error = 'Jam buka harus diisi';
    } elseif (empty($jam_tutup)) {
        $error = 'Jam tutup harus diisi';
    } else {
        // Cek apakah hari sudah ada (kecuali untuk jadwal ini sendiri jika edit)
        $cekHari = querySingle($conn, "SELECT id FROM jadwal_pelayanan WHERE hari = '$hari' AND id != $editId");
        
        if ($cekHari) {
            $error = 'Jadwal untuk hari ini sudah ada';
        } else {
            $data = [
                'hari' => $hari,
                'jam_buka' => $jam_buka,
                'jam_tutup' => $jam_tutup,
                'keterangan' => $keterangan,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $currentUser['id'];
            
            if ($editId > 0) {
                // Update jadwal
                if (update($conn, 'jadwal_pelayanan', $editId, $data)) {
                    // Log aktivitas
                    $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'update', 'Memperbarui jadwal pelayanan hari: $hari')");
                    
                    $success = 'Jadwal pelayanan berhasil diperbarui';
                    $hari = $jam_buka = $jam_tutup = $keterangan = '';
                    $status = 'aktif';
                    $editId = 0;
                } else {
                    $error = 'Gagal memperbarui jadwal pelayanan';
                }
            } else {
                // Tambah data created_at untuk insert
                $data['created_at'] = date('Y-m-d H:i:s');
                
                // Tambah jadwal baru
                if (insert($conn, 'jadwal_pelayanan', $data)) {
                    // Log aktivitas
                    $conn->query("INSERT INTO log_aktivitas (user_id, tipe, deskripsi) VALUES ($userId, 'create', 'Menambahkan jadwal pelayanan baru hari: $hari')");
                    
                    $success = 'Jadwal pelayanan berhasil ditambahkan';
                    $hari = $jam_buka = $jam_tutup = $keterangan = '';
                    $status = 'aktif';
                } else {
                    $error = 'Gagal menambahkan jadwal pelayanan';
                }
            }
        }
    }
}

// Ambil data jadwal
$jadwalList = $conn->query("SELECT * FROM jadwal_pelayanan ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");

// Tutup koneksi
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jadwal Pelayanan - Puskesmas Kepulauan Seribu Selatan</title>
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
                    <li>
                        <a href="../layanan/index.php">
                            <i class="fas fa-hand-holding-medical"></i>
                            <span>Layanan</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="index.php">
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
                    <h1>Manajemen Jadwal Pelayanan</h1>
                    <nav class="breadcrumb">
                        <a href="../index.php">Home</a> / 
                        <span>Jadwal</span>
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
                                <h3><?php echo ($editId > 0) ? 'Edit Jadwal Pelayanan' : 'Tambah Jadwal Pelayanan'; ?></h3>
                            </div>
                            
                            <div class="card-body">
                                <form method="POST" action="">
                                    <?php if ($editId > 0): ?>
                                        <input type="hidden" name="edit_id" value="<?php echo $editId; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <label for="hari">Hari <span class="text-danger">*</span></label>
                                        <select id="hari" name="hari" class="form-control" required>
                                            <option value="">Pilih Hari</option>
                                            <option value="Senin" <?php echo ($hari == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                                            <option value="Selasa" <?php echo ($hari == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                                            <option value="Rabu" <?php echo ($hari == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                                            <option value="Kamis" <?php echo ($hari == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                                            <option value="Jumat" <?php echo ($hari == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                                            <option value="Sabtu" <?php echo ($hari == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                                            <option value="Minggu" <?php echo ($hari == 'Minggu') ? 'selected' : ''; ?>>Minggu</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="jam_buka">Jam Buka <span class="text-danger">*</span></label>
                                        <input type="time" id="jam_buka" name="jam_buka" class="form-control" value="<?php echo htmlspecialchars($jam_buka); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="jam_tutup">Jam Tutup <span class="text-danger">*</span></label>
                                        <input type="time" id="jam_tutup" name="jam_tutup" class="form-control" value="<?php echo htmlspecialchars($jam_tutup); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="keterangan">Keterangan</label>
                                        <textarea id="keterangan" name="keterangan" class="form-control" rows="3"><?php echo htmlspecialchars($keterangan); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="aktif" <?php echo ($status == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="nonaktif" <?php echo ($status == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <?php if ($editId > 0): ?>
                                            <a href="index.php" class="btn btn-light">Batal</a>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-primary">
                                            <?php echo ($editId > 0) ? 'Update Jadwal' : 'Simpan Jadwal'; ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3>Daftar Jadwal Pelayanan</h3>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Hari</th>
                                                <th width="15%">Jam Buka</th>
                                                <th width="15%">Jam Tutup</th>
                                                <th width="30%">Keterangan</th>
                                                <th width="10%">Status</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if ($jadwalList->num_rows > 0) {
                                                $no = 1;
                                                while ($jadwal = $jadwalList->fetch_assoc()) {
                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $jadwal['hari']; ?></td>
                                                <td><?php echo date('H:i', strtotime($jadwal['jam_buka'])); ?></td>
                                                <td><?php echo date('H:i', strtotime($jadwal['jam_tutup'])); ?></td>
                                                <td><?php echo !empty($jadwal['keterangan']) ? $jadwal['keterangan'] : '-'; ?></td>
                                                <td>
                                                    <span class="badge <?php echo ($jadwal['status'] == 'aktif') ? 'badge-success' : 'badge-secondary'; ?>">
                                                        <?php echo ucfirst($jadwal['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="index.php?action=edit&id=<?php echo $jadwal['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $jadwal['id']; ?>, '<?php echo $jadwal['hari']; ?>')" class="btn btn-sm btn-danger" title="Hapus">
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
                                                <td colspan="7" class="text-center">Tidak ada data jadwal pelayanan</td>
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
                    <p>Apakah Anda yakin ingin menghapus jadwal pelayanan hari <strong id="deleteItemName"></strong>?</p>
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