<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan: Hanya admin yang boleh melakukan aksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$action = $_POST['action'] ?? '';
$redirect_url = BASE_URL . "admin/permissions_management.php";

// --- LOGIKA TAMBAH HALAMAN BARU ---
if ($action == 'add') {
    $page_name = $_POST['page_name'] ?? '';
    $page_key = $_POST['page_key'] ?? '';
    $description = $_POST['description'] ?? '';
    $required_role = $_POST['required_role'] ?? 'admin';

    if (empty($page_name) || empty($page_key)) {
        header("Location: {$redirect_url}?status=error&message=Nama Halaman dan Kunci Halaman wajib diisi.");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO page_permissions (page_name, page_key, description, required_role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $page_name, $page_key, $description, $required_role);

    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Izin halaman baru berhasil ditambahkan.");
    } else {
        // Cek jika error karena duplikat kunci
        if ($conn->errno == 1062) {
            header("Location: {$redirect_url}?status=error&message=Gagal: Kunci Halaman (nama file) '{$page_key}' sudah ada.");
        } else {
            header("Location: {$redirect_url}?status=error&message=Gagal menyimpan data.");
        }
    }
    $stmt->close();
    exit();
}

// --- LOGIKA EDIT HALAMAN ---
if ($action == 'edit') {
    $id = $_POST['id'] ?? 0;
    $page_name = $_POST['page_name'] ?? '';
    $page_key = $_POST['page_key'] ?? '';
    $description = $_POST['description'] ?? '';
    $required_role = $_POST['required_role'] ?? 'admin';

    if ($id === 0 || empty($page_name) || empty($page_key)) {
        header("Location: {$redirect_url}?status=error&message=Data tidak valid.");
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE page_permissions SET page_name=?, page_key=?, description=?, required_role=? WHERE id=?");
    $stmt->bind_param("ssssi", $page_name, $page_key, $description, $required_role, $id);

    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Izin halaman berhasil diperbarui.");
    } else {
        if ($conn->errno == 1062) {
            header("Location: {$redirect_url}?status=error&message=Gagal: Kunci Halaman '{$page_key}' sudah digunakan oleh data lain.");
        } else {
            header("Location: {$redirect_url}?status=error&message=Gagal memperbarui data.");
        }
    }
    $stmt->close();
    exit();
}

// --- LOGIKA HAPUS HALAMAN ---
if ($action == 'delete') {
    $id = $_POST['id'] ?? 0;
    if ($id === 0) { die("ID tidak valid."); }

    $stmt = $conn->prepare("DELETE FROM page_permissions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Izin halaman berhasil dihapus.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal menghapus data.");
    }
    $stmt->close();
    exit();
}

// --- LOGIKA LAMA (BULK UPDATE) TETAP ADA DI SINI JIKA DIPERLUKAN ---
// Namun, dengan fungsionalitas edit, fitur bulk update menjadi kurang relevan.
// Kita bisa menghapusnya atau menyimpannya. Untuk sekarang, kita anggap tidak ada aksi lain.

$conn->close();
header("Location: {$redirect_url}?status=error&message=Aksi tidak dikenal.");
exit();