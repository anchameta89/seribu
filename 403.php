<?php
// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include file konfigurasi
require_once 'config/config.php';

// Set header 403
header("HTTP/1.0 403 Forbidden");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | Puskesmas Kepulauan Seribu Selatan</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-container {
            text-align: center;
            padding: 100px 0;
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 0;
            line-height: 1;
        }
        
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
            color: #333;
        }
        
        .error-description {
            max-width: 600px;
            margin: 0 auto 30px;
            color: #666;
        }
        
        .error-image {
            max-width: 300px;
            margin-bottom: 30px;
        }
        
        .btn-home {
            padding: 10px 30px;
            font-size: 16px;
            border-radius: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="error-container">
            <img src="<?php echo BASE_URL; ?>/assets/images/403.svg" alt="403 Error" class="error-image">
            <h1 class="error-code">403</h1>
            <h2 class="error-message">Akses Ditolak</h2>
            <p class="error-description">
                Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Halaman ini mungkin dibatasi atau memerlukan hak akses khusus.
            </p>
            <div class="error-actions">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-home">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="<?php echo BASE_URL; ?>/assets/js/jquery.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>