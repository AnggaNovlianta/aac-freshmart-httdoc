<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan: Hanya admin yang boleh melakukan aksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

// =========================================================================
// ========================== FUNGSI HELPER ============================
// =========================================================================

// Fungsi untuk membuat awalan SKU dari nama kategori
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

// Fungsi untuk memproses (resize & compress) gambar yang diupload
function process_and_save_image($file_tmp_name, $output_dir, $new_filename_base, $max_width = 800) {
    if (!file_exists($file_tmp_name) || !is_readable($file_tmp_name)) return null;
    $image_info = getimagesize($file_tmp_name);
    if (!$image_info) return null;
    list($width, $height, $type) = $image_info;

    $source_image = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $source_image = imagecreatefromjpeg($file_tmp_name); break;
        case IMAGETYPE_PNG: $source_image = imagecreatefrompng($file_tmp_name); break;
        case IMAGETYPE_WEBP: $source_image = imagecreatefromwebp($file_tmp_name); break;
        default: return null;
    }
    if ($source_image === false) return null;

    $ratio = $width / $height;
    if ($width > $max_width) { $new_width = $max_width; $new_height = $max_width / $ratio; } 
    else { $new_width = $width; $new_height = $height; }

    $resized_image = imagecreatetruecolor($new_width, $new_height);
    if ($type == IMAGETYPE_PNG) { imagealphablending($resized_image, false); imagesavealpha($resized_image, true); }
    imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    $output_filename = $new_filename_base . '.webp';
    $output_path = $output_dir . $output_filename;
    imagewebp($resized_image, $output_path, 75);

    imagedestroy($source_image);
    imagedestroy($resized_image);
    return $output_filename;
}

// =========================================================================
// ========================== LOGIKA UTAMA ===============================
// =========================================================================

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
            $new_filename_base = 'product_' . $new_product_id . '_' . time();
            $image_path = process_and_save_image($_FILES['image']['tmp_name'], $upload_dir, $new_filename_base);
        }

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

    if ($id === 0) {
        header("Location: {$redirect_url}?status=error&message=ID Produk tidak valid.");
        exit();
    }

    $sql_parts = []; $params = []; $types = '';
    array_push($sql_parts, "name=?", "category_id=?", "supplier_id=?", "description=?", "cost_price=?", "selling_price=?", "stock=?", "low_stock_threshold=?", "weight_kg=?", "is_active=?", "is_featured=?");
    array_push($params, $name, $category_id, $supplier_id, $description, $cost_price, $selling_price, $stock, $low_stock_threshold, $weight_kg, $is_active, $is_featured);
    $types .= "siisddiidii";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        $upload_dir = '../assets/images/products/';
        $stmt_old = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
        $stmt_old->bind_param("i", $id); $stmt_old->execute();
        $old_image = $stmt_old->get_result()->fetch_assoc()['image_path'] ?? null; $stmt_old->close();
        
        $new_filename_base = 'product_' . $id . '_' . time();
        $new_filename = process_and_save_image($_FILES['image']['tmp_name'], $upload_dir, $new_filename_base);

        if ($new_filename) {
            if ($old_image && file_exists($upload_dir . $old_image)) { unlink($upload_dir . $old_image); }
            $sql_parts[] = "image_path=?";
            $params[] = $new_filename;
            $types .= "s";
        }
    }
    $params[] = $id; $types .= "i";
    
    $sql = "UPDATE products SET " . implode(", ", $sql_parts) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
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
    $id = $_POST['id'] ?? 0;
    if ($id === 0) { die("ID tidak valid."); }

    $stmt_img = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt_img->bind_param("i", $id); $stmt_img->execute();
    $image_to_delete = $stmt_img->get_result()->fetch_assoc()['image_path'] ?? null;
    $stmt_img->close();

    $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete->bind_param("i", $id);
    
    if ($stmt_delete->execute()) {
        if ($image_to_delete && file_exists('../assets/images/products/' . $image_to_delete)) {
            unlink('../assets/images/products/' . $image_to_delete);
        }
        header("Location: {$redirect_url}?status=success&message=Produk berhasil dihapus.");
    } else {
        header("Location: {$redirect_url}?status=error&message=Gagal menghapus produk.");
    }
    $stmt_delete->close();
    exit();
}

$conn->close();
header("Location: {$redirect_url}");
exit();
?>