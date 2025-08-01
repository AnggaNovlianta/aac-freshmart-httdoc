<?php
// ✅ LANGKAH KRUSIAL: Muat file konfigurasi yang mendefinisikan BASE_URL
require_once 'config.php';

// --- Kode koneksi database Anda yang sudah ada ---
$db_host = 'localhost'; // Tetap 'localhost'
$db_user = 'u123456789_user_anda'; // Ganti dengan MySQL User dari Hostinger
$db_pass = 'PasswordDatabaseAnda'; // Ganti dengan password yang Anda buat di Hostinger
$db_name = 'u123456789_db_anda'; // Ganti dengan Nama Database dari Hostinger

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>