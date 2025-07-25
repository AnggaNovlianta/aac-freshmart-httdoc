<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan & ambil semua data settings
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php?error=Akses ditolak!");
    exit();
}

$settings = [];
$settings_result = $conn->query("SELECT setting_key, setting_value FROM site_settings");
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Ambil data slide carousel
$slides_result = $conn->query("SELECT * FROM carousel_slides ORDER BY order_number ASC");
// Dapatkan nomor urut berikutnya untuk modal
$order_result = $conn->query("SELECT MAX(order_number) AS max_order FROM carousel_slides");
$next_order_number = ($order_result->fetch_assoc()['max_order'] ?? 0) + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Website - Admin</title>
    <!-- ... (CSS links Anda) ... -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style> body { background-color: #f8f9fa; } .thumbnail { width: 150px; height: auto; object-fit: cover; } </style>
</head>
<body>
<div class="container my-4">
    <h1 class="mb-4">Pengaturan Website</h1>
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3"><i class="fa fa-arrow-left"></i> Kembali ke Dashboard</a>

    <!-- Notifikasi -->
    <?php if(isset($_GET['status'])): ?>
    <div class="alert alert-<?php echo $_GET['status'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($_GET['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- SATU FORM UNTUK SEMUA PENGATURAN TEKS DAN GAMBAR -->
    <form action="../_process/process_all_settings.php" method="POST" enctype="multipart/form-data">
        
        <!-- Kartu Pengaturan Umum -->
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h4><i class="fa fa-globe me-2"></i>Pengaturan Umum</h4></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="company_name" class="form-label">Nama Perusahaan</label>
                        <input type="text" class="form-control" name="company_name" id="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="company_icon_class" class="form-label">Kelas Ikon Perusahaan (Font Awesome)</label>
                        <input type="text" class="form-control" name="company_icon_class" id="company_icon_class" value="<?php echo htmlspecialchars($settings['company_icon_class'] ?? ''); ?>">
                        <small class="form-text">Contoh: fa-solid fa-snowflake</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Hero Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h4><i class="fa fa-desktop me-2"></i>Hero Section</h4></div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="hero_title" class="form-label">Judul Hero</label>
                    <input type="text" class="form-control" name="hero_title" id="hero_title" value="<?php echo htmlspecialchars($settings['hero_title'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="hero_subtitle" class="form-label">Subjudul Hero</label>
                    <textarea class="form-control" name="hero_subtitle" id="hero_subtitle" rows="2"><?php echo htmlspecialchars($settings['hero_subtitle'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="hero_button_text" class="form-label">Teks Tombol Hero</label>
                    <input type="text" class="form-control" name="hero_button_text" id="hero_button_text" value="<?php echo htmlspecialchars($settings['hero_button_text'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="hero_image" class="form-label">Ganti Gambar Latar Hero</label>
                    <input type="file" class="form-control" name="hero_image" id="hero_image">
                    <small>Gambar saat ini: <?php echo htmlspecialchars($settings['hero_image'] ?? 'Tidak ada'); ?></small>
                </div>
            </div>
        </div>

        <!-- Kartu Tentang Kami -->
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h4><i class="fa fa-info-circle me-2"></i>Tentang Kami</h4></div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="about_headline" class="form-label">Judul "Tentang Kami"</label>
                    <input type="text" class="form-control" name="about_headline" id="about_headline" value="<?php echo htmlspecialchars($settings['about_headline'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="about_text_1" class="form-label">Paragraf 1</label>
                    <textarea class="form-control" name="about_text_1" id="about_text_1" rows="3"><?php echo htmlspecialchars($settings['about_text_1'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="about_text_2" class="form-label">Paragraf 2</label>
                    <textarea class="form-control" name="about_text_2" id="about_text_2" rows="4"><?php echo htmlspecialchars($settings['about_text_2'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="about_image" class="form-label">Ganti Gambar "Tentang Kami"</label>
                    <input type="file" class="form-control" name="about_image" id="about_image">
                    <small>Gambar saat ini: <?php echo htmlspecialchars($settings['about_image'] ?? 'Tidak ada'); ?></small>
                </div>
            </div>
        </div>
        
        <!-- Kartu Keunggulan Kami -->
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h4><i class="fa fa-star me-2"></i>Keunggulan Kami</h4></div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="features_headline" class="form-label">Judul Utama</label>
                    <input type="text" class="form-control" name="features_headline" id="features_headline" value="<?php echo htmlspecialchars($settings['features_headline'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="features_subheadline" class="form-label">Sub-Judul</label>
                    <input type="text" class="form-control" name="features_subheadline" id="features_subheadline" value="<?php echo htmlspecialchars($settings['features_subheadline'] ?? ''); ?>">
                </div>
                <hr>
                <!-- Keunggulan 1, 2, 3 -->
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <h5>Keunggulan #<?php echo $i; ?></h5>
                <div class="row mb-3">
                    <div class="col-md-3"><input type="text" class="form-control" name="feature_<?php echo $i; ?>_icon_class" placeholder="Kelas Ikon" value="<?php echo htmlspecialchars($settings['feature_'.$i.'_icon_class'] ?? ''); ?>"></div>
                    <div class="col-md-3"><input type="text" class="form-control" name="feature_<?php echo $i; ?>_title" placeholder="Judul Keunggulan" value="<?php echo htmlspecialchars($settings['feature_'.$i.'_title'] ?? ''); ?>"></div>
                    <div class="col-md-6"><textarea class="form-control" name="feature_<?php echo $i; ?>_text" placeholder="Teks Keunggulan" rows="2"><?php echo htmlspecialchars($settings['feature_'.$i.'_text'] ?? ''); ?></textarea></div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Kartu Footer -->
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h4><i class="fa fa-phone me-2"></i>Footer & Kontak</h4></div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="footer_description" class="form-label">Deskripsi di Footer</label>
                    <textarea class="form-control" name="footer_description" id="footer_description" rows="3"><?php echo htmlspecialchars($settings['footer_description'] ?? ''); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label>URL Facebook</label><input type="text" class="form-control" name="social_facebook_url" value="<?php echo htmlspecialchars($settings['social_facebook_url'] ?? ''); ?>"></div>
                    <div class="col-md-4 mb-3"><label>URL Instagram</label><input type="text" class="form-control" name="social_instagram_url" value="<?php echo htmlspecialchars($settings['social_instagram_url'] ?? ''); ?>"></div>
                    <div class="col-md-4 mb-3"><label>URL WhatsApp</label><input type="text" class="form-control" name="social_whatsapp_url" value="<?php echo htmlspecialchars($settings['social_whatsapp_url'] ?? ''); ?>"></div>
                </div>
                 <div class="row">
                    <div class="col-md-4 mb-3"><label>Alamat</label><input type="text" class="form-control" name="contact_address" value="<?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?>"></div>
                    <div class="col-md-4 mb-3"><label>Email</label><input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>"></div>
                    <div class="col-md-4 mb-3"><label>Telepon</label><input type="text" class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>"></div>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan Utama -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Simpan Semua Pengaturan</button>
        </div>
    </form>

    <!-- KARTU MANAJEMEN CAROUSEL (Tetap terpisah karena logikanya beda) -->
    <div class="card shadow-sm mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa-regular fa-images me-2"></i>Manajemen Slide Carousel</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                <i class="fa fa-plus"></i> Tambah Slide Baru
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Urutan</th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($slides_result->num_rows > 0): ?>
                            <?php $slides_result->data_seek(0); // Reset pointer hasil query ?>
                            <?php while($slide = $slides_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $slide['order_number']; ?></td>
                                <td><img src="../assets/images/carousel/<?php echo htmlspecialchars($slide['image_path']); ?>" alt="<?php echo htmlspecialchars($slide['title']); ?>" class="thumbnail rounded"></td>
                                <td><?php echo htmlspecialchars($slide['title']); ?></td>
                                <td><?php echo htmlspecialchars($slide['caption']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($slide['is_active'] ? 'success' : 'secondary'); ?>">
                                        <?php echo ($slide['is_active'] ? 'Aktif' : 'Non-Aktif'); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Di sini Anda bisa menambahkan tombol Edit nanti -->
                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $slide['id']; ?>)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada slide. Silakan tambahkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- AKHIR DARI BLOK YANG DITAMBAHKAN KEMBALI -->
</div>

<!-- ... (Modal & JavaScript Anda) ... -->
<!-- Modal Tambah Slide (Pastikan kode ini ada di bawah) -->
<!-- Modal Tambah Slide (dengan ID Unik) -->
<div class="modal fade" id="addSlideModal" tabindex="-1" aria-labelledby="addSlideModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addSlideModalLabel">Tambah Slide Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../_process/process_carousel.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">

            <!-- INPUT JUDUL -->
            <div class="mb-3">
                <!-- ✅ DIUBAH: 'for' dan 'id' diberi prefix 'modal_' -->
                <label for="modal_title" class="form-label">Judul Slide</label>
                <input type="text" class="form-control" id="modal_title" name="title" required>
            </div>

            <!-- INPUT KETERANGAN -->
            <div class="mb-3">
                <!-- ✅ DIUBAH: 'for' dan 'id' diberi prefix 'modal_' -->
                <label for="modal_caption" class="form-label">Keterangan (Caption)</label>
                <textarea class="form-control" id="modal_caption" name="caption" rows="3"></textarea>
            </div>

            <!-- INPUT GAMBAR -->
            <div class="mb-3">
                <!-- ✅ DIUBAH: 'for' dan 'id' diberi prefix 'modal_' -->
                <label for="modal_image" class="form-label">File Gambar (Rekomendasi: 1200x500px)</label>
                <input type="file" class="form-control" id="modal_image" name="image" accept="image/jpeg, image/png, image/webp" required>
            </div>

            <!-- INPUT NOMOR URUT OTOMATIS -->
            <div class="mb-3">
                <!-- ✅ DIUBAH: 'for' dan 'id' diberi prefix 'modal_' -->
                <label for="modal_order_number" class="form-label">Nomor Urut</label>
                <input type="number" class="form-control" id="modal_order_number" name="order_number" value="<?php echo $next_order_number; ?>" readonly>
            </div>

            <!-- INPUT STATUS AKTIF -->
            <div class="form-check">
                <!-- ✅ DIUBAH: 'id' diberi prefix 'modal_' -->
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="modal_is_active" checked>
                <label class="form-check-label" for="modal_is_active">
                    Aktifkan slide ini?
                </label>
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
<!-- Form Hapus Tersembunyi (Pastikan kode ini ada di bawah) -->
<form id="deleteForm" action="../_process/process_carousel.php" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id) {
    if (confirm("Apakah Anda yakin ingin menghapus slide ini?")) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>
</body>
</html>