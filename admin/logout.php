<?php
/**
 * Halaman Logout
 */

// Mulai session
session_start();

// Include file auth.php
require_once 'auth.php';

// Proses logout
logout();

// Set flash message
setFlashMessage('success', 'Anda berhasil logout');

// Redirect ke halaman login
header('Location: login.php');
exit;
?>