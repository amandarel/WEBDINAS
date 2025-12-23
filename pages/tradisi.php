<?php
// =======================================================
// PORTAL WARISAN BUDAYA MINAHASA - Halaman Tunggal Lengkap
// File ini berfungsi sebagai daftar koleksi warisan
// =======================================================

function getWarisanBudayaPopuler() {
    // Gabungan semua item yang memiliki detail lengkap untuk diperingkat
    $all_items = array_merge(
        getAllTradisiBudaya(),
        getAllPakaianAdatMinahasa(), 
        getAllMusikTradisional(), 
        getAllLaguDaerah(),
        getAllSitusBudaya()
    );

    // --- SIMULASI PEMERINGKATAN POPULER ---
    // Di sini kita akan menggunakan properti internal (misalnya, ID atau nama) 
    // untuk menciptakan urutan yang berbeda dari urutan penggabungan,
    // yang mensimulasikan "popularitas"
    
    $ranked_items = array_map(function($item, $index) {
        
        // Inisialisasi properti dasar
        $item['rank'] = $index + 1; // Rank sementara
        
        // Pastikan ada image_url untuk template ranking
        if (!isset($item['image_url'])) {
             // Jika item dari musik/lagu yang tidak punya image, gunakan placeholder default
             $item['image_url'] = 'https://placehold.co/400x250/333333/ffffff?text=' . urlencode($item['nama'] ?? $item['Judul_Lagu'] ?? 'Budaya');
        }
        
        // Tambahkan kategori jika belum ada
        if (!isset($item['kategori'])) {
             $item['kategori'] = 'Warisan Budaya';
        }

        // Tambahkan skor acak internal (yang akan dihapus setelah pengurutan)
        // Ini hanya untuk keperluan simulasi pengurutan yang berbeda
        $item['internal_score'] = mt_rand(1, 10000); 

        return $item;
    }, $all_items, array_keys($all_items));
    
    // Urutkan berdasarkan 'internal_score' tertinggi (Simulasi popularitas)
    usort($ranked_items, function($a, $b) {
        return (int)$b['internal_score'] <=> (int)$a['internal_score'];
    });
    
    // Perbarui rank setelah diurutkan, dan hapus 'internal_score'
    $ranked_items = array_map(function($item, $index) {
        $item['rank'] = $index + 1;
        unset($item['internal_score']); // Hapus skor internal
        return $item;
    }, $ranked_items, array_keys($ranked_items));


    return array_slice($ranked_items, 0, 8); // Tampilkan hanya 8 teratas
}


// =======================================================
// 1. DATA GATHERING (Mengambil semua data untuk semua kategori)
// =======================================================
$data_sections = [
    'peringkat' => [
        'nama' => 'Warisan Budaya Populer',
        'ikon' => 'bi-award',
        'data' => getWarisanBudayaPopuler(), 
        'deskripsi' => 'Warisan Budaya Benda dan Tak Benda Minahasa yang paling banyak dicari.',
        'template' => 'ranking',
    ],
    'tarian' => [
        'nama' => 'Tarian & Tradisi Daerah',
        'ikon' => 'bi-person-badge',
        'data' => getAllTradisiBudaya(), 
        'deskripsi' => 'Tarian daerah mencerminkan nilai-nilai budaya, keberanian, spiritualitas, serta kebernasamaan masyarakat lokal.',
        'template' => 'grid',
    ],
    'baju' => [
        'nama' => 'Pakaian Adat',
        'ikon' => 'bi-suit-diamond',
        'data' => getAllPakaianAdatMinahasa(), 
        'deskripsi' => 'Pakaian adat daerah mencerminkan identitas budaya, nilai historis, serta status sosial dalam masyarakat tradisional.',
        'template' => 'grid',
    ],
    'situs' => [
        'nama' => 'Situs Budaya & Sejarah',
        'ikon' => 'bi-building',
        'data' => getAllSitusBudaya(),
        'deskripsi' => 'Waruga, Benteng, dan Cagar Budaya lainnya yang menjadi saksi bisu kejayaan dan adat leluhur Minahasa.',
        'template' => 'grid',
    ],
    'musik' => [
        'nama' => 'Musik Tradisional',
        'ikon' => 'bi-ear-fill',
        'data' => getAllMusikTradisional(),
        'deskripsi' => 'Musik Tradisional seperti Kolintang, Bambu Melulu, dan Gong Wayer. Jelajahi keindahan suara Minahasa.',
        'template' => 'audio_grid', 
    ],
    'lagu' => [
        'nama' => 'Lagu Daerah',
        'ikon' => 'bi-music-note-beamed',
        'data' => getAllLaguDaerah(), 
        'deskripsi' => 'Koleksi Lagu daerah Minahasa seperti O Ina Ni Keke, Si Patokaan, dan Esa Mokan. Simbol kekeluargaan dan kerinduan.',
        'template' => 'audio_grid', 
    ],
    // Galeri Budaya Visual telah dihapus
];

// Atur Judul Halaman
$page_title = 'Portal Budaya Minahasa Lengkap';
$halaman_judul = 'Portal Warisan Budaya Minahasa';
$halaman_deskripsi = 'Jelajahi keanekaragaman budaya Minahasa dalam satu halaman terpadu.';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- AOS Library CSS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    
    <style>
    /* --- STYLES KHAS UNTUK PORTAL BUDAYA --- */
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f7f9fb; /* Latar belakang yang lebih cerah */
    }
    /* Warna Merah Khas Minahasa (Simulasi Merah Lengan Kabasaran) */
    .bg-merah-aksi { background-color: #A32929 !important; }
    .text-merah-aksi { color: #A32929 !important; }
    .text-dark-text { color: #333333 !important; }

    /* Navigasi Cepat (Anchor Links) */
    .nav-pills-custom .nav-link {
        color: #333;
        background-color: #eee;
        margin-right: 10px;
        border-radius: 10px;
        font-weight: 600;
        border: 1px solid #ddd;
        transition: all 0.3s;
        white-space: nowrap; /* Penting untuk overflow-auto */
        padding: 8px 15px;
    }
    .nav-pills-custom .nav-link:hover {
        background-color: #EFEFEF;
        color: #A32929;
    }
    .nav-pills-custom .nav-link.active {
        background-color: #A32929;
        color: white;
        border-color: #A32929;
        box-shadow: 0 4px 10px rgba(163, 41, 41, 0.3);
    }
    .nav-pills-custom {
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
    }
    .nav-pills-custom::-webkit-scrollbar {
        display: none; /* Chrome, Safari and Opera */
    }

    /* Style untuk Card Item */
    .warisan-card {
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%; /* Penting untuk h-100 */
        border-radius: 12px;
    }
    .warisan-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2) !important; /* Bayangan lebih kuat saat hover */
    }
    .img-hover-zoom {
        transition: transform 0.5s ease-in-out;
    }
    .warisan-card:hover .img-hover-zoom {
        transform: scale(1.05);
    }
    .rank-badge {
        z-index: 10;
        font-size: 1.2rem;
        width: 45px; 
        height: 45px; 
        font-weight: 700;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }

    /* Media Card (untuk Lagu/Musik) */
    .media-card {
        background-color: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
    }
    
    /* Media Query untuk audio_grid agar lebih kompak di mobile */
    @media (max-width: 576px) {
        .media-card .card-body {
            padding: 1rem;
        }
        .media-card h5 {
            font-size: 1rem;
        }
        .media-card audio {
            height: 40px;
        }
    }


    </style>
</head>
<body>

<!-- HEADER SECTION -->
<section id="portal-header" class="bg-light py-5 mb-5 shadow-sm" style="background-color: #f7f9fb;">
    <div class="container" data-aos="fade-down" data-aos-duration="1000">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="display-5 fw-bolder text-dark-text border-bottom border-merah-aksi pb-2 mb-3">
                    <?= $halaman_judul ?>
                </h1>
                <p class="lead mb-4 text-muted"><?= $halaman_deskripsi ?></p>
                
                <!-- Navigasi Cepat (Anchor Links) -->
                <h4 class="fw-bold mb-3 mt-5 text-dark-text">Telusuri Berdasarkan Kategori:</h4>
                <div class="nav-pills-custom mb-4 overflow-auto pb-2" data-aos="fade-right" data-aos-delay="200">
                    <ul class="nav nav-pills flex-nowrap">
                        <?php foreach ($data_sections as $key => $section): ?>
                            <li class="nav-item flex-shrink-0">
                                <a class="nav-link" href="#kategori-<?= $key ?>">
                                    <i class="bi <?= $section['ikon'] ?> me-2"></i> <?= $section['nama'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- MAIN CONTENT: LOOPING SEMUA SECTION -->
<section id="all-sections" class="container py-3">

    <?php 
    // Array untuk mengatur urutan dan jenis animasi AOS untuk setiap kategori
    $aos_effects = [
        'peringkat' => 'fade-up',
        'tarian' => 'fade-up',
        'baju' => 'fade-up',
        'situs' => 'fade-up',
        'musik' => 'fade-up',
        'lagu' => 'fade-up',
    ];
    $delay = 0; // Inisialisasi delay
    ?>

    <?php foreach ($data_sections as $key => $section): ?>
        <?php 
        $aos_type = $aos_effects[$key] ?? 'fade-up';
        $delay += 200; // Tambahkan delay untuk setiap kategori
        ?>
        <div id="kategori-<?= $key ?>" class="mb-5 pt-4 border-top" data-aos="<?= $aos_type ?>" data-aos-duration="1000" data-aos-once="true">
            <!-- PUSATKAN JUDUL DAN DESKRIPSI KATEGORI -->
            <div class="text-center"> 
                <h2 class="fw-bolder text-dark-text mb-2 pt-3">
                    <i class="bi <?= $section['ikon'] ?> me-2 text-merah-aksi"></i> <?= $section['nama'] ?>
                </h2>
                <p class="lead text-muted mb-4 small mx-auto" style="max-width: 700px;"><?= $section['deskripsi'] ?></p>
            </div>

            <?php if (empty($section['data'])): ?>
                <div class="alert alert-info text-center rounded-3 shadow-sm" data-aos="zoom-in" data-aos-delay="100">
                    Data **<?= $section['nama'] ?>** belum tersedia saat ini.
                </div>
            <?php else: ?>
                
                <!-- TEMPLATE: RANKING (Peringkat Budaya) -->
                <?php if ($section['template'] === 'ranking'): ?>
                    <div class="row g-4 justify-content-center">
                        <?php $item_delay = 0; foreach ($section['data'] as $item): $item_delay += 100; ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" data-aos="fade-up" data-aos-delay="<?= $item_delay ?>">
                            <div class="card warisan-card shadow-lg overflow-hidden text-center border-0">
                                <div class="position-relative overflow-hidden">
                                    <img src="<?= $item['image_url'] ?? 'https://placehold.co/400x250/333333/ffffff?text=Budaya' ?>" class="card-img-top img-hover-zoom" alt="<?= $item['nama'] ?>" style="height: 180px; object-fit: cover;"> 
                                    <span class="badge bg-merah-aksi position-absolute top-0 end-0 m-2 fw-bold rounded-pill">
                                        <?= $item['kategori'] ?? 'Item Budaya' ?>
                                    </span>
                                    <span class="rank-badge bg-warning text-dark fw-bolder rounded-circle d-flex align-items-center justify-content-center position-absolute top-0 start-0 m-2 shadow">
                                        <?= $item['rank'] ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title text-dark-text fw-bold mb-1 text-truncate" title="<?= $item['nama'] ?>"><?= $item['nama'] ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    
                <!-- TEMPLATE: STANDARD GRID (Situs, Tarian, Baju Adat) - DENGAN KETERANGAN LENGKAP -->
                <?php elseif ($section['template'] === 'grid'): ?>
    <div class="row g-4 justify-content-center">
        <?php $item_delay = 0; foreach ($section['data'] as $item): $item_delay += 150; 
            // Pastikan variabel ada
            $deskripsi_lengkap = $item['deskripsi_lengkap'] ?? 'Keterangan lengkap belum tersedia.';
            $lokasi = $item['lokasi'] ?? 'N/A';
            $tahun = $item['tahun_pencatatan'] ?? 'N/A';
            $koordinat = $item['koordinat'] ?? 'N/A'; // Koordinat hanya ada untuk Situs
            
            // Tentukan kategori item. Jika kategori tidak ada di $item, gunakan nama seksi sebagai fallback.
            $kategori = $item['kategori'] ?? $section['nama'];
        ?>
        <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $item_delay ?>">
            <div class="card warisan-card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="position-relative overflow-hidden">
                    <img src="<?= $item['image_url'] ?? 'https://placehold.co/600x400/808080/ffffff?text=Item' ?>" class="card-img-top img-hover-zoom" alt="<?= $item['nama'] ?>" style="height: 220px; object-fit: cover;"> 
                    <span class="badge bg-merah-aksi position-absolute top-0 start-0 m-3 fw-bold rounded-pill">
                        <?= $kategori ?>
                    </span>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-dark-text fw-bold mb-2"><?= $item['nama'] ?></h5>
                    
                    <!-- FAKTA SINGKAT: Lokasi dan Tahun -->
                    <div class="d-flex justify-content-between small text-muted mb-3 border-bottom pb-2">
                        <span class="d-block"><i class="bi bi-geo-alt-fill text-merah-aksi me-1"></i> Lokasi: <span class="fw-semibold text-dark-text"><?= $lokasi ?></span></span>
                        <span class="d-block"><i class="bi bi-calendar-check-fill text-merah-aksi me-1"></i> Tahun: <span class="fw-semibold text-dark-text"><?= $tahun ?></span></span>
                    </div>
                        
                        <h6 class="text-dark-text fw-bold mt-2 mb-2 border-top pt-2">Detail Budaya:</h6>
                        <!-- DESKRIPSI PANJANG (Menggunakan deskripsi_lengkap) -->
                            <p class="card-text small text-dark-text mb-0">
                                    <?= $deskripsi_lengkap ?>
                            </p>
                    
                    <!-- BLOK KOORDINAT HANYA UNTUK SITUS BUDAYA -->
                    <?php if ($key === 'situs' && $koordinat !== 'N/A'): 
                        // Pisahkan koordinat menjadi Latitude dan Longitude
                        // Menghindari error jika format koordinat tidak seperti yang diharapkan
                        $coords = explode(', ', $koordinat);
                        $latitude = trim($coords[0] ?? '0');
                        $longitude = trim($coords[1] ?? '0');
                        
                        // Buat URL Google Maps
                        $map_url = "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
                    ?>
                        <div class="d-flex justify-content-between small text-muted mt-3 pt-2 border-top">
                            <!-- Tampilkan Koordinat -->
                            <span class="d-block">
                                <i class="bi bi-compass-fill text-merah-aksi me-1"></i> Koordinat: 
                                <span class="fw-semibold text-dark-text"><?= $koordinat ?></span>
                            </span>
                            
                            <!-- Tampilkan Tautan ke Peta -->
                            <span class="d-block">
                                <a href="<?= $map_url ?>" target="_blank" class="text-merah-aksi fw-semibold hover:text-red-700 text-decoration-none">
                                    <i class="bi bi-map-fill me-1"></i> Lihat di Peta
                                </a>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
                
                <?php elseif ($section['template'] === 'audio_grid'): ?>
                    <div class="row g-4 justify-content-center">
                        <?php $item_delay = 0; foreach ($section['data'] as $item): $item_delay += 150; ?>
                        <?php
                            // 1. Ambil URL, prioritaskan link_youtube, lalu Link_Audio
                            $rawYoutubeUrl = sanitize_external_url($item['link_youtube'] ?? $item['Link_Audio'] ?? null);

                            $isYoutube = $rawYoutubeUrl && (strpos($rawYoutubeUrl, 'youtube.com') !== false || strpos($rawYoutubeUrl, 'youtu.be') !== false);
                            
                            // Tentukan URL eksternal (link yang akan dibuka di browser)
                            $externalUrl = $isYoutube ? $rawYoutubeUrl : ($item['Link_Audio'] ?? '#');

                            // 2. Konversi ke URL Embed (Tidak digunakan di sini, tapi dipertahankan jika ingin ditambahkan lagi)
                            $embedUrl = $rawYoutubeUrl;
                            if ($isYoutube && strpos($rawYoutubeUrl, 'watch?v=') !== false) {
                                $embedUrl = str_replace('watch?v=', 'embed/', $rawYoutubeUrl);
                                $embedUrl = strtok($embedUrl, '&');
                            }

                            // Ambil deskripsi. 
                            // Ini akan digunakan untuk deskripsi singkat pada kartu (card).
                            $deskripsi_lengkap = $item['deskripsi_lengkap'] ?? 'Keterangan lengkap belum tersedia.';

                            // Ambil Judul
                            $judulItem = htmlspecialchars($item['Judul_Lagu'] ?? $item['nama'] ?? 'Judul Tidak Diketahui');
                        ?>
                        <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $item_delay ?>">
                            <div class="card media-card warisan-card shadow-sm h-100 border-0">
                                <div class="position-relative overflow-hidden">
                                    <!-- Gambar/visual cover -->
                                    <img src="<?= $item['image_url'] ?? 'https://placehold.co/600x400/555555/ffffff?text=Item+Media' ?>" class="card-img-top img-hover-zoom" alt="<?= $judulItem ?>" style="height: 220px; object-fit: cover;"> 
                                    <span class="badge bg-merah-aksi position-absolute top-0 start-0 m-3 fw-bold rounded-pill">
                                        <?= htmlspecialchars($item['kategori'] ?? $section['nama']) ?>
                                    </span>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    
                                    <!-- Judul Utama -->
                                    <h5 class="card-title text-dark-text fw-bold mb-2 text-merah-aksi">
                                        <i class="bi bi-mic-fill me-2"></i> <?= $judulItem ?>
                                    </h5>
                                    
                                    <!-- KETERANGAN LENGKAP -->
                                    <h6 class="text-dark-text fw-bold mt-2 mb-2 border-top pt-2">Detail Media:</h6>
                                    <p class="card-text small text-dark-text mb-0">
                                            <?= $deskripsi_lengkap ?>
                                    </p>
                                    
                                    <!-- Audio Player atau Tombol YouTube -->
                                    <div class="mt-auto pt-3 border-top">
                                        <!-- Tautan ke YouTube (Jika ada link YouTube) -->
                                        <?php if ($isYoutube): ?>
                                            <a href="<?= $rawYoutubeUrl ?>" target="_blank" class="btn btn-sm btn-danger w-100 mb-2 rounded-pill">
                                                <i class="bi bi-youtube me-1"></i> Tonton di YouTube
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Placeholder Audio Player (Jika non-YouTube, tetapi ada Link Audio) -->
                                        <?php if (!$isYoutube && ($item['Link_Audio'] ?? false) && $externalUrl !== '#'): ?>
                                            <audio controls class="w-100 mb-2 rounded-3">
                                                <source src="<?= sanitize_external_url($item['Link_Audio']) ?>" type="audio/mp3">
                                                Browser Anda tidak mendukung elemen audio.
                                            </audio>
                                        <?php endif; ?>

                                        <?php if (!$isYoutube && $externalUrl === '#'): ?>
                                            <div class="alert alert-warning p-2 small" role="alert">
                                                Tidak ada tautan media tersedia.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>


                <!-- TEMPLATE: GALLERY MASONRY - DIHAPUS (sesuai permintaan user) -->

                <?php endif; // End Template Check ?>

            <?php endif; ?>

            <!-- Link kembali ke atas -->
            <div class="text-center mt-5" data-aos="zoom-in" data-aos-delay="200">
                <a href="#portal-header" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-up-circle-fill me-1"></i> Kembali ke Atas
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</section>


<!-- AOS Library JS -->
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
    // Inisialisasi AOS setelah semua elemen dimuat
    document.addEventListener('DOMContentLoaded', (event) => {
        // Hapus penundaan kecil (minimal 100ms) untuk memastikan DOM siap
        setTimeout(() => {
            AOS.init({
                // Opsional: setel properti global di sini
                offset: 120, // jarak (dalam px) dari elemen asli yang memicu dimulainya animasi
                delay: 0, // penundaan animasi (dalam ms)
                duration: 1000, // durasi animasi (dalam ms)
                easing: 'ease-in-out', // easing untuk animasi AOS
                once: true, // apakah animasi hanya boleh terjadi sekali
                mirror: false, // apakah elemen harus beranimasi keluar saat scroll ke atas
            });
        }, 100);
    });
</script>

</body>
</html>