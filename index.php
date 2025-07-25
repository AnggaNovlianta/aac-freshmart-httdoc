<?php
// PHP block Anda sudah sempurna, tidak perlu diubah.
require_once '_includes/db_connection.php'; 

// --- Ambil data pengaturan ---
$settings_query = "SELECT setting_key, setting_value FROM site_settings";
$settings_result = $conn->query($settings_query);
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// --- Ambil data slide ---
$slides_query = "SELECT * FROM carousel_slides WHERE is_active = 1 ORDER BY order_number ASC";
$slides_result = $conn->query($slides_query);
$slides = [];
if ($slides_result && $slides_result->num_rows > 0) {
    while($row = $slides_result->fetch_assoc()) {
        $slides[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AAC Freshmart - Distributor Frozen Food Terpercaya</title>
    
    <!-- Library CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS Anda (opsional, jika ada) -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Custom CSS Tambahan untuk Tampilan Modern -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .hero-section h1, .hero-section p {
            /* Menambahkan bayangan agar teks lebih terbaca di atas gambar apapun */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
        }
        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4rem;
            height: 4rem;
            margin-bottom: 1rem;
            font-size: 2rem;
            color: #fff;
            background-color: #0d6efd; /* Warna utama Bootstrap */
            border-radius: 50%;
        }
        #cta {
            background: linear-gradient(90deg, #0d6efd, #0558ca);
        }
        .footer-social-link {
            display: inline-block;
            height: 40px;
            width: 40px;
            background-color: rgba(255,255,255,0.2);
            margin: 0 10px 10px 0;
            text-align: center;
            line-height: 40px;
            border-radius: 50%;
            color: #ffffff;
            transition: all 0.3s ease;
        }
        .footer-social-link:hover {
            color: #0d6efd;
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <!-- Navbar (dibuat sticky-top) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bolder" href="index.php">
                <i class="<?php echo htmlspecialchars($settings['company_icon_class'] ?? 'fa-solid fa-snowflake'); ?>"></i>
                    <?php echo htmlspecialchars($settings['company_name'] ?? 'AAC Freshmart'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#hero">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tentang-kami">Tentang Kami</a></li>
                    <li class="nav-item"><a class="nav-link" href="#keunggulan-kami">Keunggulan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#produk-unggulan">Produk</a></li>
                </ul>
                <a href="login.php" class="btn btn-primary ms-lg-3 mt-2 mt-lg-0">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section (Tetap sama, sudah bagus) -->
    <header id="hero" class="hero-section d-flex align-items-center" 
        style="background: url('assets/images/site/<?php echo htmlspecialchars($settings['hero_image'] ?? 'hero_default.jpg'); ?>') no-repeat center center; background-size: cover;">
        <div class="container text-center text-white">
            <h1 class="display-3 fw-bold"><?php echo htmlspecialchars($settings['hero_title'] ?? 'Judul Default'); ?></h1>
            <p class="lead my-3 fs-4"><?php echo htmlspecialchars($settings['hero_subtitle'] ?? 'Subjudul Default'); ?></p>
            <a href="#produk-unggulan" class="btn btn-primary btn-lg mt-3 fw-bold">Lihat Produk Kami</a>
        </div>
    </header>

    <!-- Tentang Kami Section (Versi Diperbaiki) -->
    <section id="tentang-kami" class="py-5">
        <div class="container">
            <div class="row align-items-center g-5">
                
                <!-- KOLOM GAMBAR (Sekarang di kiri) -->
                <div class="col-lg-6">
                    <?php
                    // Logika PHP untuk menentukan sumber gambar
                    $about_image_path = $settings['about_image'] ?? '';
                    $image_source = '';
                    if (!empty($about_image_path)) {
                        if (filter_var($about_image_path, FILTER_VALIDATE_URL)) {
                            $image_source = htmlspecialchars($about_image_path);
                        } else {
                            $image_source = 'assets/images/site/' . htmlspecialchars($about_image_path);
                        }
                    } else {
                        // Gambar default jika tidak ada di database
                        $image_source = 'https://via.placeholder.com/600x400.png?text=AAC+Freshmart';
                    }
                    ?>
                    <img src="<?php echo $image_source; ?>" class="img-fluid rounded shadow" alt="Gudang Frozen Food AAC Freshmart">
                </div>

                <!-- KOLOM TEKS (Sekarang di kanan) -->
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($settings['about_headline'] ?? 'Mitra Terpercaya untuk Bisnis Kuliner Anda'); ?></h2>
                    <p class="text-muted fs-5"><?php echo htmlspecialchars($settings['about_text_1'] ?? 'Teks default paragraf 1.'); ?></p>
                    <p><?php echo htmlspecialchars($settings['about_text_2'] ?? 'Teks default paragraf 2.'); ?></p>
                </div>

            </div>
        </div>
    </section>

    <!-- Keunggulan Kami Section (BARU) -->
    <section id="keunggulan-kami" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Mengapa Memilih Kami?</h2>
                <p class="text-muted">Kami lebih dari sekedar distributor. Kami adalah mitra pertumbuhan bisnis Anda.</p>
            </div>
            <div class="row text-center g-4">
                <!-- Keunggulan 1: Kualitas -->
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 p-4">
                        <div class="feature-icon mx-auto">
                            <i class="fa-solid fa-award"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Kualitas Terjamin</h5>
                        <p class="card-text">Kami hanya bekerja sama dengan produsen terkemuka untuk memastikan produk yang Anda terima memiliki kualitas terbaik.</p>
                    </div>
                </div>
                <!-- Keunggulan 2: Keandalan -->
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 p-4">
                        <div class="feature-icon mx-auto">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Pengiriman Tepat Waktu</h5>
                        <p class="card-text">Dengan sistem logistik modern, kami menjamin pesanan Anda tiba sesuai jadwal untuk menjaga kelancaran operasional Anda.</p>
                    </div>
                </div>
                <!-- Keunggulan 3: Pelayanan -->
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 p-4">
                        <div class="feature-icon mx-auto">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Layanan Pelanggan</h5>
                        <p class="card-text">Tim kami siap membantu Anda dengan responsif untuk setiap pertanyaan, pesanan, dan kebutuhan spesifik Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Produk Unggulan (Carousel) Section -->
    <section id="produk-unggulan" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Produk Unggulan Kami</h2>
                <p class="text-muted">Pilihan terbaik untuk kebutuhan bisnis kuliner Anda.</p>
            </div>
            <?php if (!empty($slides)): ?>
            <div id="productCarousel" class="carousel slide shadow-lg" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($slides as $index => $slide): ?>
                        <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index == 0 ? 'active' : ''; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner rounded">
                    <?php foreach ($slides as $index => $slide): ?>
                    <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                        <img src="assets/images/carousel/<?php echo htmlspecialchars($slide['image_path']); ?>" class="d-block w-100" style="height: 500px; object-fit: cover;" alt="<?php echo htmlspecialchars($slide['title']); ?>">
                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                            <h5 class="fw-bold"><?php echo htmlspecialchars($slide['title']); ?></h5>
                            <p><?php echo htmlspecialchars($slide['caption']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
            <?php else: ?>
                <div class="alert alert-info text-center"><p class="mb-0">Saat ini belum ada produk unggulan yang ditampilkan.</p></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action (CTA) Section (BARU) -->
    <section id="cta" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <h2 class="fw-bold">Siap Meningkatkan Kualitas Pasokan Anda?</h2>
                    <p class="fs-5 my-4">Jadikan AAC Freshmart sebagai mitra andalan Anda. Hubungi kami hari ini untuk konsultasi dan dapatkan penawaran terbaik untuk bisnis Anda.</p>
                    <a href="#" class="btn btn-light btn-lg fw-bold">
                        <i class="fa-solid fa-phone-volume"></i> Hubungi Kami Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row">
                <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold">AAC Freshmart</h6>
                    <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #0d6efd; height: 2px"/>
                    <p>Perusahaan distributor frozen food yang melayani pengiriman ke seluruh area dengan mengutamakan kualitas produk dan ketepatan waktu.</p>
                </div>
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold">Tautan</h6>
                     <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #0d6efd; height: 2px"/>
                    <p><a href="#hero" class="text-white-50 text-decoration-none">Beranda</a></p>
                    <p><a href="#tentang-kami" class="text-white-50 text-decoration-none">Tentang Kami</a></p>
                    <p><a href="#produk-unggulan" class="text-white-50 text-decoration-none">Produk</a></p>
                    <p><a href="login.php" class="text-white-50 text-decoration-none">Login</a></p>
                </div>
                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold">Tautan Sosial</h6>
                    <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #0d6efd; height: 2px"/>
                    <div>
                        <a href="#" class="footer-social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="footer-social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="footer-social-link"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-md-0 mb-4">
                    <h6 class="text-uppercase fw-bold">Hubungi Kami</h6>
                     <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #0d6efd; height: 2px"/>
                    <p><i class="fas fa-home me-3"></i> Jl. Industri Raya No. 1, Jakarta</p>
                    <p><i class="fas fa-envelope me-3"></i> info@aacfreshmart.com</p>
                    <p><i class="fas fa-phone me-3"></i> +62 21 1234 5678</p>
                </div>
            </div>
        </div>
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2)">
            © <?php echo date('Y'); ?> AAC Freshmart. All Rights Reserved.
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>