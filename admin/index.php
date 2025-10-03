<?php
/**
 * Dashboard Admin
 */

// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once 'auth.php';

// Cek login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Cek session expired
if (isSessionExpired()) {
    logout();
    setFlashMessage('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
    redirect('login.php');
}

// Refresh login time
refreshLoginTime();

// Ambil data user yang sedang login
$currentUser = getCurrentUser();

// Ambil statistik untuk dashboard
$totalBerita = count_rows('berita');
$totalLayanan = count_rows('layanan');
$totalPustu = count_rows('pustu');
$totalUsers = count_rows('users');

// Ambil berita terbaru
$conn = getConnection();
$result = $conn->query("SELECT * FROM berita ORDER BY tanggal_publikasi DESC LIMIT 5");
$beritaTerbaru = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $beritaTerbaru[] = $row;
    }
}
closeConnection($conn);

// Ambil log aktivitas terbaru
$conn = getConnection();
$result = $conn->query("SELECT l.*, u.username FROM log_aktivitas l JOIN users u ON l.user_id = u.id ORDER BY l.waktu DESC LIMIT 10");
$logAktivitas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $logAktivitas[] = $row;
    }
}
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Puskesmas Kepulauan Seribu Selatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li class="active">
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
                    <h1>Dashboard</h1>
                    <nav class="breadcrumb">
                        <a href="index.php">Home</a> / 
                        <span>Dashboard</span>
                    </nav>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div class="stat-card-info">
                                <h3>Berita</h3>
                                <p class="stat-number"><?php echo $totalBerita; ?></p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                        </div>
                        <a href="berita/index.php" class="stat-card-link">Lihat Detail <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div class="stat-card-info">
                                <h3>Layanan</h3>
                                <p class="stat-number"><?php echo $totalLayanan; ?></p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-hand-holding-medical"></i>
                            </div>
                        </div>
                        <a href="layanan/index.php" class="stat-card-link">Lihat Detail <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div class="stat-card-info">
                                <h3>Pustu</h3>
                                <p class="stat-number"><?php echo $totalPustu; ?></p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-hospital"></i>
                            </div>
                        </div>
                        <a href="pustu/index.php" class="stat-card-link">Lihat Detail <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div class="stat-card-info">
                                <h3>Users</h3>
                                <p class="stat-number"><?php echo $totalUsers; ?></p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <a href="users/index.php" class="stat-card-link">Lihat Detail <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="dashboard-charts">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Statistik Kunjungan</h3>
                            <div class="chart-actions">
                                <select id="chart-period">
                                    <option value="week">Minggu Ini</option>
                                    <option value="month" selected>Bulan Ini</option>
                                    <option value="year">Tahun Ini</option>
                                </select>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="visitChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Distribusi Layanan</h3>
                            <div class="chart-actions">
                                <button class="chart-action-btn active" data-chart="pie">Pie</button>
                                <button class="chart-action-btn" data-chart="doughnut">Doughnut</button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="serviceChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Content -->
                <div class="recent-content">
                    <div class="recent-card">
                        <div class="recent-header">
                            <h3>Berita Terbaru</h3>
                            <a href="berita/index.php" class="view-all">Lihat Semua</a>
                        </div>
                        <div class="recent-body">
                            <table class="recent-table">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Kategori</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($beritaTerbaru)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Belum ada berita</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($beritaTerbaru as $berita): ?>
                                            <tr>
                                                <td>
                                                    <a href="berita/edit.php?id=<?php echo $berita['id']; ?>">
                                                        <?php echo truncateText($berita['judul'], 40); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo $berita['kategori_id']; ?></td>
                                                <td><?php echo formatDate($berita['tanggal_publikasi']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $berita['status'] === 'published' ? 'status-success' : 'status-warning'; ?>">
                                                        <?php echo $berita['status'] === 'published' ? 'Dipublikasi' : 'Draft'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="recent-card">
                        <div class="recent-header">
                            <h3>Log Aktivitas</h3>
                        </div>
                        <div class="recent-body">
                            <div class="activity-log">
                                <?php if (empty($logAktivitas)): ?>
                                    <div class="activity-item">
                                        <div class="activity-content">
                                            <p>Belum ada aktivitas</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($logAktivitas as $log): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <?php 
                                                $icon = 'fa-info-circle';
                                                $color = 'activity-info';
                                                
                                                switch ($log['tipe']) {
                                                    case 'login':
                                                        $icon = 'fa-sign-in-alt';
                                                        $color = 'activity-success';
                                                        break;
                                                    case 'logout':
                                                        $icon = 'fa-sign-out-alt';
                                                        $color = 'activity-warning';
                                                        break;
                                                    case 'create':
                                                        $icon = 'fa-plus-circle';
                                                        $color = 'activity-success';
                                                        break;
                                                    case 'update':
                                                        $icon = 'fa-edit';
                                                        $color = 'activity-warning';
                                                        break;
                                                    case 'delete':
                                                        $icon = 'fa-trash-alt';
                                                        $color = 'activity-danger';
                                                        break;
                                                }
                                                ?>
                                                <i class="fas <?php echo $icon; ?> <?php echo $color; ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p><?php echo $log['deskripsi']; ?></p>
                                                <div class="activity-meta">
                                                    <span class="activity-user"><?php echo $log['username']; ?></span>
                                                    <span class="activity-time"><?php echo formatDate($log['waktu'], true); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
        
        // Charts
        const visitCtx = document.getElementById('visitChart').getContext('2d');
        const visitChart = new Chart(visitCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Kunjungan',
                    data: [65, 59, 80, 81, 56, 55, 40, 45, 60, 70, 75, 80],
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        const serviceCtx = document.getElementById('serviceChart').getContext('2d');
        const serviceChart = new Chart(serviceCtx, {
            type: 'pie',
            data: {
                labels: ['Poli Umum', 'Poli Gigi', 'KIA/KB', 'Laboratorium', 'Farmasi'],
                datasets: [{
                    data: [30, 20, 25, 15, 10],
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(25, 135, 84, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: [
                        'rgba(13, 110, 253, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(25, 135, 84, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // Chart type toggle
        document.querySelectorAll('.chart-action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const chartType = this.dataset.chart;
                document.querySelectorAll('.chart-action-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                serviceChart.config.type = chartType;
                serviceChart.update();
            });
        });
        
        // Chart period change
        document.getElementById('chart-period').addEventListener('change', function() {
            const period = this.value;
            let data = [];
            let labels = [];
            
            switch (period) {
                case 'week':
                    labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    data = [30, 40, 35, 50, 45, 30, 25];
                    break;
                case 'month':
                    labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    data = [65, 59, 80, 81, 56, 55, 40, 45, 60, 70, 75, 80];
                    break;
                case 'year':
                    labels = ['2018', '2019', '2020', '2021', '2022', '2023'];
                    data = [400, 450, 380, 500, 550, 600];
                    break;
            }
            
            visitChart.data.labels = labels;
            visitChart.data.datasets[0].data = data;
            visitChart.update();
        });
    </script>
</body>
</html>