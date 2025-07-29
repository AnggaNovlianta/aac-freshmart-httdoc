<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan: Hanya admin yang boleh melakukan aksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$action = $_POST['action'] ?? '';
$redirect_url = BASE_URL . "admin/management_user.php";

// --- LOGIKA TAMBAH PENGGUNA BARU ---
if ($action == 'add') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($role)) {
        header("Location: {$redirect_url}?status=error&message=Semua field wajib diisi.");
        exit();
    }

    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: {$redirect_url}?status=error&message=Username sudah digunakan.");
        exit();
    }
    $stmt_check->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama_lengkap, $username, $hashed_password, $role);

    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Pengguna baru berhasil ditambahkan.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Terjadi kesalahan saat menyimpan data.");
    }
    $stmt->close();
    exit();
}

// --- LOGIKA EDIT PENGGUNA ---
if ($action == 'edit') {
    $id = $_POST['id'] ?? 0;
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Bisa kosong
    $role = $_POST['role'];

    if ($id === 0 || empty($nama_lengkap) || empty($username) || empty($role)) {
        header("Location: {$redirect_url}?status=error&message=Data tidak valid.");
        exit();
    }

    // Cek duplikasi username, KECUALI untuk user itu sendiri
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt_check->bind_param("si", $username, $id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: {$redirect_url}?status=error&message=Username sudah digunakan oleh pengguna lain.");
        exit();
    }
    $stmt_check->close();

    if (!empty($password)) {
        // --- JIKA PASSWORD DIISI (ingin diubah) ---
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, username=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama_lengkap, $username, $hashed_password, $role, $id);
    } else {
        // --- JIKA PASSWORD KOSONG (tidak ingin diubah) ---
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, username=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $nama_lengkap, $username, $role, $id);
    }

    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Data pengguna berhasil diperbarui.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal memperbarui data.");
    }
    $stmt->close();
    exit();
}

// --- LOGIKA HAPUS PENGGUNA ---
if ($action == 'delete') {
    $id = $_POST['id'] ?? 0;
    if ($id === 0) { die("ID tidak valid."); }
    
    // Jangan biarkan admin menghapus dirinya sendiri
    if ($id == $_SESSION['user_id']) {
        header("Location: {$redirect_url}?status=error&message=Anda tidak bisa menghapus akun Anda sendiri.");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Pengguna berhasil dihapus.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal menghapus pengguna.");
    }
    $stmt->close();
    exit();
}

$conn->close();
header("Location: {$redirect_url}");
exit();