<?php
/**
 * Halaman Lupa Password
 */

// Mulai session
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Include file auth.php
require_once 'auth.php';

// Inisialisasi variabel
$error = '';
$success = '';

// Proses form lupa password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    // Validasi input
    if (empty($email)) {
        $error = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Proses reset password
        if (resetPassword($email)) {
            $success = 'Instruksi reset password telah dikirim ke email Anda';
        } else {
            $error = 'Email tidak terdaftar dalam sistem';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Puskesmas Kepulauan Seribu Selatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/images/puskesmas-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .forgot-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 400px;
            max-width: 100%;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .forgot-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .forgot-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .forgot-header p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .forgot-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .form-group .icon {
            position: absolute;
            top: 40px;
            right: 15px;
            color: var(--secondary-color);
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #0b5ed7;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .success-message {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .back-to-login a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
        }
        
        .instructions {
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--secondary-color);
            line-height: 1.5;
        }
        
        @media (max-width: 480px) {
            .forgot-container {
                width: 90%;
            }
            
            .forgot-header h1 {
                font-size: 20px;
            }
            
            .forgot-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h1>Lupa Password</h1>
            <p>Puskesmas Kepulauan Seribu Selatan</p>
        </div>
        
        <div class="forgot-form">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="instructions">
                Masukkan alamat email yang terdaftar. Kami akan mengirimkan instruksi untuk mengatur ulang password Anda.
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email" required>
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                </div>
                
                <button type="submit" class="btn-submit">Kirim Instruksi Reset</button>
            </form>
            
            <div class="back-to-login">
                <a href="login.php"><i class="fas fa-arrow-left"></i> Kembali ke halaman login</a>
            </div>
        </div>
    </div>
</body>
</html>