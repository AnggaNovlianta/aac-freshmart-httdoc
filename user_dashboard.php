<?php
session_start();
$page_title = "Dashboard";
require_once '_includes/db_connection.php'; 
require_once '_includes/auth_check.php';

// Memeriksa izin menggunakan sistem terpusat
enforce_permission($conn);

// Ambil semua menu PENGGUNA yang aktif dari database
$menus_query = "SELECT * FROM user_menus WHERE is_active = 1 ORDER BY order_number ASC";
$menus_result = $conn->query($menus_query);

// Panggil template header pengguna
require '_templates/user_header.php'; 
?>

<!-- Header Halaman -->
<div class="pb-2 mb-4 border-bottom">
    <h1 class="h2">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p class="text-muted">Pilih menu di bawah untuk melanjutkan aktivitas Anda.</p>
</div>

<!-- Grid untuk Kartu Menu -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4 py-3">

    <?php if ($menus_result && $menus_result->num_rows > 0): ?>
        <?php while($menu = $menus_result->fetch_assoc()): ?>
            <div class="col">
                <a href="<?php echo htmlspecialchars($menu['url']); ?>" class="text-decoration-none text-dark">
                    <div class="card h-100 shadow-sm border-0 text-center text-decoration-none hover-lift">
                        <div class="card-body d-flex flex-column justify-content-center p-4">
                            <div class="display-4 text-primary mb-3">
                                <i class="<?php echo htmlspecialchars($menu['icon_class']); ?>"></i>
                            </div>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($menu['title']); ?></h5>
                            <p class="card-text text-muted small mb-0"><?php echo htmlspecialchars($menu['description']); ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">
                Belum ada menu yang tersedia untuk Anda saat ini.
            </div>
        </div>
    <?php endif; ?>

</div>

<?php
ob_start(); 
?>
<style>
    .hover-lift {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-lift:hover { 
        transform: translateY(-8px); 
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>
<?php
$page_scripts = ob_get_clean();
?>

<?php
// Panggil template footer pengguna
require '_templates/user_footer.php';
?>