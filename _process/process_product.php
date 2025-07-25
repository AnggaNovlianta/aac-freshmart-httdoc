<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan: Hanya admin yang boleh melakukan aksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$action = $_POST['action'] ?? '';

// --- LOGIKA TAMBAH PRODUK BARU ---
if ($action == 'add') {
    // Ambil data dari form
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validasi dasar
    if (empty($name) || $price <= 0 || !isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        header("Location: " . BASE_URL . "admin/product_management.php?status=error&message=Nama, Harga, dan Gambar wajib diisi.");
        exit();
    }
    
    // Penanganan Upload Gambar
    $image_path = null;
    $upload_dir = '../assets/images/products/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $file_type = $_FILES['image']['type'];

    if (in_array($file_type, $allowed_types)) {
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = $new_filename;
        } else {
            header("Location: " . BASE_URL . "admin/product_management.php?status=error&message=Gagal mengupload gambar.");
            exit();
        }
    } else {
        header("Location: " . BASE_URL . "admin/product_management.php?status=error&message=Tipe file gambar tidak valid.");
        exit();
    }

    // Simpan data ke database
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image_path, $is_active);
    
    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "admin/product_management.php?status=success&message=Produk baru berhasil ditambahkan.");
    } else {
        // Untuk debugging jika ada error SQL:
        // die("Error: " . $stmt->error); 
        header("Location: " . BASE_URL . "admin/product_management.php?status=error&message=Gagal menyimpan produk ke database.");
    }
    $stmt->close();
    exit();
}

// --- LOGIKA EDIT PRODUK ---
if ($action == 'edit') {
    // ... (kode edit Anda yang sudah ada, tetap sama) ...
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    // ... dst ...
}

// --- LOGIKA HAPUS PRODUK ---
if ($action == 'delete') {
    // ... (kode hapus Anda yang sudah ada, tetap sama) ...
    $id = $_POST['id'] ?? 0;
    // ... dst ...
}

// Jika tidak ada aksi yang cocok, arahkan kembali
$conn->close();
header("Location: " . BASE_URL . "admin/product_management.php");
exit();
?>