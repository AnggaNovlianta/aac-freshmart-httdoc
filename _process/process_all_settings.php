<?php
session_start();
require '../_includes/db_connection.php';

// Keamanan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

// Fungsi helper untuk update, membuat kode lebih rapi
function update_setting($key, $value, $conn) {
    $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->bind_param("ss", $value, $key);
    $stmt->execute();
    $stmt->close();
}

// Fungsi helper untuk handle upload gambar
function handle_image_upload($file_input_name, $setting_key, $upload_subdir, $conn) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        
        $upload_dir = '../assets/images/' . $upload_subdir . '/';

        // Dapatkan nama file lama untuk dihapus
        $stmt_old = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt_old->bind_param("s", $setting_key);
        $stmt_old->execute();
        $old_image_filename = $stmt_old->get_result()->fetch_assoc()['setting_value'] ?? null;
        $stmt_old->close();
        
        // Buat nama file baru yang unik
        $file_extension = pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION);
        $new_filename = $setting_key . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        // Pindahkan file dan update DB
        if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $upload_path)) {
            update_setting($setting_key, $new_filename, $conn);
            
            // Hapus file lama jika ada dan bukan URL placeholder
            $old_image_path = $upload_dir . $old_image_filename;
            if ($old_image_filename && !filter_var($old_image_filename, FILTER_VALIDATE_URL) && file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Daftar semua kunci teks yang akan di-update
    $text_keys = [
        'company_name', 'company_icon_class', 'hero_title', 'hero_subtitle', 'hero_button_text',
        'about_headline', 'about_text_1', 'about_text_2', 'features_headline', 'features_subheadline',
        'feature_1_icon_class', 'feature_1_title', 'feature_1_text',
        'feature_2_icon_class', 'feature_2_title', 'feature_2_text',
        'feature_3_icon_class', 'feature_3_title', 'feature_3_text',
        'footer_description', 'social_facebook_url', 'social_instagram_url', 'social_whatsapp_url',
        'contact_address', 'contact_email', 'contact_phone', 'cta_headline','cta_text','cta_button_text','cta_button_icon_class'
    ];

    // Loop dan update semua pengaturan teks
    foreach ($text_keys as $key) {
        if (isset($_POST[$key])) {
            update_setting($key, $_POST[$key], $conn);
        }
    }

    // Handle upload gambar
    handle_image_upload('hero_image', 'hero_image', 'site', $conn);
    handle_image_upload('about_image', 'about_image', 'site', $conn);

    // Redirect kembali dengan pesan sukses
    header("Location: " . BASE_URL . "admin/settings_management.php?status=success&message=Semua pengaturan berhasil diperbarui.");
    exit();
}
?>