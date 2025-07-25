<?php
session_start();
// BENAR: Path require diperbaiki
require '../_includes/db_connection.php';

// Keamanan: Hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

// Fungsi ini sudah bagus, tidak perlu diubah
function update_setting($key, $value, $conn) {
    $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->bind_param("ss", $value, $key);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update Teks
    update_setting('hero_title', $_POST['hero_title'], $conn);
    update_setting('hero_subtitle', $_POST['hero_subtitle'], $conn);

    // Proses Upload Gambar Hero jika ada file baru
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] == 0) {
        // 1. Dapatkan nama file lama untuk dihapus nanti
        $stmt_old = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'hero_image'");
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $old_image_filename = $result_old->fetch_assoc()['setting_value'];
        $stmt_old->close();

        // BENAR: Path upload diperbaiki untuk naik ke folder akar dulu
        $upload_dir = '../assets/images/site/';
        $file_extension = pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'hero_image.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $upload_path)) {
            // 3. Update nama file di database
            update_setting('hero_image', $new_filename, $conn);

            // 4. Hapus file lama jika namanya berbeda dari yang baru
            $old_image_path = $upload_dir . $old_image_filename;
            if ($old_image_filename && $old_image_filename != $new_filename && file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        } else {
             // BENAR: Kesalahan ketik diperbaiki dan menggunakan BASE_URL
             header("Location: " . BASE_URL . "admin/settings_management.php?status=error&message=Gagal mengupload gambar.");
             exit();
        }
    }

    // BENAR: Kesalahan ketik diperbaiki dan menggunakan BASE_URL
    header("Location: " . BASE_URL . "admin/settings_management.php?status=success&message=Pengaturan berhasil diperbarui.");
    exit();
}
?>