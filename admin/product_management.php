<?php
session_start();
// 1. Definisikan variabel judul terlebih dahulu
$page_title = "Manajemen Produk"; 
// 2. Hubungkan ke DB
require '../_includes/db_connection.php';

// --- 3. JALANKAN SEMUA LOGIKA & PENGAMBILAN DATA DI SINI ---
// Ambil data untuk filter (categories & suppliers)
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$suppliers_result = $conn->query("SELECT * FROM suppliers ORDER BY name ASC");

// Statistik
$total_products = $conn->query("SELECT COUNT(id) FROM products")->fetch_row()[0];
$stock_value_result = $conn->query("SELECT SUM(stock * cost_price) FROM products")->fetch_row()[0];
$low_stock_count = $conn->query("SELECT COUNT(id) FROM products WHERE stock <= low_stock_threshold AND is_active = 1")->fetch_row()[0];
$featured_count = $conn->query("SELECT COUNT(id) FROM products WHERE is_featured = 1")->fetch_row()[0];

// Filter & Pencarian
$search = $_GET['search'] ?? ''; $filter_category = $_GET['filter_category'] ?? ''; $filter_supplier = $_GET['filter_supplier'] ?? ''; $filter_status = $_GET['filter_status'] ?? '';
$where_clauses = []; $params = []; $types = '';
if (!empty($search)) { $where_clauses[] = "(p.name LIKE ? OR p.sku LIKE ?)"; $search_param = "%{$search}%"; array_push($params, $search_param, $search_param); $types .= 'ss'; }
if (!empty($filter_category)) { $where_clauses[] = "p.category_id = ?"; $params[] = $filter_category; $types .= 'i'; }
if (!empty($filter_supplier)) { $where_clauses[] = "p.supplier_id = ?"; $params[] = $filter_supplier; $types .= 'i'; }
if ($filter_status === 'low_stock') { $where_clauses[] = "p.stock <= p.low_stock_threshold"; }
if ($filter_status === 'featured') { $where_clauses[] = "p.is_featured = 1"; }
$where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Pagination & Query Utama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; $limit = 10; $offset = ($page - 1) * $limit;
$total_query = "SELECT COUNT(p.id) FROM products p {$where_sql}";
$stmt_total = $conn->prepare($total_query);
if (!empty($params)) { $stmt_total->bind_param($types, ...$params); }
$stmt_total->execute(); $total_filtered_products = $stmt_total->get_result()->fetch_row()[0]; $total_pages = ceil($total_filtered_products / $limit); $stmt_total->close();

$products_query = "SELECT p.*, c.name as category_name, s.name as supplier_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN suppliers s ON p.supplier_id = s.id {$where_sql} ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt_products = $conn->prepare($products_query);
$current_params = $params; $current_types = $types; $current_params[] = $limit; $current_params[] = $offset; $current_types .= 'ii';
if (count($current_params) > 0) { $stmt_products->bind_param($current_types, ...$current_params); }
$stmt_products->execute(); $products_result = $stmt_products->get_result();
// --- AKHIR DARI SEMUA LOGIKA PENGAMBILAN DATA ---

// 4. SEKARANG BARU PANGGIL HEADER untuk mulai menggambar halaman
require '../_templates/admin_header.php';
?>

<!-- ========================================================================= -->
<!-- =================== KONTEN SPESIFIK HALAMAN DIMULAI =================== -->
<!-- ========================================================================= -->

<!-- Header dengan Judul Halaman & Tombol Aksi -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" id="addNewProductBtn">
            <i class="fa fa-plus"></i> Tambah Produk Baru
        </button>
    </div>
</div>

<!-- Notifikasi -->
<?php if(isset($_GET['status'])): ?>
<div class="alert alert-<?php echo $_GET['status'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_GET['message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Kartu Statistik -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6"><div class="card shadow-sm border-0"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="fs-4 fw-bold"><?php echo $total_products; ?></div><div class="text-muted">Total Produk</div></div><i class="fa fa-boxes-stacked fa-3x text-primary stat-card"></i></div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card shadow-sm border-0"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="fs-4 fw-bold">Rp <?php echo number_format($stock_value_result ?? 0, 0, ',', '.'); ?></div><div class="text-muted">Nilai Stok (Modal)</div></div><i class="fa fa-dollar-sign fa-3x text-success stat-card"></i></div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card shadow-sm border-0"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="fs-4 fw-bold text-danger"><?php echo $low_stock_count; ?></div><div class="text-muted">Produk Stok Rendah</div></div><i class="fa fa-exclamation-triangle fa-3x text-danger stat-card"></i></div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card shadow-sm border-0"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="fs-4 fw-bold"><?php echo $featured_count; ?></div><div class="text-muted">Produk Unggulan</div></div><i class="fa fa-star fa-3x text-warning stat-card"></i></div></div></div>
</div>

<!-- Kartu Daftar Produk -->
<div class="card shadow-sm">
    <div class="card-body border-bottom">
        <form method="GET" action="product_management.php" class="d-flex flex-wrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm flex-grow-1" placeholder="Cari nama, SKU..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="filter_category" class="form-select form-select-sm" style="width: 200px;"><option value="">Semua Kategori</option><?php if ($categories_result->num_rows > 0) { $categories_result->data_seek(0); while($cat = $categories_result->fetch_assoc()) { echo "<option value='{$cat['id']}' ".($filter_category == $cat['id'] ? 'selected' : '').">{$cat['name']}</option>"; } } ?></select>
            <select name="filter_supplier" class="form-select form-select-sm" style="width: 200px;"><option value="">Semua Supplier</option><?php if ($suppliers_result->num_rows > 0) { $suppliers_result->data_seek(0); while($sup = $suppliers_result->fetch_assoc()) { echo "<option value='{$sup['id']}' ".($filter_supplier == $sup['id'] ? 'selected' : '').">{$sup['name']}</option>"; } } ?></select>
            <select name="filter_status" class="form-select form-select-sm" style="width: 180px;"><option value="">Status Apapun</option><option value="low_stock" <?php if($filter_status == 'low_stock') echo 'selected'; ?>>Stok Rendah</option><option value="featured" <?php if($filter_status == 'featured') echo 'selected'; ?>>Unggulan</option></select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i></button>
            <a href="product_management.php" class="btn btn-light btn-sm"><i class="fa fa-times"></i></a>
        </form>
    </div>

    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Produk</th><th>SKU</th><th>Supplier</th><th>Harga Jual</th><th>Margin</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php if ($products_result && $products_result->num_rows > 0): while($p = $products_result->fetch_assoc()):
                $margin = ($p['selling_price'] > 0 && $p['cost_price'] > 0) ? (($p['selling_price'] - $p['cost_price']) / $p['selling_price']) * 100 : 0;
            ?>
            <tr>
                <td><div class="d-flex align-items-center"><img src="../assets/images/products/<?php echo htmlspecialchars($p['image_path'] ?? 'default.png'); ?>" class="product-thumbnail rounded me-3"><div><div class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></div><div class="text-muted small"><?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></div></div></div></td>
                <td><?php echo htmlspecialchars($p['sku'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($p['supplier_name'] ?? 'N/A'); ?></td>
                <td>Rp <?php echo number_format($p['selling_price'], 0, ',', '.'); ?></td>
                <td><span class="badge bg-<?php echo $margin >= 20 ? 'success' : 'warning'; ?>"><?php echo number_format($margin, 1); ?>%</span></td>
                <td><?php if($p['stock'] <= $p['low_stock_threshold']) { echo "<span class='badge bg-danger'>{$p['stock']}</span>"; } else { echo $p['stock']; } ?></td>
                <td>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" <?php echo $p['is_active'] ? 'checked' : ''; ?>></div>
                    <?php if ($p['is_featured']): ?><span class="badge bg-warning mt-1">Unggulan</span><?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-light edit-btn" data-product='<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>'><i class="fa fa-edit"></i></button>
                    <button class="btn btn-sm btn-light" onclick="confirmDelete(<?php echo $p['id']; ?>)"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="8" class="text-center p-5"><div class="fs-5">Produk tidak ditemukan.</div><div class="text-muted">Coba ubah kata kunci pencarian atau reset filter.</div></td></tr>
            <?php endif; ?>
        </tbody>
    </table></div></div>

    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <div class="text-muted small">Menampilkan <strong><?php echo $products_result->num_rows; ?></strong> dari <strong><?php echo $total_filtered_products; ?></strong> produk</div>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php if($page > 1): ?><li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&filter_category=<?php echo $filter_category; ?>&filter_supplier=<?php echo $filter_supplier; ?>&filter_status=<?php echo $filter_status; ?>">‹</a></li><?php endif; ?>
            <?php for($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?php if($i == $page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&filter_category=<?php echo $filter_category; ?>&filter_supplier=<?php echo $filter_supplier; ?>&filter_status=<?php echo $filter_status; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
            <?php if($page < $total_pages): ?><li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&filter_category=<?php echo $filter_category; ?>&filter_supplier=<?php echo $filter_supplier; ?>&filter_status=<?php echo $filter_status; ?>">›</a></li><?php endif; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah & Edit Produk (LENGKAP) -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel">Form Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="productForm" action="../_process/process_product.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" id="form-action">
        <input type="hidden" name="id" id="form-id">
        <div class="modal-body">
            <!-- Info Dasar -->
            <h6>Info Dasar</h6><hr class="mt-0">
            <div class="row">
                <div class="col-md-8 mb-3"><label for="form-name" class="form-label">Nama Produk</label><input type="text" class="form-control" id="form-name" name="name" required></div>
                <div class="col-md-4 mb-3"><label for="form-sku" class="form-label">SKU</label><input type="text" class="form-control" id="form-sku" name="sku" readonly></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="form-category" class="form-label">Kategori</label><select class="form-select" id="form-category" name="category_id" required><option value="" selected disabled>-- Pilih --</option><?php if ($categories_result->num_rows > 0) { $categories_result->data_seek(0); while($cat = $categories_result->fetch_assoc()) { echo "<option value='{$cat['id']}'>{$cat['name']}</option>"; } } ?></select></div>
                <div class="col-md-6 mb-3"><label for="form-supplier" class="form-label">Supplier</label><select class="form-select" id="form-supplier" name="supplier_id"><option value="">-- Pilih --</option><?php if ($suppliers_result->num_rows > 0) { $suppliers_result->data_seek(0); while($sup = $suppliers_result->fetch_assoc()) { echo "<option value='{$sup['id']}'>{$sup['name']}</option>"; } } ?></select></div>
            </div>
            <div class="mb-3"><label for="form-description" class="form-label">Deskripsi</label><textarea class="form-control" id="form-description" name="description" rows="3"></textarea></div>
            
            <!-- Harga & Stok -->
            <h6 class="mt-4">Harga & Stok</h6><hr class="mt-0">
            <div class="row">
                <div class="col-md-4 mb-3"><label for="form-cost-price" class="form-label">Harga Modal (Rp)</label><input type="number" class="form-control" id="form-cost-price" name="cost_price" value="0"></div>
                <div class="col-md-4 mb-3"><label for="form-selling-price" class="form-label">Harga Jual (Rp)</label><input type="number" class="form-control" id="form-selling-price" name="selling_price" value="0" required></div>
                <div class="col-md-4 mb-3"><label for="form-stock" class="form-label">Jumlah Stok</label><input type="number" class="form-control" id="form-stock" name="stock" value="0" required></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="form-low-stock" class="form-label">Ambang Stok Rendah</label><input type="number" class="form-control" id="form-low-stock" name="low_stock_threshold" value="10"></div>
                <div class="col-md-6 mb-3"><label for="form-weight" class="form-label">Berat (Kg)</label><input type="number" class="form-control" id="form-weight" name="weight_kg" step="0.01" value="0"></div>
            </div>
            
            <!-- Atribut Lain -->
            <h6 class="mt-4">Atribut Lain</h6><hr class="mt-0">
            <div class="mb-3"><label for="form-image" class="form-label">Gambar Produk</label><input type="file" class="form-control" id="form-image" name="image" accept="image/*"><small id="image-help-text" class="form-text text-muted"></small></div>
            <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="form-is-active"><label class="form-check-label" for="form-is-active">Aktif (Tampil di website)</label></div>
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_featured" value="1" id="form-is-featured"><label class="form-check-label" for="form-is-featured">Jadikan Produk Unggulan</label></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="form-submit-button">Simpan</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Form Hapus Tersembunyi (LENGKAP) -->
<form id="deleteForm" action="../_process/process_product.php" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id">
</form>

<?php
// Mulai buffer untuk menangkap output JavaScript
ob_start(); 
?>
<script>
    // Seluruh JavaScript spesifik untuk halaman ini ada di sini
    function confirmDelete(id) { if (confirm("Yakin ingin menghapus produk ini?")) { document.getElementById('delete-id').value = id; document.getElementById('deleteForm').submit(); } }

    document.addEventListener('DOMContentLoaded', function () {
        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        const modal = document.getElementById('productModal');
        const form = document.getElementById('productForm');

        // Handle tombol "Tambah Produk Baru"
        document.getElementById('addNewProductBtn').addEventListener('click', function() {
            form.reset();
            modal.querySelector('.modal-title').textContent = 'Tambah Produk Baru';
            modal.querySelector('#form-action').value = 'add';
            modal.querySelector('#form-id').value = '';
            modal.querySelector('#form-sku').value = '(Otomatis)';
            modal.querySelector('#image-help-text').textContent = '';
            modal.querySelector('#form-submit-button').textContent = 'Simpan Produk';
            productModal.show();
        });

        // Handle semua tombol "Edit"
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productData = JSON.parse(this.getAttribute('data-product'));
                
                modal.querySelector('.modal-title').textContent = 'Edit Produk: ' + productData.name;
                modal.querySelector('#form-action').value = 'edit';
                modal.querySelector('#form-id').value = productData.id;
                modal.querySelector('#form-name').value = productData.name;
                modal.querySelector('#form-sku').value = productData.sku;
                modal.querySelector('#form-category').value = productData.category_id;
                modal.querySelector('#form-supplier').value = productData.supplier_id;
                modal.querySelector('#form-description').value = productData.description;
                modal.querySelector('#form-cost-price').value = productData.cost_price;
                modal.querySelector('#form-selling-price').value = productData.selling_price;
                modal.querySelector('#form-stock').value = productData.stock;
                modal.querySelector('#form-low-stock').value = productData.low_stock_threshold;
                modal.querySelector('#form-weight').value = productData.weight_kg;
                modal.querySelector('#form-is-active').checked = (productData.is_active == 1);
                modal.querySelector('#form-is-featured').checked = (productData.is_featured == 1);
                modal.querySelector('#image-help-text').textContent = 'Biarkan kosong jika tidak ingin mengganti gambar.';
                modal.querySelector('#form-submit-button').textContent = 'Simpan Perubahan';
                productModal.show();
            });
        });
    });
</script>

<!-- ... (setelah semua HTML: tabel, modal, form, dll.) ... -->

<?php
// Mulai buffer untuk menangkap semua output CSS dan JS kustom
ob_start(); 
?>

<!-- Style kustom untuk halaman ini -->
<style>
    .product-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 0.25rem;
        background-color: #e9ecef;
    }
</style>

<!-- JavaScript kustom untuk halaman ini -->
<script>
    function confirmDelete(id) { 
        if (confirm("Yakin ingin menghapus produk ini? Aksi ini tidak bisa dibatalkan.")) { 
            document.getElementById('delete-id').value = id; 
            document.getElementById('deleteForm').submit(); 
        } 
    }

    document.addEventListener('DOMContentLoaded', function () {
        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        const modal = document.getElementById('productModal');
        const form = document.getElementById('productForm');

        // Handle tombol "Tambah Produk Baru"
        document.getElementById('addNewProductBtn').addEventListener('click', function() {
            form.reset();
            modal.querySelector('.modal-title').textContent = 'Tambah Produk Baru';
            modal.querySelector('#form-action').value = 'add';
            modal.querySelector('#form-id').value = '';
            modal.querySelector('#form-sku').value = '(Otomatis)';
            modal.querySelector('#image-help-text').textContent = '';
            modal.querySelector('#form-submit-button').textContent = 'Simpan Produk';
            productModal.show();
        });

        // Handle semua tombol "Edit"
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productData = JSON.parse(this.getAttribute('data-product'));
                
                modal.querySelector('.modal-title').textContent = 'Edit Produk: ' + productData.name;
                modal.querySelector('#form-action').value = 'edit';
                modal.querySelector('#form-id').value = productData.id;
                modal.querySelector('#form-name').value = productData.name;
                modal.querySelector('#form-sku').value = productData.sku;
                modal.querySelector('#form-category').value = productData.category_id;
                modal.querySelector('#form-supplier').value = productData.supplier_id;
                modal.querySelector('#form-description').value = productData.description;
                modal.querySelector('#form-cost-price').value = productData.cost_price;
                modal.querySelector('#form-selling-price').value = productData.selling_price;
                modal.querySelector('#form-stock').value = productData.stock;
                modal.querySelector('#form-low-stock').value = productData.low_stock_threshold;
                modal.querySelector('#form-weight').value = productData.weight_kg;
                modal.querySelector('#form-is-active').checked = (productData.is_active == 1);
                modal.querySelector('#form-is-featured').checked = (productData.is_featured == 1);
                modal.querySelector('#image-help-text').textContent = 'Biarkan kosong jika tidak ingin mengganti gambar.';
                modal.querySelector('#form-submit-button').textContent = 'Simpan Perubahan';
                productModal.show();
            });
        });
    });
</script>
<?php
// Simpan semua output (CSS dan JS) ke dalam satu variabel
$page_scripts = ob_get_clean(); 
?>

<?php
require '../_templates/admin_footer.php'; // Panggil template footer
?>