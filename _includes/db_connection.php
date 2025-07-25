<?php
// ✅ LANGKAH KRUSIAL: Muat file konfigurasi yang mendefinisikan BASE_URL
require_once 'config.php';

// --- Kode koneksi database Anda yang sudah ada ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_aac_freshmart';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>