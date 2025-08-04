<?php // BARIS PALING ATAS
// Blok keamanan terpusat
require_once __DIR__ . '/../_includes/db_connection.php';
require_once __DIR__ . '/../_includes/auth_check.php';
enforce_permission($conn);

// Baris di bawah ini sekarang bisa dihapus karena sudah ditangani oleh enforce_permission()
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { ... }

// Dapatkan nama halaman saat ini untuk menandai menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - AAC Freshmart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        /* Hapus style untuk sidebar dan main, karena sudah tidak digunakan */
    </style>
</head>
<body>

<!-- Navbar Baru yang Lebih Lengkap -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid">
        <!-- Brand / Nama Perusahaan -->
        <a class="navbar-brand" href="admin_dashboard.php">
            <i class="fa-solid fa-snowflake"></i> AAC Freshmart Admin
        </a>

        <!-- Tombol Toggler untuk Mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Konten Navbar yang Bisa Collapse -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <!-- Menu Utama (dipindahkan ke sini dari sidebar) -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php if($current_page == 'admin_dashboard.php') echo 'active'; ?>" href="admin_dashboard.php">
                        <i class="fa fa-tachometer-alt fa-fw me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if($current_page == 'product_management.php') echo 'active'; ?>" href="product_management.php">
                        <i class="fa fa-box-open fa-fw me-1"></i> Produk
                    </a>
                </li>

                <!-- Dropdown untuk Submenu Manajemen Produk -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-cogs fa-fw me-1"></i> Pengelolaan
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="managementDropdown">
                        <li>
                            <a class="dropdown-item" href="category_management.php">
                                <i class="fa fa-tags fa-fw me-2"></i> Kelola Kategori
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="supplier_management.php">
                                <i class="fa fa-truck fa-fw me-2"></i> Kelola Supplier
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="satuan.php">
                                <i class="fa fa-truck fa-fw me-2"></i> Kelola UoM
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php if($current_page == 'settings_management.php') echo 'active'; ?>" href="settings_management.php">
                        <i class="fa fa-sliders fa-fw me-1"></i> Pengaturan Website
                    </a>
                </li>
            </ul>

            <!-- Profil Pengguna dan Tombol Logout di Sebelah Kanan -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                 <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user-circle fa-fw me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="../_process/process_logout.php">
                                <i class="fa fa-sign-out-alt fa-fw me-2"></i> Sign out
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Kontainer Utama untuk Konten Halaman -->
<main>
    <!-- Konten spesifik halaman akan dimulai di sini -->