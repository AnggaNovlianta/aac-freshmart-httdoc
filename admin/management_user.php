<?php
session_start();
$page_title = "Manajemen Pengguna";
require '../_includes/db_connection.php';
require '../_templates/admin_header.php';

// Ambil semua data pengguna
$users_query = "SELECT id, nama_lengkap, username, role, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" id="addUserBtn">
            <i class="fa fa-plus"></i> Tambah Pengguna Baru
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
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr><th>ID</th><th>Nama Lengkap</th><th>Username</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><span class="badge bg-<?php echo ($user['role'] == 'admin' ? 'success' : 'info'); ?>"><?php echo ucfirst($user['role']); ?></span></td>
                        <td><?php echo date('d M Y, H:i', strtotime($user['created_at'])); ?></td>
                        <td>
                            <!-- TOMBOL EDIT FUNGSIONAL -->
                            <button class="btn btn-warning btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#userModal"
                                    data-id="<?php echo $user['id']; ?>"
                                    data-nama_lengkap="<?php echo htmlspecialchars($user['nama_lengkap']); ?>"
                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                    data-role="<?php echo $user['role']; ?>">
                                <i class="fa fa-edit"></i>
                            </button>
                            <!-- TOMBOL HAPUS FUNGSIONAL -->
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $user['id']; ?>)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah & Edit Pengguna -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userModalLabel">Form Pengguna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="userForm" action="../_process/process_add_user.php" method="POST">
        <input type="hidden" name="action" id="form-action">
        <input type="hidden" name="id" id="form-id">
        <div class="modal-body">
            <div class="mb-3">
                <label for="form-nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="form-nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="mb-3">
                <label for="form-username" class="form-label">Username</label>
                <input type="text" class="form-control" id="form-username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="form-password" class="form-label">Password</label>
                <input type="password" class="form-control" id="form-password" name="password">
                <small id="passwordHelp" class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
            </div>
            <div class="mb-3">
                <label for="form-role" class="form-label">Role</label>
                <select class="form-select" id="form-role" name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="form-submit-button">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Form Hapus Tersembunyi -->
<form id="deleteForm" action="../_process/process_add_user.php" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id">
</form>

<?php
ob_start(); 
?>
<script>
function confirmDelete(id) {
    if (confirm("Apakah Anda yakin ingin menghapus pengguna ini?")) {
        document.getElementById('delete-id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const passwordInput = document.getElementById('form-password');
    const passwordHelp = document.getElementById('passwordHelp');

    // Handle tombol "Tambah Pengguna Baru"
    document.getElementById('addUserBtn').addEventListener('click', function() {
        form.reset();
        modal.querySelector('.modal-title').textContent = 'Tambah Pengguna Baru';
        modal.querySelector('#form-action').value = 'add';
        modal.querySelector('#form-id').value = '';
        passwordInput.required = true;
        passwordHelp.style.display = 'none';
        modal.querySelector('#form-submit-button').textContent = 'Simpan';
        userModal.show();
    });

    // Handle tombol "Edit"
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama_lengkap = this.getAttribute('data-nama_lengkap');
            const username = this.getAttribute('data-username');
            const role = this.getAttribute('data-role');

            form.reset();
            modal.querySelector('.modal-title').textContent = 'Edit Pengguna: ' + username;
            modal.querySelector('#form-action').value = 'edit';
            modal.querySelector('#form-id').value = id;
            modal.querySelector('#form-nama_lengkap').value = nama_lengkap;
            modal.querySelector('#form-username').value = username;
            modal.querySelector('#form-role').value = role;
            passwordInput.required = false;
            passwordHelp.style.display = 'block';
            modal.querySelector('#form-submit-button').textContent = 'Simpan Perubahan';
            userModal.show();
        });
    });
});
</script>
<?php
$page_scripts = ob_get_clean();
require '../_templates/admin_footer.php';
?>