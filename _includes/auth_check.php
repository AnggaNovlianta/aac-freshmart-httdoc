<?php
/**
 * Memeriksa izin akses pengguna untuk halaman saat ini berdasarkan database.
 * Fungsi ini akan menghentikan eksekusi dan mengalihkan pengguna jika tidak berizin.
 *
 * @param mysqli $conn Objek koneksi database yang sudah ada.
 */
function enforce_permission(mysqli $conn) {
    // Ambil nama file halaman saat ini
    $current_page_key = basename($_SERVER['PHP_SELF']);

    $stmt = $conn->prepare("SELECT required_role FROM page_permissions WHERE page_key = ?");
    $stmt->bind_param("s", $current_page_key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Keamanan default: Jika halaman tidak terdaftar, hanya admin yang boleh akses.
    if ($result->num_rows === 0) {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . BASE_URL . "login.php?error=Halaman tidak terdaftar.");
            exit();
        }
        return;
    }

    $permission = $result->fetch_assoc();
    $required_role = $permission['required_role'];
    $stmt->close();

    // Jika halaman publik, semua orang boleh akses.
    if ($required_role === 'public') {
        return;
    }

    // Jika halaman butuh login tapi pengguna belum login.
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php?error=Anda harus login untuk mengakses halaman ini.");
        exit();
    }
    
    $user_role = $_SESSION['role'];

    // Aturan utama:
    // 1. Jika role yang dibutuhkan 'admin', hanya admin yang bisa.
    // 2. Jika role yang dibutuhkan 'user', maka 'admin' (sebagai superuser) dan 'user' bisa.
    if ($required_role === 'admin' && $user_role !== 'admin') {
        header("Location: " . BASE_URL . "login.php?error=Akses ditolak. Halaman ini hanya untuk admin.");
        exit();
    }

    if ($required_role === 'user' && !in_array($user_role, ['user', 'admin'])) {
        header("Location: " . BASE_URL . "login.php?error=Akses ditolak.");
        exit();
    }
}
