<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan & ambil data produk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php?error=Akses ditolak!");
    exit();
}
$products_query = "SELECT * FROM products ORDER BY created_at DESC";
$products_result = $conn->query($products_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Produk - Admin</title>
    <!-- ... (CSS links Anda) ... -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style> body { background-color: #f8f9fa; } .product-thumbnail { width: 80px; height: 80px; object-fit: cover; } </style>
</head>
<body>
<div class="container my-4">
    <h1 class="mb-4">Manajemen Produk</h1>
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3"><i class="fa fa-arrow-left"></i> Kembali ke Dashboard</a>

    <!-- Notifikasi -->
    <?php if(isset($_GET['status'])): ?>
    <div class="alert alert-<?php echo $_GET['status'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($_GET['message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Daftar Produk</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fa fa-plus"></i> Tambah Produk Baru</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr><th>Gambar</th><th>Nama Produk</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while($product = $products_result->fetch_assoc()): ?>
                            <tr>
                                <td><img src="../assets/images/products/<?php echo htmlspecialchars($product['image_path'] ?? 'default.png'); ?>" class="product-thumbnail rounded"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td><span class="badge bg-<?php echo ($product['is_active'] ? 'success' : 'secondary'); ?>"><?php echo ($product['is_active'] ? 'Aktif' : 'Non-Aktif'); ?></span></td>
                                <td>
                                    <!-- TOMBOL EDIT BARU -->
                                    <button class="btn btn-warning btn-sm edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editProductModal"
                                            data-id="<?php echo $product['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                            data-price="<?php echo $product['price']; ?>"
                                            data-stock="<?php echo $product['stock']; ?>"
                                            data-is_active="<?php echo $product['is_active']; ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <!-- TOMBOL HAPUS BARU -->
                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $product['id']; ?>)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Belum ada produk.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Produk (PASTIKAN KODE INI LENGKAP) -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addProductModalLabel">Form Tambah Produk Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../_process/process_product.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
            <!-- Nama Produk -->
            <div class="mb-3">
                <label for="add-name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="add-name" name="name" required>
            </div>
            <!-- Deskripsi -->
            <div class="mb-3">
                <label for="add-description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="add-description" name="description" rows="4"></textarea>
            </div>
            <!-- Harga dan Stok -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="add-price" class="form-label">Harga (Rp)</label>
                    <input type="number" class="form-control" id="add-price" name="price" step="100" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="add-stock" class="form-label">Jumlah Stok</label>
                    <input type="number" class="form-control" id="add-stock" name="stock" required>
                </div>
            </div>
            <!-- Gambar -->
            <div class="mb-3">
                <label for="add-image" class="form-label">Gambar Produk</label>
                <input type="file" class="form-control" id="add-image" name="image" accept="image/*" required>
            </div>
            <!-- Status Aktif -->
             <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="add-is_active" checked>
                <label class="form-check-label" for="add-is_active">Tampilkan produk ini di website?</label>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Produk</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT PRODUK (BARU) -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProductModalLabel">Edit Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="../_process/process_product.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit-id"> <!-- ID produk yang akan diedit -->
        <div class="modal-body">
            <div class="mb-3">
                <label for="edit-name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="edit-name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="edit-description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="edit-description" name="description" rows="4"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit-price" class="form-label">Harga (Rp)</label>
                    <input type="number" class="form-control" id="edit-price" name="price" step="100" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="edit-stock" class="form-label">Jumlah Stok</label>
                    <input type="number" class="form-control" id="edit-stock" name="stock" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-image" class="form-label">Ganti Gambar Produk (Opsional)</label>
                <input type="file" class="form-control" id="edit-image" name="image" accept="image/*">
                <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>
            </div>
             <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit-is_active">
                <label class="form-check-label" for="edit-is_active">Tampilkan produk ini di website?</label>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- FORM HAPUS TERSEMBUNYI (BARU) -->
<form id="deleteForm" action="../_process/process_product.php" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- JAVASCRIPT BARU -->
<script>
// Fungsi untuk konfirmasi hapus
function confirmDelete(id) {
    if (confirm("Apakah Anda yakin ingin menghapus produk ini secara permanen?")) {
        document.getElementById('delete-id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Fungsi untuk mengisi modal edit saat tombol edit diklik
document.addEventListener('DOMContentLoaded', function () {
    var editProductModal = document.getElementById('editProductModal');
    editProductModal.addEventListener('show.bs.modal', function (event) {
        // Tombol yang memicu modal
        var button = event.relatedTarget;
        
        // Ekstrak data dari atribut data-*
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var description = button.getAttribute('data-description');
        var price = button.getAttribute('data-price');
        var stock = button.getAttribute('data-stock');
        var isActive = button.getAttribute('data-is_active');

        // Update elemen-elemen di dalam modal
        var modalTitle = editProductModal.querySelector('.modal-title');
        var modalIdInput = editProductModal.querySelector('#edit-id');
        var modalNameInput = editProductModal.querySelector('#edit-name');
        var modalDescriptionInput = editProductModal.querySelector('#edit-description');
        var modalPriceInput = editProductModal.querySelector('#edit-price');
        var modalStockInput = editProductModal.querySelector('#edit-stock');
        var modalIsActiveCheckbox = editProductModal.querySelector('#edit-is_active');

        modalTitle.textContent = 'Edit Produk: ' + name;
        modalIdInput.value = id;
        modalNameInput.value = name;
        modalDescriptionInput.value = description;
        modalPriceInput.value = price;
        modalStockInput.value = stock;
        modalIsActiveCheckbox.checked = (isActive == 1);
    });
});
</script>
</body>
</html>