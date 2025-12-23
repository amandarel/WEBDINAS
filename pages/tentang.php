<?php 
// =======================================================
// PAGES/TENTANG.PHP - Halaman Informasi Institusi
// =======================================================

// Atur Judul Halaman
$page_title = 'Tentang Kami - Disbudpar Minahasa';

// Tentukan warna utama (Merah Aksi) dan warna latar belakang (Dark Minahasa)
// Catatan: Variabel PHP ini akan digunakan di dalam HTML.
$merah_aksi = 'text-merah-aksi';
$dark_minahasa = 'bg-dark-minahasa'; 
$bg_merah_aksi = 'bg-merah-aksi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- FIX: Memuat Bootstrap CSS (Wajib untuk kelas seperti .container, .row, .card) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FIX: Memuat Bootstrap Icons CSS (Wajib untuk ikon bi-...) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <!-- AOS (Animate On Scroll) STYLESHEET -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Style untuk Mendefinisikan Warna PHP (FIX TATA LETAK/WARNA) -->
    <style>
        /* Menggunakan font sistem dan memastikan tampilan responsif */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; }
        .merah-aksi { color: #dc3545; } /* Merah yang tegas */
        .bg-merah-aksi { background-color: #dc3545 !important; }
        .dark-minahasa { background-color: #343a40 !important; } /* Hitam/Abu-abu gelap */
        .text-dark-text { color: #212529 !important; } /* Teks gelap */
        .border-merah-aksi { border-color: #dc3545 !important; }
        .shadow-lg { box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important; }
        .card { transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.25) !important; }
    </style>
</head>
<body>

<!-- Kontainer Utama Halaman Tentang Kami -->
<section id="tentang-kami" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                
                <!-- HEADER - Judul Utama (AOS: Fade-down) -->
                <h1 class="display-5 fw-bold text-dark-text border-bottom border-merah-aksi pb-2 mb-4" data-aos="fade-down">Tentang Dinas Kebudayaan dan Pariwisata Minahasa</h1>
                
                <!-- Bagian Pengantar & Gambar Ilustrasi (AOS: Fade-up) -->
                <div class="row mb-5 align-items-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="col-md-7">
                        <p class="lead text-muted">Kami adalah lembaga pemerintah yang berdedikasi untuk menjaga, melestarikan, dan mempromosikan kekayaan budaya serta potensi pariwisata yang tak ternilai dari Minahasa.</p>
                        <p>Dengan semangat "I Yayat U Santi", kami bekerja bersama masyarakat, budayawan, dan pelaku industri pariwisata untuk menjadikan Minahasa sebagai destinasi unggulan di Sulawesi Utara, yang berlandaskan pada nilai-nilai luhur adat dan kearifan lokal.</p>
                    </div>
                    <div class="col-md-5 text-center" data-aos="fade-up" data-aos-delay="300">
                        <!-- Placeholder Foto Tim atau Kantor -->
                        <img 
                            src="assets/images/dinas3.jpg"
                            onerror="this.onerror=null;this.src='https://placehold.co/400x300/495057/ffffff?text=Kantor+Disbudpar';"
                            alt="Foto Dinas Kebudayaan dan Pariwisata Minahasa" 
                            class="img-fluid rounded shadow-lg border border-2 border-primary"
                        >
                    </div>
                </div>

                <!-- Bagian VISI - Dibungkus dalam Card yang Menonjol (AOS: Flip-left) -->
                <div class="mb-5 card shadow-lg border-start border-5 border-merah-aksi" data-aos="fade-up" data-aos-duration="1000">
                    <div class="card-body p-5">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-3 rounded-circle me-3 <?php echo $bg_merah_aksi; ?> text-white" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-eye-fill fs-5"></i>
                            </div>
                            <h3 class="card-title mb-0 fw-bold <?php echo $merah_aksi; ?>">Visi Kami</h3>
                        </div>
                        <blockquote class="blockquote lead fst-italic mt-3 mb-0 text-dark">Minahasa Daerah Pariwisata yang Maju dan Sejahtera.</blockquote>
                    </div>
                </div>

                <!-- Bagian MISI - Layout Grid dengan Ikon Berwarna -->
                <div class="mb-5">
                    <h3 class="fw-bold mb-4 border-bottom pb-2 text-dark" data-aos="fade-up">Misi Kami</h3>
                    <div class="row g-4">
                        
                        <!-- Misi 1 (AOS: Zoom-in) -->
                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                            <div class="card h-100 p-3 shadow-sm border-top border-4 border-primary hover-lift">
                                <div class="card-body">
                                    <i class="bi bi-book-half fs-3 text-primary mb-3 d-block"></i>
                                    <h5 class="fw-bold">1. Meningkatkan SDM Minahasa</h5>
                                    <p class="card-text small text-muted">Meningkatkan kualitas SDM Minahasa yang sehat, cerdas, berbudaya dan berdaya tangguh.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Misi 2 (AOS: Zoom-in) -->
                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                            <div class="card h-100 p-3 shadow-sm border-top border-4 border-success hover-lift">
                                <div class="card-body">
                                    <i class="bi bi-compass fs-3 text-success mb-3 d-block"></i>
                                    <h5 class="fw-bold">2. Meningkatka Pembangunan & Ekonomi</h5>
                                    <p class="card-text small text-muted">Meningkatkan pembangunan infrastruktur berwawasan lingkungan untuk mewujudkan ekonomi yag tangguh.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Misi 3 (AOS: Zoom-in) -->
                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                            <div class="card h-100 p-3 shadow-sm border-top border-4 border-warning hover-lift">
                                <div class="card-body">
                                    <i class="bi bi-people-fill fs-3 text-warning mb-3 d-block"></i>
                                    <h5 class="fw-bold">3. Tata Kelola Pemerintahan</h5>
                                    <p class="card-text small text-muted">Memantapkan tata kelola pemerintahan yang akuntabel berbasis digitalisasi dan inovatif untuk mewujudkan pelayanan publik yang prima.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian TUGAS POKOK - Kotak Informasi Ringkas (AOS: Fade-left) -->
                <div class="mt-5 p-4 rounded-3 shadow border border-info" data-aos="fade-up" data-aos-duration="1000">
                    <h3 class="text-info fw-bold border-bottom pb-2 mb-3"><i class="bi bi-briefcase-fill me-2"></i>Tugas Pokok</h3>
                    <p class="lead mb-2">Dinas Kebudayaan dan Pariwisata bertugas membantu Bupati melaksanakan urusan pemerintahan di bidang kebudayaan dan bidang pariwisata yang menjadi kewenangan daerah.</p>
                    <ul class="list-unstyled small text-muted mt-3">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Perencanaan program dan anggaran kebudayaan dan pariwisata.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Pelaksanaan pelestarian situs, tradisi, dan warisan budaya.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Pengembangan destinasi dan promosi pariwisata daerah.</li>
                    </ul>
                </div>
                
                <!-- Bagian Struktur Organisasi (AOS: Fade-up-right) -->
                <div class="card shadow-lg mt-5 border border-dark" data-aos="fade-up" data-aos-duration="1000">
                    <div class="card-header <?php echo $dark_minahasa; ?> text-white p-3">
                        <h3 class="card-title mb-0 fw-bold"><i class="bi bi-diagram-3-fill me-2"></i>Struktur Organisasi</h3>
                    </div>
                    <div class="card-body p-4">
                        <p class="card-text text-muted">Diagram berikut menampilkan bagan struktural resmi Dinas Kebudayaan dan Pariwisata Kabupaten Minahasa.</p>
                        
                        <!-- Placeholder Struktur Organisasi -->
                        <div class="text-center p-4 bg-light rounded border border-secondary border-opacity-25 mt-3 overflow-auto" style="max-height: 600px;">
                            <img 
                                src="assets/images/strukturorganisasi.jpg"
                                onerror="this.onerror=null;this.src='https://placehold.co/800x400/343a40/ffffff?text=Struktur+Organisasi';"
                                alt="Diagram Struktur Organisasi Dinas Kebudayaan dan Pariwisata Minahasa" 
                                class="img-fluid rounded shadow-sm w-100"
                            >
                            <small class="text-muted mt-2 d-block">Anda dapat mengganti gambar di atas dengan bagan struktur yang sebenarnya.</small>
                        </div>
                    </div>
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
            // Pengaturan default AOS
            duration: 800, // Durasi animasi dalam ms
            once: true,    // Apakah animasi hanya dimainkan sekali saat scrolling ke bawah?
            disable: function() {
                // Nonaktifkan AOS pada perangkat mobile untuk performa yang lebih baik (opsional)
                return window.matchMedia("(max-width: 768px)").matches;
            }
        });
    });
</script>
</body>
</html>