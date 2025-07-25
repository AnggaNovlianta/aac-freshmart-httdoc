<?php
// Ganti 'password123' dengan password yang Anda inginkan
$passwordAsli = 'password123';

// Menghasilkan hash yang aman
$hashPassword = password_hash($passwordAsli, PASSWORD_DEFAULT);

echo "Password Asli: " . $passwordAsli . "<br>";
echo "Hasil Hash: " . $hashPassword;

// Anda bisa menggunakan hash ini untuk dimasukkan ke database.
?>