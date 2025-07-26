<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan: Hanya admin yang boleh melakukan aksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

// Fungsi helper untuk membuat awalan SKU
function create_sku_prefix($category_id, $conn) {
    if (empty($category_id)) return 'GEN';
    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $category_name = $stmt->get_result()->fetch_assoc()['name'] ?? 'GENERIC';
    $stmt->close();
    
    $words = explode(' ', strtoupper($category_name));
    $prefix = '';
    foreach ($words as $word) { $prefix .= substr($word, 0, 1); }
    return substr(preg_replace("/[^A-Z0-9]/", "", $prefix), 0, 3);
}

$action = $_POST['action'] ?? '';
$redirect_url = BASE_URL . "admin/product_management.php";

// --- LOGIKA TAMBAH PRODUK BARU ---
if ($action == 'add') {
    $name = $_POST['name'] ?? '';
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $description = $_POST['description'] ?? '';
    $cost_price = $_POST['cost_price'] ?? 0;
    $selling_price = $_POST['selling_price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $low_stock_threshold = $_POST['low_stock_threshold'] ?? 10;
    $weight_kg = $_POST['weight_kg'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (empty($name) || $selling_price <= 0 || empty($category_id)) {
        header("Location: {$redirect_url}?status=error&message=Nama, Kategori, dan Harga Jual wajib diisi.");
        exit();
    }

    // INSERT produk dengan SKU dan image_path diisi NULL untuk sementara
    $stmt = $conn->prepare("INSERT INTO products (name, category_id, supplier_id, description, cost_price, selling_price, stock, low_stock_threshold, weight_kg, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisddiidii", $name, $category_id, $supplier_id, $description, $cost_price, $selling_price, $stock, $low_stock_threshold, $weight_kg, $is_active, $is_featured);
    
    if ($stmt->execute()) {
        $new_product_id = $stmt->insert_id;
        $sku_prefix = create_sku_prefix($category_id, $conn);
        $date_part = date('Ymd');
        $new_sku = "{$sku_prefix}-{$date_part}-{$new_product_id}";
        
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
            $upload_dir = '../assets/images/products/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'product_' . $new_product_id . '_' . time() . '.' . $file_extension;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                $image_path = $new_filename;
            }
        }

        // UPDATE baris produk tadi dengan SKU dan nama gambar
        $stmt_update = $conn->prepare("UPDATE products SET sku = ?, image_path = ? WHERE id = ?");
        $stmt_update->bind_param("ssi", $new_sku, $image_path, $new_product_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        header("Location: {$redirect_url}?status=success&message=Produk baru berhasil ditambahkan.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal menyimpan produk.");
    }
    $stmt->close();
    exit();
}

// --- LOGIKA EDIT PRODUK ---
if ($action == 'edit') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $description = $_POST['description'] ?? '';
    $cost_price = $_POST['cost_price'] ?? 0;
    $selling_price = $_POST['selling_price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $low_stock_threshold = $_POST['low_stock_threshold'] ?? 10;
    $weight_kg = $_POST['weight_kg'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if ($id === 0) { /* handle error */ }

    $image_sql_part = "";
    $params = [$name, $category_id, $supplier_id, $description, $cost_price, $selling_price, $stock, $low_stock_threshold, $weight_kg, $is_active, $is_featured];
    $types = "siisddiidii";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        $upload_dir = '../assets/images/products/';
        $stmt_old = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
        $stmt_old->bind_param("i", $id); $stmt_old->execute();
        $old_image = $stmt_old->get_result()->fetch_assoc()['image_path'] ?? null; $stmt_old->close();
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'product_' . $id . '_' . time() . '.' . $file_extension;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
            if ($old_image && file_exists($upload_dir . $old_image)) { unlink($upload_dir . $old_image); }
            $image_sql_part = ", image_path = ?";
            $params[] = $new_filename;
            $types .= "s";
        }
    }
    $params[] = $id; $types .= "i";
    
    $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, supplier_id=?, description=?, cost_price=?, selling_price=?, stock=?, low_stock_threshold=?, weight_kg=?, is_active=?, is_featured=? {$image_sql_part} WHERE id=?");
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        header("Location: {$redirect_url}?status=success&message=Produk berhasil diperbarui.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal memperbarui produk.");
    }
    $stmt->close();
    exit();
}

// --- LOGIKA HAPUS PRODUK ---
if ($action == 'delete') {
    // ... (kode hapus Anda yang sudah benar, tidak perlu diubah) ...
}

$conn->close();
header("Location: {$redirect_url}");
exit();
?>