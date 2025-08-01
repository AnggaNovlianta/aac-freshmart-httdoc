<?php
session_start();
$page_title = "Manajemen Izin Akses Halaman";
require '../_templates/admin_header.php'; // Ini sudah memanggil db_connection dan auth_check

// Ambil semua data izin dari database
$permissions_query = "SELECT * FROM page_permissions ORDER BY page_name ASC";
$permissions_result = $conn->query($permissions_query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <!-- TOMBOL BARU untuk memicu modal tambah -->
        <button type="button" class="btn btn-sm btn-primary" id="addPageBtn">
            <i class="fa fa-plus"></i> Tambah Halaman Baru
        </button>
    </div>
</div>

<!-- Notifikasi -->
<?php if(isset($_GET['status'])): ?>
<div class="alert alert-<?php echo $_GET['status'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_GET['message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <p class="text-muted">Kelola halaman mana saja yang terdaftar di sistem dan peran (role) minimum yang diperlukan untuk mengaksesnya.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Halaman</th>
                        <th>Kunci Halaman (Filename)</th>
                        <th>Peran Dibutuhkan</th>
                        <th>Aksi</th> <!-- Kolom Aksi BARU -->
                    </tr>
                </thead>
                <tbody>
                    <?php if ($permissions_result && $permissions_result->num_rows > 0): while($p = $permissions_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($p['page_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($p['description']); ?></small>
                        </td>
                        <td><code><?php echo htmlspecialchars($p['page_key']); ?></code></td>
                        <td>
                            <span class="badge bg-<?php 
                                switch($p['required_role']) {
                                    case 'admin': echo 'danger'; break;
                                    case 'user': echo 'info'; break;
                                    default: echo 'success';
                                }
                            ?>"><?php echo ucfirst($p['required_role']); ?></span>
                        </td>
                        <!-- Tombol Aksi BARU -->
                        <td>
                            <button class="btn btn-sm btn-light edit-btn" data-page='<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>'><i class="fa fa-edit"></i></button>
                            <button class="btn btn-sm btn-light" onclick="confirmDelete(<?php echo $p['id']; ?>)"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" class="text-center p-4">Belum ada data izin halaman.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL BARU untuk Tambah & Edit -->
<div class="modal fade" id="pageModal" tabindex="-1" aria-labelledby="pageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="pageModalLabel">Form Izin Halaman</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <form id="pageForm" action="../_process/process_permissions.php" method="POST">
        <input type="hidden" name="action" id="form-action">
        <input type="hidden" name="id" id="form-id">
        <div class="modal-body">
            <div class="mb-3">
                <label for="form-page_name" class="form-label">Nama Halaman (Judul)</label>
                <input type="text" name="page_name" id="form-page_name" class="form-control" required placeholder="Contoh: Laporan Penjualan">
            </div>
            <div class="mb-3">
                <label for="form-page_key" class="form-label">Kunci Halaman (Nama File)</label>
                <input type="text" name="page_key" id="form-page_key" class="form-control" required placeholder="Contoh: laporan_penjualan.php">
            </div>
            <div class="mb-3">
                <label for="form-description" class="form-label">Deskripsi</label>
                <textarea name="description" id="form-description" class="form-control" rows="2" placeholder="Jelaskan fungsi halaman ini"></textarea>
            </div>
            <div class="mb-3">
                <label for="form-role" class="form-label">Peran yang Dibutuhkan</label>
                <select name="required_role" id="form-role" class="form-select" required>
                    <option value="public">Publik (Semua Orang)</option>
                    <option value="user">User</option>
                    <option value="admin" selected>Admin</option>
                </select>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="form-submit-button">Simpan</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Form Hapus Tersembunyi -->
<form id="deleteForm" action="../_process/process_permissions.php" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id">
</form>

<?php
ob_start(); 
?>
<script>
function confirmDelete(id) {
    if (confirm("Yakin ingin menghapus data izin halaman ini? Aksi ini tidak dapat dibatalkan.")) {
        document.getElementById('delete-id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const pageModal = new bootstrap.Modal(document.getElementById('pageModal'));
    const modal = document.getElementById('pageModal');
    const form = document.getElementById('pageForm');

    // Menangani klik tombol "Tambah Halaman Baru"
    document.getElementById('addPageBtn').addEventListener('click', function() {
        form.reset();
        modal.querySelector('.modal-title').textContent = 'Tambah Izin Halaman Baru';
        modal.querySelector('#form-action').value = 'add';
        modal.querySelector('#form-id').value = '';
        modal.querySelector('#form-role').value = 'admin'; // Default ke admin
        modal.querySelector('#form-submit-button').textContent = 'Simpan';
        pageModal.show();
    });

    // Menangani klik tombol "Edit" di setiap baris
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const pageData = JSON.parse(this.getAttribute('data-page'));
            
            modal.querySelector('.modal-title').textContent = 'Edit Izin Halaman: ' + pageData.page_name;
            modal.querySelector('#form-action').value = 'edit';
            modal.querySelector('#form-id').value = pageData.id;
            modal.querySelector('#form-page_name').value = pageData.page_name;
            modal.querySelector('#form-page_key').value = pageData.page_key;
            modal.querySelector('#form-description').value = pageData.description;
            modal.querySelector('#form-role').value = pageData.required_role;
            modal.querySelector('#form-submit-button').textContent = 'Simpan Perubahan';
            pageModal.show();
        });
    });
});
</script>
<?php
$page_scripts = ob_get_clean();
require '../_templates/admin_footer.php';
?>