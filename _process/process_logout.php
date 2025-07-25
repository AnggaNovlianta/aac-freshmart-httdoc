<?php
session_start();

// Muat konfigurasi untuk mendapatkan BASE_URL
require_once '../_includes/db_connection.php';

// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Arahkan kembali ke halaman login menggunakan BASE_URL
header("Location: " . BASE_URL . "index.php");
exit();
?>