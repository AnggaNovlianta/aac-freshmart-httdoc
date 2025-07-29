<?php
session_start();
$page_title = "Admin Dashboard"; // Judul ini akan digunakan oleh header
require '../_includes/db_connection.php';

// Ambil semua menu yang aktif dari database, urutkan berdasarkan order_number
$menus_query = "SELECT * FROM admin_menus WHERE is_active = 1 ORDER BY order_number ASC";
$menus_result = $conn->query($menus_query);

// Panggil template header SETELAH semua data siap
require '../_templates/admin_header.php'; 
?>

<!-- ========================================================================= -->
<!-- =================== KONTEN SPESIFIK HALAMAN DIMULAI =================== -->
<!-- ========================================================================= -->

<!-- Header Halaman -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p class="text-muted">Pilih salah satu menu di bawah ini untuk memulai pengelolaan website Anda.</p>
    </div>
</div>

<!-- Grid untuk Kartu Menu -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4 py-3">

    <?php if ($menus_result && $menus_result->num_rows > 0): ?>
        <?php while($menu = $menus_result->fetch_assoc()): ?>
            <div class="col">
                <a href="<?php echo htmlspecialchars($menu['url']); ?>" class="text-decoration-none text-dark">
                    <div class="card h-100 shadow-sm border-0 text-center text-decoration-none hover-lift">
                        <div class="card-body d-flex flex-column justify-content-center p-4">
                            
                            <!-- Ikon Menu -->
                            <div class="display-4 text-primary mb-3">
                                <i class="<?php echo htmlspecialchars($menu['icon_class']); ?>"></i>
                            </div>
                            
                            <!-- Judul Menu -->
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($menu['title']); ?></h5>
                            
                            <!-- Deskripsi Menu -->
                            <p class="card-text text-muted small mb-0"><?php echo htmlspecialchars($menu['description']); ?></p>
                        
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning">
                Belum ada menu yang bisa ditampilkan. Silakan tambahkan menu terlebih dahulu melalui 
                <a href="menu_management.php">Pengaturan Menu</a>.
            </div>
        </div>
    <?php endif; ?>

</div>

<?php
// Mulai buffer untuk menangkap CSS spesifik halaman
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
// Simpan CSS ke dalam variabel $page_styles yang akan dicetak oleh footer
$page_styles = ob_get_clean(); 
?>

<?php
require '../_templates/admin_footer.php'; // Panggil template footer
?>