<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan: Hanya admin yang boleh melakukan aksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$action = $_POST['action'] ?? '';

// --- FUNGSI TAMBAH SLIDE ---
if ($action == 'add') {
    $title = $_POST['title'] ?? '';
    $caption = $_POST['caption'] ?? '';
    $order_number = $_POST['order_number'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validasi dasar di sisi server
    if (empty($title) || !isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        header("Location: " . BASE_URL . "admin/settings_management.php?status=error&message=Judul dan Gambar wajib diisi.");
        exit();
    }

    // --- Penanganan Upload Gambar ---
    $upload_dir = '../assets/images/carousel/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $file_type = $_FILES['image']['type'];

    if (in_array($file_type, $allowed_types)) {
        // Buat nama file unik untuk keamanan
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'slide_' . uniqid('', true) . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Simpan ke database menggunakan prepared statement
            $stmt = $conn->prepare("INSERT INTO carousel_slides (title, caption, image_path, order_number, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $title, $caption, $new_filename, $order_number, $is_active);
            
            if ($stmt->execute()) {
                header("Location: " . BASE_URL . "admin/settings_management.php?status=success&message=Slide berhasil ditambahkan.");
            } else {
                header("Location: " . BASE_URL . "admin/settings_management.php?status=error&message=Gagal menyimpan ke database.");
            }
            $stmt->close();
        } else {
            header("Location: " . BASE_URL . "admin/settings_management.php?status=error&message=Gagal memindahkan file.");
        }
    } else {
        header("Location: " . BASE_URL . "admin/settings_management.php?status=error&message=Tipe file tidak valid. Hanya JPG/PNG/WEBP.");
    }
    exit();
}

// --- FUNGSI HAPUS SLIDE ---
if ($action == 'delete') {
    $id = $_POST['id'] ?? 0;
    if ($id === 0) { die("ID tidak valid."); }

    // Ambil nama file gambar dari DB sebelum dihapus
    $stmt = $conn->prepare("SELECT image_path FROM carousel_slides WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image_path_to_delete = null;
    if($row = $result->fetch_assoc()) {
        $image_path_to_delete = '../assets/images/carousel/' . $row['image_path'];
    }
    $stmt->close();

    // Hapus record dari database
    $stmt_delete = $conn->prepare("DELETE FROM carousel_slides WHERE id = ?");
    $stmt_delete->bind_param("i", $id);
    if ($stmt_delete->execute()) {
        // Jika berhasil, hapus file gambar fisiknya
        if ($image_path_to_delete && file_exists($image_path_to_delete)) {
            unlink($image_path_to_delete);
        }
        header("Location: " . BASE_URL . "admin/settings_management.php?status=success&message=Slide berhasil dihapus.");
    } else {
        header("Location: " . BASE_URL . "admin/settings_management.php?status=error&message=Gagal menghapus slide.");
    }
    $stmt_delete->close();
    exit();
}

$conn->close();
header("Location: " . BASE_URL . "admin/settings_management.php");
exit();
?>