<?php
session_start();
require_once '_includes/db_connection.php'; 
require_once '_includes/auth_check.php';

// Memeriksa izin menggunakan sistem terpusat
enforce_permission($conn);
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