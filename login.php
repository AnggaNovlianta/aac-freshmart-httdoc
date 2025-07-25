<?php
session_start();
// BENAR: Muat koneksi dan konfigurasi untuk menggunakan BASE_URL
require_once '_includes/db_connection.php'; 

// Jika sudah login, redirect ke dashboard yang sesuai menggunakan BASE_URL
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        // BENAR: Menggunakan BASE_URL untuk path yang andal
        header("Location: " . BASE_URL . "admin/admin_dashboard.php");
    } else {
        // BENAR: Menggunakan BASE_URL untuk path yang andal
        header("Location: " . BASE_URL . "user_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AAC Freshmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Login AAC Freshmart</h3>
                        <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>
                        <!-- ✅ Path form action ini sudah BENAR -->
                        <form action="_process/process_login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                             <p class="text-center mt-3">
                                <!-- ✅ Tautan ini sudah BENAR -->
                                <a href="index.php">Kembali ke Beranda</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>