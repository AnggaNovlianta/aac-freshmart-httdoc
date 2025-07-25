<?php
// Pengaturan untuk koneksi database
$host = "localhost";
$user = "root"; // Username default XAMPP
$pass = "";     // Password default XAMPP
$db   = "db_aac_freshmart";

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Tidak dapat terhubung ke database: " . mysqli_connect_error());
}
?>