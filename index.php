<?php
// =======================================================
// INDEX.PHP - ENTRY POINT UTAMA
// =======================================================

// 1. Definisikan ROOT PATH (Wajib didefinisikan di awal)
define('ROOT_PATH', __DIR__); 

// 2. Panggil File Utama
// File functions.php akan memuat db_connect.php
require_once ROOT_PATH . '/includes/functions.php';

// 3. TENTUKAN HALAMAN YANG AKAN DITAMPILKAN
$page_file = 'home.php'; // Default halaman
$page_id = 'home'; // Identifier halaman

// --- Mapping Halaman Baru (Fokus Budaya) ---
$valid_pages = [
    'home'              => 'home.php',      // URL: index.php
    'beranda'           => 'home.php',      // Alias
    'situs'             => 'situs.php',     // Mengganti 'pariwisata'
    'tradisi'           => 'tradisi.php',   
    'event'             => 'event.php',     // Event Budaya
    'kontak'            => 'kontak.php',    
    'tentang'           => 'tentang.php',   
    'about'             => 'tentang.php',   // Memetakan 'about' ke file 'tentang.php'
    '404'               => '404.php',       // Halaman error
];
// ---------------------------------------------


// Cek apakah ada parameter 'page' di URL
if (isset($_GET['page']) && $_GET['page'] !== '') {
    $requested_page = strtolower($_GET['page']);

    if (array_key_exists($requested_page, $valid_pages)) {
        $file_to_load = $valid_pages[$requested_page];

        // Verifikasi apakah file tersebut benar-benar ada di folder pages/
        if (file_exists(ROOT_PATH . '/pages/' . $file_to_load)) {
            $page_file = $file_to_load;
            $page_id = $requested_page;
        } else {
            // Jika valid tapi file belum dibuat (misalnya situs.php)
            $page_file = '404.php'; 
        }
    } else {
        // Halaman tidak valid
        $page_file = '404.php';
    }
}

// 4. SET JUDUL HALAMAN DINAMIS
// Variabel $page_title dapat diatur di dalam file halaman (misalnya pages/home.php).
// Jika belum diset, gunakan default.
$page_title = $page_title ?? 'Minahasa - Warisan Budaya Sulawesi'; 

// 5. MUAT LAYOUT DAN KONTEN
require_once ROOT_PATH . '/templates/header.php'; // <-- <main class="flex-shrink-0"> ASUMSI DIBUKA DI SINI

// Muat konten halaman utama atau error
require_once ROOT_PATH . '/pages/' . $page_file; 

// =======================================================
// PERBAIKAN: TUTUP TAG <main> SEBELUM MEMUAT FOOTER
// =======================================================
?>
</main> 

<?php 
require_once ROOT_PATH . '/templates/footer.php'; // <-- FOOTER DIMUAT SEKARANG (di luar <main>)
?>