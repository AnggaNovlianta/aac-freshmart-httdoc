<?php
session_start();
// BENAR: Muat koneksi dan konfigurasi untuk menggunakan BASE_URL
require_once '_includes/db_connection.php';

// Cek apakah pengguna sudah login dan memiliki role 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    // BENAR: Menggunakan BASE_URL untuk pengalihan yang andal
    header("Location: " . BASE_URL . "login.php?error=Akses ditolak!");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - AAC Freshmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Ini adalah halaman dashboard Anda.</p>
        <p>Di sini Anda bisa melihat daftar produk dan melakukan pemesanan.</p>
        <!-- BENAR: Path dan nama file logout diperbaiki -->
        <a href="_process/process_logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>