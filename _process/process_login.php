<?php
session_start();
// ✅ PASTIKAN PATH INI BENAR: Harus naik satu level
require '../_includes/db_connection.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ... (kode prepared statement Anda) ...
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Rekomendasi keamanan
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Arahkan berdasarkan role MENGGUNAKAN BASE_URL
            if ($user['role'] == 'admin') {
                // ✅ PASTIKAN INI BENAR: Menggunakan BASE_URL dan nama file Anda
                header("Location: " . BASE_URL . "admin/admin_dashboard.php");
            } else {
                header("Location: " . BASE_URL . "user_dashboard.php");
            }
            exit();
        }
    }

    // ✅ PASTIKAN PENGALIHAN GAGAL JUGA MENGGUNAKAN BASE_URL
    header("Location: " . BASE_URL . "login.php?error=Username atau password salah");
    exit();

} else {
    // ✅ PASTIKAN PENGALIHAN AKSES LANGSUNG JUGA MENGGUNAKAN BASE_URL
    header("Location: " . BASE_URL . "login.php");
    exit();
}
?>