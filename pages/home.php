<?php 
// =======================================================
// PAGES/HOME.PHP - KONTEN BERANDA (Fokus Budaya - Desain Beragam)
// =======================================================

// Pastikan ROOT_PATH sudah terdefinisi di index.php
require_once ROOT_PATH . '/includes/functions.php'; 
$page_title = 'Beranda - Warisan Budaya Minahasa';

?>
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<!-- PENTING: Tambahkan STYLE untuk perbaikan pemotongan footer, 
     menghilangkan garis bawah footer, dan style baru untuk fitur utama. -->
<style>
/* CSS khusus untuk Hero Section di halaman Beranda */
.hero-slide-bg {
    /* Gunakan 70% dari tinggi layar untuk memastikan footer memiliki ruang yang cukup */
    height: 70vh; 
    min-height: 500px; /* Tinggi minimum untuk desktop */
    background-size: cover;
    background-position: center center;
    position: relative; 
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px;
    color: white; 
    /* Efek overlay agar teks H1 dan P terbaca jelas di atas gambar */
    box-shadow: inset 0 0 0 1000px rgba(0, 0, 0, 0.4); 
}

.hero-overlay-content {
    z-index: 10;
    max-width: 800px;
    padding: 0 15px;
}

/* =========================================================
PERBAIKAN GARIS BAWAH FOOTER (TEXT-DECORATION: NONE)
========================================================= */
footer a {
    text-decoration: none !important; /* Menghilangkan garis bawah default */
    color: inherit; /* Memastikan warna tetap putih atau warna pewarisan footer */
    transition: color 0.3s ease; /* Transisi halus saat hover */
}

/* Efek hover opsional */
footer a:hover {
    color: #ffc107; /* Contoh: Warna kuning saat di-hover */
}

/* =========================================================
CSS BARU UNTUK FITUR UTAMA (Sesuai Permintaan Gambar)
========================================================= */
.feature-box {
    text-align: center;
    padding: 20px 15px;
    border-radius: 10px;
    /* Menggunakan background putih dan sedikit shadow untuk efek "mengambang" */
    background: white; 
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    font-size: 3rem; /* Ukuran ikon */
    color: #cc3333; /* Warna Merah Aksi Minahasa */
    margin-bottom: 15px;
}

/* Penyesuaian Responsif */
@media (max-width: 768px) {
    .hero-slide-bg {
        height: 50vh; 
        min-height: 350px; /* Tinggi minimum untuk ponsel */
    }
}
</style>

<!-- SECTION: HERO CAROUSEL -->
<section id="hero-carousel" class="mb-5">
<div id="minahasaCarousel" class="carousel slide" data-bs-ride="carousel" data-aos="fade-down" data-aos-duration="1000">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#minahasaCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#minahasaCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#minahasaCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        <button type="button" data-bs-target="#minahasaCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
    </div>

    <div class="carousel-inner rounded-0"> 
        <div class="carousel-item active" data-bs-interval="5000">
            <div class="hero-slide-bg" style="background-image: url('assets/images/waruga.jpg');">
                <div class="hero-overlay-content">
                    <h1 class="display-3 fw-bolder mb-3" data-aos="fade-up" data-aos-duration="1000">Jelajahi Warisan Budaya Minahasa</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">Temukan kekayaan adat, sejarah, dan pesona alam Minahasa.</p>
                </div>
            </div>
        </div>

        <div class="carousel-item" data-bs-interval="5000">
            <div class="hero-slide-bg" style="background-image: url('assets/images/kabasaran3.jpg');">
                <div class="hero-overlay-content">
                    <h1 class="display-3 fw-bolder mb-3" data-aos="fade-up" data-aos-duration="1000">Saksikan Tari Kabasaran</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">Ritual adat dan tarian perang yang menjadi identitas kebudayaan Minahasa.</p>
                </div>
            </div>
        </div>

        <div class="carousel-item" data-bs-interval="5000">
            <div class="hero-slide-bg" style="background-image: url('assets/images/tiang.jpg');">
                <div class="hero-overlay-content">
                    <h1 class="display-3 fw-bolder mb-3" data-aos="fade-up" data-aos-duration="1000">Pesona Cagar Budaya Minahasa</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">Nikmati keindahan Situs Budaya Minahasa.</p>
                </div>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#minahasaCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#minahasaCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
</section>


<section id="pimpinan-visimisi" class="py-5 position-relative bg-light">
    <div class="container">
    <h2 class="text-center fw-bolder display-5 text-dark-minahasa mb-5" data-aos="fade-up">Bupati dan Wakil Bupati Minahasa</h2>
        <div class="row align-items-center">
            <!-- Foto Bupati (KIRI) -->
            <div class="col-md-3 text-center mb-4 mb-md-0" data-aos="fade-right">
                <img src="assets/images/kepala.jpg" 
                     alt="Bupati Minahasa"
                     class="shadow rounded-4"
                     style="width: 180px; height: 240px; object-fit: cover; border: 4px solid white;">

                <!-- Nama dengan Latar Merah -->
                <div style="
                    background: #cc0000; 
                    color: white; 
                    padding: 8px 12px; 
                    margin-top: 10px; 
                    border-radius: 5px; 
                    font-weight: bold;
                ">
                    DR. ROBBY DONDOKAMBEY, S.SI, MAP
                </div>
            </div>

            <!-- VISI & MISI (TENGAH) -->
            <div class="col-md-6 text-center" data-aos="fade-up">
                <h3 class="fw-bold mb-3">Minahasa Daerah Pariwisata Yang Maju dan Sejahtera</h3>
            </div>

            <!-- Foto Wakil Bupati (KANAN) -->
            <div class="col-md-3 text-center mt-4 mt-md-0" data-aos="fade-left">
                <img src="assets/images/wakil.jpg" 
                     alt="Wakil Bupati Minahasa"
                     class="shadow rounded-4"
                     style="width: 180px; height: 240px; object-fit: cover; border: 4px solid white;">

                <!-- Nama dengan Latar Merah -->
                <div style="
                    background: #cc0000; 
                    color: white; 
                    padding: 8px 12px; 
                    margin-top: 10px; 
                    border-radius: 5px; 
                    font-weight: bold;
                ">
                    VANDA SARUNDAJANG, SS
                </div>
            </div>

        </div>

    </div>
</section>

<!-- SECTION: DINAS INFO (Diletakkan di sini untuk alur logis) -->
<section id="dinas-info" class="bg-dark-minahasa text-white py-5" data-aos="fade-up" data-aos-delay="100">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7 mb-4 mb-md-0" data-aos="fade-right" data-aos-duration="1000">
                <h3 class="fw-bolder display-6 mb-3">Dinas Kebudayaan dan Pariwisata</h3>
                <p class="lead mb-4">Bertanggung jawab dalam pelestarian warisan budaya dan pengembangan pariwisata Minahasa secara berkelanjutan.</p>
                <a href="index.php?page=tentang" class="btn btn-light btn-lg fw-bold text-dark-minahasa rounded-pill" data-aos="zoom-in" data-aos-delay="500">Pelajari Tugas Kami <i class="bi bi-arrow-right"></i></a>
            </div>
        <div class="col-md-5 text-center" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300">
            <img src="assets/images/dinas3.jpg" alt="Gedung Dinas Kebudayaan dan Pariwisata Minahasa" class="img-fluid rounded-3 shadow-lg" style="max-height: 250px; width: auto;" onerror="this.onerror=null; this.src='assets/images/placeholder.jpg';">
        </div>
        </div>
</div>
</section>

<!-- SECTION: WARISAN UTAMA -->
<section id="warisan-utama" class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center fw-bolder display-5 text-dark-minahasa mb-5" data-aos="fade-up">Jelajahi Warisan Utama</h2>
        
        <div class="row g-4 mb-5">
            
            <!-- Item 1: Tarian Adat - Tari Maengket (Kolom 1) -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 shadow border-0 rounded-4 overflow-hidden">
                    <div class="card-img-top object-fit-cover bg-primary-minahasa text-white d-flex align-items-center justify-content-center" style="height: 220px;">
                        <i class="bi bi-music-note-beamed display-1"></i>
                        <img src="assets/images/kabasaran4.jpg" alt="Tari Maengket" class="img-fluid h-100 w-100 object-fit-cover" style="opacity: 0.8;" onerror="this.onerror=null; this.src='https://placehold.co/400x220/333333/ffffff?text=Tari+Maengket';">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark-minahasa">Tari Kawasaran</h5>
                        <p class="card-text text-muted small"> Tarian Adat</p>
                        <p class="card-text text-sm">Tarian perang tradisional dari suku Minahasa di Sulawesi Utara, yang kini juga digunakan untuk penyambutan tamu penting dan acara adat lainnya. </p>
                    </div>
                </div>
            </div>

            <!-- Item 2: Cagar Budaya 1 - Waruga (Kubur Batu) (Kolom 2) -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 shadow border-0 rounded-4 overflow-hidden">
                    <div class="card-img-top object-fit-cover bg-success-minahasa text-white d-flex align-items-center justify-content-center" style="height: 220px;">
                        <i class="bi bi-bank2 display-1"></i>
                        <img src="assets/images/waruga.jpg" alt="Waruga Minahasa" class="img-fluid h-100 w-100 object-fit-cover" style="opacity: 0.8;" onerror="this.onerror=null; this.src='https://placehold.co/400x220/15803d/ffffff?text=Waruga';">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark-minahasa">Waruga (Kubur Batu)</h5>
                        <p class="card-text text-muted small"> Cagar Budaya</p>
                        <p class="card-text text-sm">Situs kuburan kuno Minahasa yang berbentuk batu berpenutup, di mana jenazah diletakkan dalam posisi jongkok menghadap utara. Melambangkan harapan untuk kembali ke asal.</p>
                    </div>
                </div>
            </div>

            <!-- Item 3: Cagar Budaya 2 - Watu Pinawetengan (Batu Pembelahan) (Kolom 3) -->
            <div class="col-lg-4 col-md-12" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 shadow border-0 rounded-4 overflow-hidden">
                    <div class="card-img-top object-fit-cover bg-success-minahasa text-white d-flex align-items-center justify-content-center" style="height: 220px;">
                        <i class="bi bi-geo-alt display-1"></i>
                        <img src="assets/images/watu.jpg" alt="Watu Pinawetengan" class="img-fluid h-100 w-100 object-fit-cover" style="opacity: 0.8;" onerror="this.onerror=null; this.src='https://placehold.co/400x220/15803d/ffffff?text=Watu+Pinawetengan';">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark-minahasa">Watu Pinawetengan</h5>
                        <p class="card-text text-muted small">Cagar Budaya</p>
                        <p class="card-text text-sm">Batu besar bersejarah yang dipercaya sebagai tempat musyawarah leluhur Minahasa untuk membagi wilayah adat. Situs sakral yang penting bagi identitas Tana Minahasa.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- SEKSI BARU: FITUR UTAMA (DIPINDAH KE SINI) -->
        <!-- ======================================================= -->
        <h3 class="text-center fw-bolder display-6 text-dark-minahasa mb-4 pt-5 border-top" data-aos="fade-up">Ragam Budaya Lainnya</h3>
        <div class="row g-4 justify-content-center mb-5">
            
            <!-- Fitur 1: Musik Tradisional Minahasa -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box h-100 shadow-sm border">
                    <div class="feature-icon mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-music-4"><path d="M9 18V5l12-2v13"/><path d="M15 17.5V12"/><circle cx="7" cy="18" r="4"/><circle cx="17" cy="16" r="4"/></svg>
                    </div>
                    <h5 class="fw-bold mb-3">Musik Tradisional</h5>
                    <p class="text-muted small">Mempelajari alat musik khas Minahasa seperti Kolintang, Bambu Melulu, dan Gong. Seni bunyi yang mengiringi ritual dan perayaan adat.</p>
                </div>
            </div>

            <!-- Fitur 2: Tari Adat Minahasa (Kabasaran/Maengket) -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box h-100 shadow-sm border">
                    <div class="feature-icon mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h5 class="fw-bold mb-3">Tari Adat Minahasa</h5>
                    <p class="text-muted small">Saksikan kegagahan Tari Kabasaran (Tari Perang) dan keindahan Tari Maengket yang sakral dan penuh makna historis.</p>
                </div>
            </div>

            <!-- Fitur 3: Situs & Adat Minahasa (Waruga/Pinawetengan) -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box h-100 shadow-sm border">
                    <div class="feature-icon mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scale"><path d="m12 6-3-3-2 3"/><path d="M18 6 9 3 2 6"/><path d="M2 18h20"/><path d="M20 12v6"/><path d="M4 12v6"/><path d="M16 12v6"/><path d="M8 12v6"/><path d="M12 12v6"/></svg>
                    </div>
                    <h5 class="fw-bold mb-3">Situs Cagar Budaya</h5>
                    <p class="text-muted small">Mengenal Waruga (Kubur Batu Kuno) dan Watu Pinawetengan, dua peninggalan sejarah yang menjadi akar kebudayaan Minahasa.</p>
                </div>
            </div>

            <!-- Fitur 4: Pakaian Adat Minahasa -->
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-box h-100 shadow-sm border">
                    <div class="feature-icon mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shirt"><path d="M20.38 3.46 16 2a4 4 0 0 1-4-4 4 4 0 0 1-4 4L3.62 3.46a2 2 0 0 0-2.3 2.3L2 10v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-9l.68-4.24a2 2 0 0 0-2.3-2.3z"/></svg>
                    </div>
                    <h5 class="fw-bold mb-3">Pakaian Adat</h5>
                    <p class="text-muted small">Lihat keindahan Baju Karai, Baniang, dan aksesoris adat yang dipakai dalam upacara, melambangkan status dan peran sosial.</p>
                </div>
            </div>

        </div>
        <!-- Akhir dari Fitur Utama yang Dipindah -->

        <!-- Tombol Lihat Semua (Dipertahankan di bagian paling bawah) -->
        <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="700">
            <a href="index.php?page=tradisi"
                class="btn btn-dark-minahasa btn-lg fw-bold rounded-pill shadow-lg"
                style="padding: 10px 40px;">
                Lihat Semua Warisan Budaya <i class="bi bi-arrow-right-circle-fill ms-2"></i>
            </a>
        </div>
    </div>
</section>

 <!-- AOS JS CDN -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        // Inisialisasi AOS setelah DOM dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Memastikan AOS diinisialisasi sekali dan setelah semua elemen dimuat
            AOS.init({
                // Opsional: atur properti default untuk semua animasi
                offset: 120, // jarak (dalam px) dari titik bawah viewport yang memicu animasi
                delay: 0, // tunda animasi (dalam ms)
                duration: 800, // durasi animasi (dalam ms)
                easing: 'ease', // jenis easing default
                once: true, // apakah animasi harus dijalankan hanya sekali (true) atau setiap kali elemen terlihat (false)
                mirror: false // apakah elemen harus beranimasi kembali saat digulir ke atas
            });
        });
    </script>
</body>
</html>