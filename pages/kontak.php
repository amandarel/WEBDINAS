<?php 
// =======================================================
// PAGES/KONTAK.PHP - Halaman Kontak (HANYA INFO STATIS)
// =======================================================

// Atur Judul Halaman
$page_title = 'Hubungi Kami - Disbudpar Minahasa';

// Data Kontak Statis
$kontak_data = [
    'alamat' => 'Kembuan, Kec. Tondano Utara, Kabupaten Minahasa, Sulawesi Utara',
    'telepon' => '(0431) 1234567',
    'email' => 'kiapotabuga@gmail.com',
    'jam_kerja' => 'Senin - Jumat: 08:00 - 16:30 WITA',
    'gambar_dinas' => 'assets/images/dinas3.jpg' 
];

// Catatan: Seluruh logika pemrosesan formulir (POST, sanitasi, pengiriman email) telah dihapus
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Memuat Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Memuat Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <!-- AOS (Animate On Scroll) STYLESHEET -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Style -->
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; }
        .text-merah-aksi { color: #dc3545 !important; } /* Merah yang tegas */
        .text-dark-text { color: #212529 !important; } /* Teks gelap */
        .shadow-text { text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); }
        .ratio-16x9 { transition: transform 0.5s ease-out; }
        .ratio-16x9:hover { transform: scale(1.01); }
        .contact-details li { transition: all 0.3s ease; cursor: default; padding: 5px; }
        .contact-details li:hover { background-color: #f1f1f1; padding-left: 10px; border-radius: 5px; }
    </style>
</head>
<body>

<section id="halaman-kontak" class="py-5">
    <!-- HEADER GAMBAR BANNER (AOS: Fade-down) -->
    <div class="header-kontak mb-5 position-relative" style="height: 300px; overflow: hidden;" data-aos="fade-down">
        <img 
            src="<?= $kontak_data['gambar_dinas'] ?>" 
            onerror="this.onerror=null;this.src='https://placehold.co/1200x300/dc3545/ffffff?text=Gedung+Disbudpar+Minahasa';"
            alt="Gedung Dinas Kebudayaan dan Pariwisata Minahasa" 
            class="img-fluid w-100 h-100 object-fit-cover"
        >
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: rgba(0, 0, 0, 0.4);">
            <h1 class="display-3 fw-bold text-white shadow-text">HUBUNGI KAMI</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="row g-4">
            
            <!-- KOLOM KIRI: Informasi Kontak (Diperluas untuk menggantikan Form) (AOS: Fade-right) -->
            <div class="col-lg-6 mb-5" data-aos="fade-right" data-aos-duration="1000">
                <h2 class="fw-bold text-merah-aksi mb-4">Dinas Kebudayaan dan Pariwisata Minahasa</h2>
                
                <p class="lead text-dark-text mb-4">
                    Kantor kami terbuka untuk kunjungan dan koordinasi selama jam kerja. Untuk pertanyaan umum atau kerjasama, silakan hubungi kontak utama di bawah ini.
                </p>
                
                <!-- Detail Kontak (AOS: Staggering) -->
                <ul class="list-unstyled contact-details mb-5 p-3 rounded shadow-sm bg-white border">
                    <li class="mb-4" data-aos="fade-right" data-aos-delay="200">
                        <i class="bi bi-geo-alt me-3 text-merah-aksi fs-4"></i> 
                        <span class="fw-bold d-block">Alamat Kantor:</span> 
                        <a href="https://maps.app.goo.gl/3Qk2Qp3P8t8Jk8vB8" target="_blank" class="text-decoration-none text-dark">
                            <?= $kontak_data['alamat'] ?> 
                            <i class="bi bi-box-arrow-up-right ms-2 small"></i>
                        </a>
                    </li>
                    <li class="mb-4" data-aos="fade-right" data-aos-delay="300">
                        <i class="bi bi-phone me-3 text-merah-aksi fs-4"></i> 
                        <span class="fw-bold d-block">Telepon:</span> 
                        <a href="tel:<?= $kontak_data['telepon'] ?>" class="text-decoration-none text-dark">
                            <?= $kontak_data['telepon'] ?>
                        </a>
                    </li>
                    <li class="mb-4" data-aos="fade-right" data-aos-delay="400">
                        <i class="bi bi-envelope me-3 text-merah-aksi fs-4"></i> 
                        <span class="fw-bold d-block">Email Resmi:</span> 
                        <a href="mailto:<?= $kontak_data['email'] ?>" class="text-decoration-none text-dark">
                            <?= $kontak_data['email'] ?>
                        </a>
                    </li>
                    <li class="mb-0" data-aos="fade-right" data-aos-delay="500">
                        <i class="bi bi-clock me-3 text-merah-aksi fs-4"></i> 
                        <span class="fw-bold d-block">Jam Layanan:</span> 
                        <?= $kontak_data['jam_kerja'] ?>
                    </li>
                </ul>
            </div>

            <!-- KOLOM KANAN: Peta Lokasi (AOS: Fade-left) -->
            <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000">
                <h2 class="fw-bold text-merah-aksi mb-4">Peta Lokasi Kantor</h2>
                
                <div class="ratio ratio-16x9 shadow-lg rounded">
                    <!-- Iframe Google Maps -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3287.6186011035184!2d124.90888438885499!3d1.3137832!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32871386c00e4d05%3A0x305da2760187373a!2sKantor%20Dinas%20Pariwisata%20dan%20Kebudayaan%20Kab.%20Minahasa!5e1!3m2!1sid!2sid!4v1764155600203!5m2!1sid!2sid" 
                            width="600" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ======================================================= -->
<!-- BOOTSTRAP JS DAN AOS (Animate On Scroll) SKRIP INISIALISASI -->
<!-- ======================================================= -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Inisialisasi AOS setelah halaman dimuat
    window.addEventListener('DOMContentLoaded', (event) => {
        AOS.init({
            duration: 800, 
            once: true,  
            disable: function() {
                // Nonaktifkan AOS pada perangkat mobile kecil untuk performa
                return window.matchMedia("(max-width: 600px)").matches;
            }
        });
    });
</script>
</body>
</html>