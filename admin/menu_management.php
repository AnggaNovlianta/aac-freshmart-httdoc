<?php
session_start();
$page_title = "Pengaturan Menu Dashboard";
require '../_includes/db_connection.php';

// Ambil semua menu dari database, urutkan
$menus_query = "SELECT * FROM admin_menus ORDER BY order_number ASC, title ASC";
$menus_result = $conn->query($menus_query);

// Panggil template header
require '../_templates/admin_header.php'; 
?>

<!-- KONTEN SPESIFIK HALAMAN DIMULAI -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" id="addMenuBtn">
            <i class="fa fa-plus"></i> Tambah Menu Baru
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
        <p class="text-muted">Atur menu yang akan ditampilkan sebagai ikon di halaman utama dashboard admin. Urutkan berdasarkan nomor (semakin kecil, semakin di atas).</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr><th>Urutan</th><th>Ikon</th><th>Judul</th><th>Deskripsi</th><th>URL</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if ($menus_result && $menus_result->num_rows > 0): while($menu = $menus_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $menu['order_number']; ?></td>
                        <td><i class="<?php echo htmlspecialchars($menu['icon_class']); ?> fa-2x text-primary"></i></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($menu['title']); ?></td>
                        <td><?php echo htmlspecialchars($menu['description']); ?></td>
                        <td><code><?php echo htmlspecialchars($menu['url']); ?></code></td>
                        <td><span class="badge bg-<?php echo ($menu['is_active'] ? 'success' : 'secondary'); ?>"><?php echo ($menu['is_active'] ? 'Aktif' : 'Non-Aktif'); ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-light edit-btn" data-menu='<?php echo htmlspecialchars(json_encode($menu), ENT_QUOTES, 'UTF-8'); ?>'><i class="fa fa-edit"></i></button>
                            <button class="btn btn-sm btn-light" onclick="confirmDelete(<?php echo $menu['id']; ?>)"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" class="text-center p-4">Belum ada menu.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah & Edit Menu -->
<div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="menuModalLabel">Form Menu</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <form id="menuForm" action="../_process/process_menu.php" method="POST">
        <input type="hidden" name="action" id="form-action">
        <input type="hidden" name="id" id="form-id">
        <div class="modal-body">
            <div class="mb-3"><label for="form-title" class="form-label">Judul Menu</label><input type="text" name="title" id="form-title" class="form-control" required></div>
            <div class="mb-3"><label for="form-description" class="form-label">Deskripsi Singkat</label><input type="text" name="description" id="form-description" class="form-control"></div>
            <div class="mb-3"><label for="form-icon-class" class="form-label">Kelas Ikon (Font Awesome)</label><input type="text" name="icon_class" id="form-icon-class" class="form-control" placeholder="Contoh: fa-solid fa-star"></div>
            <div class="mb-3"><label for="form-url" class="form-label">URL Tujuan</label><input type="text" name="url" id="form-url" class="form-control" placeholder="Contoh: new_page.php" required></div>
            <div class="mb-3"><label for="form-order-number" class="form-label">Nomor Urut</label><input type="number" name="order_number" id="form-order-number" class="form-control" value="100"></div>
            <div class="form-check form-switch"><input type="checkbox" name="is_active" value="1" id="form-is-active" class="form-check-input" checked><label class="form-check-label" for="form-is-active">Aktifkan menu ini</label></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="form-submit-button">Simpan</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Form Hapus Tersembunyi -->
<form id="deleteForm" action="../_process/process_menu.php" method="POST" style="display: none;"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="delete-id"></form>

<?php
ob_start(); 
?>
<script>
function confirmDelete(id) { if (confirm("Yakin ingin menghapus menu ini?")) { document.getElementById('delete-id').value = id; document.getElementById('deleteForm').submit(); } }

document.addEventListener('DOMContentLoaded', function () {
    const menuModal = new bootstrap.Modal(document.getElementById('menuModal'));
    const modal = document.getElementById('menuModal');
    const form = document.getElementById('menuForm');

    document.getElementById('addMenuBtn').addEventListener('click', function() {
        form.reset();
        modal.querySelector('.modal-title').textContent = 'Tambah Menu Baru';
        modal.querySelector('#form-action').value = 'add';
        modal.querySelector('#form-id').value = '';
        modal.querySelector('#form-submit-button').textContent = 'Simpan Menu';
        menuModal.show();
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const menuData = JSON.parse(this.getAttribute('data-menu'));
            modal.querySelector('.modal-title').textContent = 'Edit Menu: ' + menuData.title;
            modal.querySelector('#form-action').value = 'edit';
            modal.querySelector('#form-id').value = menuData.id;
            modal.querySelector('#form-title').value = menuData.title;
            modal.querySelector('#form-description').value = menuData.description;
            modal.querySelector('#form-icon-class').value = menuData.icon_class;
            modal.querySelector('#form-url').value = menuData.url;
            modal.querySelector('#form-order-number').value = menuData.order_number;
            modal.querySelector('#form-is-active').checked = (menuData.is_active == 1);
            modal.querySelector('#form-submit-button').textContent = 'Simpan Perubahan';
            menuModal.show();
        });
    });
});
</script>
<?php
$page_scripts = ob_get_clean();
require '../_templates/admin_footer.php';
?>