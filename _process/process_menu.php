<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan: Hanya admin yang boleh melakukan aksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$action = $_POST['action'] ?? '';
$redirect_url = BASE_URL . "admin/menu_management.php";

// --- LOGIKA TAMBAH MENU BARU ---
if ($action == 'add') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon_class = $_POST['icon_class'] ?? 'fa-solid fa-circle-question';
    $url = $_POST['url'] ?? '';
    $order_number = $_POST['order_number'] ?? 100;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title) || empty($url)) {
        header("Location: {$redirect_url}?status=error&message=Judul dan URL wajib diisi.");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO admin_menus (title, description, icon_class, url, order_number, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $title, $description, $icon_class, $url, $order_number, $is_active);

    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Menu baru berhasil ditambahkan.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal menyimpan menu.");
    }
    $stmt->close();
    exit();
}

// --- LOGIKA EDIT MENU ---
if ($action == 'edit') {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon_class = $_POST['icon_class'] ?? 'fa-solid fa-circle-question';
    $url = $_POST['url'] ?? '';
    $order_number = $_POST['order_number'] ?? 100;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($id === 0 || empty($title) || empty($url)) {
        header("Location: {$redirect_url}?status=error&message=Data tidak valid.");
        exit();
    }

    $stmt = $conn->prepare("UPDATE admin_menus SET title=?, description=?, icon_class=?, url=?, order_number=?, is_active=? WHERE id=?");
    $stmt->bind_param("ssssiii", $title, $description, $icon_class, $url, $order_number, $is_active, $id);

    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Menu berhasil diperbarui.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal memperbarui menu.");
    }
    $stmt->close();
    exit();
}

// --- LOGIKA HAPUS MENU ---
if ($action == 'delete') {
    $id = $_POST['id'] ?? 0;
    if ($id === 0) { die("ID tidak valid."); }

    $stmt = $conn->prepare("DELETE FROM admin_menus WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Menu berhasil dihapus.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal menghapus menu.");
    }
    $stmt->close();
    exit();
}

$conn->close();
header("Location: {$redirect_url}");
exit();
?>