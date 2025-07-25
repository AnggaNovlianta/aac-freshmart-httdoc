<?php
session_start();
// BENAR: Sertakan koneksi (dan config)
require_once '../_includes/db_connection.php';

// Cek apakah pengguna sudah login dan memiliki role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // BENAR: Gunakan BASE_URL untuk pengalihan yang andal
    header("Location: " . BASE_URL . "login.php?error=Akses ditolak!");
    exit();
}
// Ambil semua data pengguna dari database untuk ditampilkan di tabel
$query_users = "SELECT id, nama_lengkap, username, role, created_at FROM users ORDER BY created_at DESC";
$result_users = $conn->query($query_users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AAC Freshmart</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS untuk Dashboard -->
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            background: #212529; /* Warna gelap */
            color: white;
            flex-shrink: 0;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 10px 15px;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: #495057;
        }
        .sidebar .nav-link .fa {
            margin-right: 10px;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar Menu -->
<div class="sidebar d-flex flex-column p-3">
    <h4 class="text-center">AAC Freshmart</h4>
    <hr class="text-white">
    <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
        <a href="admin_dashboard.php" class="nav-link text-white">
            <i class="fa fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    <li>
        <a href="admin_dashboard.php" class="nav-link text-white">
            <i class="fa fa-users"></i> Manajemen User
        </a>
    </li>
    <li>
    <!-- Di dalam file admin_dashboard.php, di dalam <ul class="nav ..."> -->
    <li>
        <a href="settings_management.php" class="nav-link text-white">
            <i class="fa fa-cog"></i> Pengaturan Website
        </a>
    </li>
    <li>
    <a href="product_management.php" class="nav-link text-white">
        <i class="fa fa-box-open"></i> Manajemen Produk
    </a>
    </li>
    <li>
        <a href="#" class="nav-link text-white">
            <i class="fa fa-box"></i> Manajemen Keuangan
        </a>
    </li>
    <li>
        <a href="#" class="nav-link text-white">
            <i class="fa fa-box"></i> Manajemen Karyawan
        </a>
    </li>
</ul>
    <hr class="text-white">
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fa fa-user-circle me-2"></i>
            <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li><a class="dropdown-item" href="../_process/process_logout.php">Sign out</a></li>
        </ul>
    </div>
</div>

<!-- Main Content -->
<div class="content">
    <nav class="navbar navbar-expand-lg navbar-light bg-light rounded-3 mb-4">
        <div class="container-fluid">
            <h1 class="h3">Manajemen User</h1>
        </div>
    </nav>

    <!-- Notifikasi -->
    <?php if(isset($_GET['status'])): ?>
        <?php if($_GET['status'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                User baru berhasil ditambahkan!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif($_GET['status'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            Daftar Pengguna
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fa fa-plus"></i> Tambah User Baru
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Tanggal Registrasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><span class="badge bg-<?php echo ($user['role'] == 'admin' ? 'success' : 'info'); ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td><?php echo date('d M Y, H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Tambah User Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../_process/process_add_user.php" method="POST">
        <div class="modal-body">
            <div class="mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="user" selected>User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>