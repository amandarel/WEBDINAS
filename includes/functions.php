<?php
// =======================================================
// INCLUDES/FUNCTIONS.PHP (REVISI AKHIR - SKEMA DATABASE DIADOPSI)
// Tabel yang digunakan: warisan, tradisi, event, bidang, pakaianadatlengkap_minahasa, musik_tradisional, lagudaerah
// =======================================================

// Memastikan file koneksi dipanggil sekali
// PENTING: Pastikan file 'db_connect.php' berada di folder yang sama (includes/)
require_once 'db_connect.php'; 

// TENTUKAN PATH DASAR UNTUK FILE AUDIO MP3
// Ubah 'assets/imagesaudio/' jika folder MP3 Anda berbeda lokasinya di root web.
define('AUDIO_BASE_PATH', 'assets/imagesaudio/');
function sanitize_external_url(?string $url): ?string {
    if (empty($url)) {
        return null;
    }

    // 1. Hapus spasi di awal dan akhir
    $url = trim($url);

    // 2. Periksa apakah sudah memiliki protokol (http:// atau https://)
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        // Sudah ada protokol, gunakan apa adanya
        return $url;
    }

    // 3. Tambahkan https:// sebagai default jika tidak ada protokol
    return 'https://' . $url;
}
// Fungsi Debugging untuk menampilkan error SQL
function displaySqlError($db, $function_name, $sql = "") {
    // Memastikan $db adalah objek mysqli yang valid
    if (!($db instanceof mysqli) || $db->connect_error) {
        $error_msg = $db->connect_error ?? 'Objek database tidak valid atau koneksi gagal.';
        echo "<div style='background-color:#fee2e2; border:1px solid #fca5a5; padding: 15px; margin: 10px; color: #991b1b; border-radius: 4px;'>";
        echo "<strong>Fatal Error:</strong> Koneksi database GAGAL di fungsi: " . htmlspecialchars($function_name) . "<br>";
        echo "<p><strong>Pesan MySQL:</strong> " . htmlspecialchars($error_msg) . "</p>";
        echo "</div>";
        return;
    }

    $error_message = $db->error;
    $has_error = $error_message;

    if ($has_error) {
        echo "<div style='background-color:#fee2e2; border:1px solid #fca5a5; padding: 15px; margin: 10px; color: #991b1b; border-radius: 4px;'>";
        echo "<h3>[SQL ERROR] di Fungsi: " . htmlspecialchars($function_name) . "</h3>";
        
        echo "<p><strong>Pesan MySQL:</strong> " . htmlspecialchars($error_message) . "</p>";

        if ($sql) {
            echo "<p>Query Gagal:</p><pre style='background-color:#fef2f2; padding: 8px; border: 1px solid #fca5a5; overflow-x: auto; font-size: 0.9em;'><code>" . htmlspecialchars($sql) . "</code></pre>";
        }
        
        // Tampilkan info file dan baris tempat fungsi error dipanggil (untuk debugging)
        // Gunakan try-catch karena debug_backtrace mungkin tidak selalu ada
        try {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
            echo "<p><em>Dipanggil dari File: " . htmlspecialchars($backtrace['file']) . " (Baris: " . htmlspecialchars($backtrace['line']) . ")</em></p>";
        } catch (\Throwable $th) {
            // Abaikan jika gagal mendapatkan backtrace
        }
        
        echo "</div>";
    }
}


// =======================================================
// FUNGSI UTILITY & POPULARITAS (VIEWS)
// =======================================================

/**
 * Menaikkan hitungan view_count berdasarkan slug-nya.
 * Mencakup tabel 'warisan', 'tradisi', dan 'lagudaerah'.
 * @param string $slug Slug warisan yang dilihat
 * @return bool True jika update berhasil di salah satu tabel, false jika gagal.
 */
function incrementViewCount($slug) {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        // displaySqlError dipanggil di dalam fungsi lain jika koneksi gagal.
        return false;
    }
    $affected_rows = 0;

    // Daftar tabel yang akan di-update (dengan nama kolom slug-nya)
    $tables = [
        'warisan' => 'nama_warisan',
        'tradisi' => 'nama_tradisi',
        'lagudaerah' => 'Judul_Lagu',
        // 'musik_tradisional' => 'nama_musik', // Tambahkan jika kolom view_count ada di sini
    ];

    foreach ($tables as $table => $column) {
        $sql = "
            UPDATE {$table}
            SET view_count = IFNULL(view_count, 0) + 1
            WHERE LOWER(REPLACE({$column}, ' ', '-')) = ?
        ";
        if ($stmt = $db->prepare($sql)) {
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $affected_rows += $db->affected_rows;
            $stmt->close();
        } else {
            // Tampilkan error jika Prepared Statement gagal (error query)
            displaySqlError($db, "incrementViewCount (Prepared {$table})", $sql);
        }
    }

    $db->close();
    return $affected_rows > 0; 
}

/**
 * Mengambil daftar warisan dari tabel WARISAN yang paling populer.
 * @param int $limit Batas jumlah warisan yang akan diambil (default 3).
 * @return array Data warisan populer
 */
function getPopularSitus($limit = 3) {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getPopularSitus (Koneksi Gagal)");
        return [];
    }
    $situs_list = [];

    $sql = "
        SELECT 
            w.nama_warisan, w.lokasi_fisik, w.gambar_utama, w.view_count,
            b.nama_bidang AS bidang, 
            LOWER(REPLACE(w.nama_warisan, ' ', '-')) AS slug
        FROM warisan w
        JOIN bidang b ON w.id_bidang = b.id_bidang
        WHERE w.status_publikasi = 'Published'
        ORDER BY w.view_count DESC, w.tanggal_upload DESC
        LIMIT ?
    ";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("i", $limit); 
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $situs_list[] = [
                    'nama' => htmlspecialchars($row['nama_warisan']),
                    'bidang' => htmlspecialchars($row['bidang']),
                    'lokasi' => htmlspecialchars($row['lokasi_fisik']),
                    'views' => $row['view_count'],
                    // MENGGUNAKAN NAMA FILE DENGAN PATH RELATIF
                    'image_url' => 'assets/images/situs/' . htmlspecialchars($row['gambar_utama']), 
                    'slug' => $row['slug']
                ];
            }
        }
        $stmt->close();
    } else {
        displaySqlError($db, "getPopularSitus (Prepared Statement)", $sql);
    }
    
    $db->close(); 
    return $situs_list;
}

// =======================================================
// FUNGSI PENGAMBILAN DATA SITUS (Tabel: warisan)
// =======================================================

/**
 * Mengambil semua Situs Budaya dari tabel WARISAN.
 * @return array Data situs
 */
function getAllSitusBudaya() {
    $db = connectDB(); 
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getAllSitusBudaya (Koneksi Gagal)");
        return [];
    }
    $situs_list = [];

    $sql = "
        SELECT
        w.nama_warisan, w.deskripsi, w.lokasi_fisik, w.gambar_utama, w.koordinat, w.view_count,
        b.nama_bidang AS bidang, 
        LOWER(REPLACE(w.nama_warisan, ' ', '-')) AS slug
        FROM warisan w
        JOIN bidang b ON w.id_bidang = b.id_bidang
        WHERE w.status_publikasi = 'Published'
        ORDER BY w.tanggal_upload DESC
    ";

    $result = $db->query($sql);

    if (!$result) {
        displaySqlError($db, "getAllSitusBudaya (Query Gagal)", $sql);
    } elseif ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $situs_list[] = [
                'nama' => htmlspecialchars($row['nama_warisan']),
                'bidang' => htmlspecialchars($row['bidang']),
                'lokasi' => htmlspecialchars($row['lokasi_fisik']),
                'deskripsi_singkat' => substr(strip_tags($row['deskripsi']), 0, 100) . '...',
                'deskripsi_lengkap' => htmlspecialchars($row['deskripsi']), 
                'koordinat' => htmlspecialchars($row['koordinat'] ?? ''),
                'views' => $row['view_count'], 
                // PERBAIKAN: Menambahkan kembali awalan 'uploads/warisan/' karena data di DB hanya berisi nama file.
                'image_url' => 'uploads/warisan/' . htmlspecialchars($row['gambar_utama']), 
                'slug' => $row['slug']
            ];
        }
    }

    $db->close(); 
    return $situs_list;
}

/**
 * Mengambil detail lengkap satu Situs Budaya berdasarkan slug dari tabel WARISAN.
 */
function getSitusBySlug($slug) {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getSitusBySlug (Koneksi Gagal)");
        return null;
    }
    $situs = null;

    $sql = "
        SELECT 
        w.*, b.nama_bidang AS bidang
        FROM warisan w
        JOIN bidang b ON w.id_bidang = b.id_bidang
        WHERE LOWER(REPLACE(w.nama_warisan, ' ', '-')) = ? 
        AND w.status_publikasi = 'Published'
    ";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $situs = null; // Inisialisasi variabel situs

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            $situs = [
                'nama' => htmlspecialchars($row['nama_warisan']),
                'deskripsi' => $row['deskripsi'], 
                'deskripsi_lengkap' => $row['deskripsi'],
                'lokasi_fisik' => htmlspecialchars($row['lokasi_fisik']),
                'koordinat' => htmlspecialchars($row['koordinat'] ?? ''), 
                'bidang' => htmlspecialchars($row['bidang']),
                'tanggal_upload' => $row['tanggal_upload'],
                'view_count' => $row['view_count'],
                // PERBAIKAN: Menambahkan kembali awalan 'uploads/warisan/' karena data di DB hanya berisi nama file.
                'gambar_url' => 'uploads/warisan/' . htmlspecialchars($row['gambar_utama']), 
            ];
        }
        $stmt->close();
    } else {
        displaySqlError($db, "getSitusBySlug (Prepared Statement)", $sql);
    }

    $db->close();
    return $situs;
}

// =======================================================
// FUNGSI PENGAMBILAN DATA TRADISI (Tabel: tradisi)
// Menggunakan nama_tradisi dan lokasi_asal
// =======================================================

/**
 * Mengambil semua Tradisi Budaya dari tabel TRADISI.
 * @return array Data daftar tradisi
 */
function getAllTradisiBudaya() {
    $db = connectDB(); 
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getAllTradisiBudaya (Koneksi Gagal)");
        return [];
    }
    $tradisi_list = [];

    // MENGGUNAKAN KOLOM 'nama_tradisi' dan 'lokasi_asal'
    $sql = "
        SELECT 
            w.nama_tradisi, w.deskripsi, w.lokasi_asal, w.gambar_utama, w.view_count,
            w.jenis_tradisi, -- Kolom yang berisi 'Tarian', 'Baju Adat', dll.
            b.nama_bidang AS bidang, 
            LOWER(REPLACE(w.nama_tradisi, ' ', '-')) AS slug
        FROM tradisi w
        JOIN bidang b ON w.id_bidang = b.id_bidang
        WHERE w.status_publikasi = 'Published'
        ORDER BY w.tanggal_upload DESC
    ";

    $result = $db->query($sql);

    if (!$result) {
        // Tampilkan error SQL jika query gagal
        displaySqlError($db, "getAllTradisiBudaya (Query Gagal)", $sql);
    } elseif ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tradisi_list[] = [
                'nama' => htmlspecialchars($row['nama_tradisi']), 
                'bidang' => htmlspecialchars($row['bidang']),
                'lokasi' => htmlspecialchars($row['lokasi_asal']), 
                // Deskripsi singkat (dipotong dan diberi '...') untuk tampilan grid
                'deskripsi_singkat' => substr(strip_tags($row['deskripsi']), 0, 100) . '...', 
                
                'deskripsi_lengkap' => htmlspecialchars($row['deskripsi']),

                // Menggunakan 'jenis_tradisi' sebagai 'kategori'
                'kategori' => htmlspecialchars($row['jenis_tradisi'] ?? ''),
                'views' => $row['view_count'], 
                // MENGGUNAKAN NAMA FILE DENGAN PATH RELATIF
                'image_url' => 'uploads/tradisi/' . htmlspecialchars($row['gambar_utama']), 
                'slug' => $row['slug']
            ];
        }
    }

    $db->close(); 
    return $tradisi_list;
}

/**
 * Mengambil detail lengkap satu Tradisi Budaya berdasarkan slug dari tabel TRADISI.
 */
function getTradisiBySlug($slug) {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getTradisiBySlug (Koneksi Gagal)");
        return null;
    }
    $tradisi = null;


    $sql = "
        SELECT 
            w.*, b.nama_bidang AS bidang
        FROM tradisi w
        JOIN bidang b ON w.id_bidang = b.id_bidang
        WHERE LOWER(REPLACE(w.nama_tradisi, ' ', '-')) = ? 
        AND w.status_publikasi = 'Published'
    ";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $tradisi = [
                'nama' => htmlspecialchars($row['nama_tradisi']),
                'deskripsi_lengkap' => $row['deskripsi'], // Menggunakan nama key yang sama
                'deskripsi_singkat' => substr(strip_tags($row['deskripsi']), 0, 100) . '...', // Tambahkan ini juga
                'lokasi' => htmlspecialchars($row['lokasi_asal']), // Menggunakan 'lokasi' untuk konsistensi template
                'kategori' => htmlspecialchars($row['jenis_tradisi'] ?? ''), // Menggunakan jenis_tradisi sebagai kategori
                'bidang' => htmlspecialchars($row['bidang']),
                'tanggal_upload' => $row['tanggal_upload'],
                'view_count' => $row['view_count'],
                'image_url' => 'uploads/tradisi/' . htmlspecialchars($row['gambar_utama']), 
            ];
        }
        $stmt->close();
    } else {
        displaySqlError($db, "getTradisiBySlug (Prepared Statement)", $sql);
    }

    $db->close();
    return $tradisi;
}

// =======================================================
// FUNGSI PENGAMBILAN DATA MUSIK TRADISIONAL (Tabel: musik_tradisional)
// Tidak ada perubahan di bagian ini.
// =======================================================


/**
 * Mengambil semua Musik Tradisional dari tabel MUSIK_TRADISIONAL.
 * @return array Data daftar musik tradisional
 */
function getAllMusikTradisional() {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getAllMusikTradisional (Koneksi Gagal)");
        return [];
    }
    $musik_list = [];

$sql = "
SELECT
id_musik, nama_musik, asal_daerah, jenis_alat, Deskripsi, link_youtube, gambar_utama,
LOWER(REPLACE(nama_musik, ' ', '-')) AS slug
FROM musik_tradisional
ORDER BY nama_musik ASC
";

$result = $db->query($sql);

if (!$result) {
    displaySqlError($db, "getAllMusikTradisional (Query Gagal)", $sql);
} elseif ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $musik_list[] = [
            'nama' => htmlspecialchars($row['nama_musik']),
            'asal_daerah' => htmlspecialchars($row['asal_daerah']),
            'jenis_alat' => htmlspecialchars($row['jenis_alat']),
            'deskripsi_lengkap' => htmlspecialchars($row['Deskripsi']),
            'link_youtube' => htmlspecialchars($row['link_youtube'] ?? ''),
            'image_url' => 'uploads/musik/' . htmlspecialchars($row['gambar_utama']),
            'slug' => $row['slug']
        ];
    }
}

$db->close();
return $musik_list;
}


/**
 * Mengambil detail lengkap satu Musik Tradisional berdasarkan slug.
 * @param string $slug Slug dari musik tradisional (contoh: "gamelan-bali")
 * @return array|null Detail musik tradisional atau null jika tidak ditemukan/error
 */
function getMusikBySlug($slug) {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getMusikBySlug (Koneksi Gagal)");
        return null;
    }
    $musik = null;

$sql = "
SELECT
id_musik, nama_musik, asal_daerah, jenis_alat, Deskripsi,
link_audio, link_youtube, gambar_utama, tanggal_input
FROM musik_tradisional
WHERE LOWER(REPLACE(nama_musik, ' ', '-')) = ?
";

if ($stmt = $db->prepare($sql)) {
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $musik = [
        'nama' => htmlspecialchars($row['nama_musik']),
        'asal_daerah' => htmlspecialchars($row['asal_daerah']),
        'jenis_alat' => htmlspecialchars($row['jenis_alat']),
        'deskripsi_lengkap' => $row['Deskripsi'],
        'link_audio' => AUDIO_BASE_PATH . htmlspecialchars($row['link_audio'] ?? ''),
        'link_youtube' => htmlspecialchars($row['link_youtube'] ?? ''),
        'tanggal_input' => $row['tanggal_input'],
        'gambar_url' => 'uploads/musik/' . htmlspecialchars($row['gambar_utama']),
     ];
    }
    $stmt->close();
} else {
    displaySqlError($db, "getMusikBySlug (Prepared Statement)", $sql);
}

$db->close();
return $musik;
}

// =======================================================
// FUNGSI PENGAMBILAN DATA LAGU DAERAH (Tabel: lagudaerah)
// SUDAH DIKOREKSI: Menambahkan kolom link_youtube dan memperbaiki key deskripsi
// =======================================================

/**
 * Mengambil semua Lagu Daerah dari tabel LAGUDAERAH.
 * @return array Data daftar lagu daerah
 */
function getAllLaguDaerah() {
    $db = connectDB(); 
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getAllLaguDaerah (Koneksi Gagal)");
        return [];
    }
    $lagu_list = [];

    $sql = "
    SELECT 
    ID_Lagu, Judul_Lagu, Deskripsi, Foto_Utama, Link_Audio, link_youtube, view_count,
    LOWER(REPLACE(Judul_Lagu, ' ', '-')) AS slug
    FROM lagudaerah
    ";

    $result = $db->query($sql);

    if (!$result) {
        displaySqlError($db, "getAllLaguDaerah (Query Gagal)", $sql);
    } elseif ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $lagu_list[] = [
                'nama' => htmlspecialchars($row['Judul_Lagu']),
                'deskripsi_lengkap' => htmlspecialchars($row['Deskripsi']),
                'views' => $row['view_count'] ?? 0, 
                'link_youtube' => htmlspecialchars($row['link_youtube'] ?? ''),
                'link_audio' => AUDIO_BASE_PATH . htmlspecialchars($row['Link_Audio'] ?? ''),
                'image_url' => 'uploads/lagu/' . htmlspecialchars($row['Foto_Utama']), 
                'slug' => $row['slug']
            ];
        }
    }

    $db->close(); 
    return $lagu_list;
}

/**
 * Mengambil detail lengkap satu Lagu Daerah berdasarkan slug.
 * @param string $slug Slug lagu yang dicari
 * @return array|null Data lagu daerah atau null jika tidak ditemukan
 */
function getLaguBySlug($slug) {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getLaguBySlug (Koneksi Gagal)");
        return null;
    }
    $lagu = null;

    $sql = "
    SELECT 
    ID_Lagu, Judul_Lagu, Deskripsi, Foto_Utama, Link_Audio, link_youtube, view_count
    FROM lagudaerah
    WHERE LOWER(REPLACE(Judul_Lagu, ' ', '-')) = ? 
    AND status_publikasi = 'Published'
    ";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $lagu = [
                'nama' => htmlspecialchars($row['Judul_Lagu']),
                'deskripsi_lengkap' => $row['Deskripsi'],
                'link_youtube' => htmlspecialchars($row['link_youtube'] ?? ''),
                'link_audio' => AUDIO_BASE_PATH . htmlspecialchars($row['Link_Audio'] ?? ''),
                'view_count' => $row['view_count'] ?? 0,
                'image_url' => 'uploads/lagu/' . htmlspecialchars($row['Foto_Utama']), 
            ];
        }
        $stmt->close();
    } else {
        displaySqlError($db, "getLaguBySlug (Prepared Statement)", $sql);
    }

    $db->close();
    return $lagu;
}




// =======================================================
// FUNGSI PENGAMBILAN DATA PAKAIAN ADAT MINAHASA (Tabel: pakaianadatlengkap_minahasa)
// =======================================================

/**
 * Mengambil semua Pakaian Adat Minahasa.
 * @return array Data daftar pakaian adat
 */
function getAllPakaianAdatMinahasa() {
    $db = connectDB(); 
    if (!$db || $db->connect_error) {
        // Tampilkan error koneksi jika gagal
        displaySqlError($db, "getAllPakaianAdatMinahasa (Koneksi Gagal)");
        return [];
    }
    $pakaian_list = [];

    $sql = "
        SELECT 
            id_pakaian, nama_pakaian, gender, acara_penggunaan, makna_filosofis, bahan_dasar, daftar_aksesoris, nama_file_foto, lokasi_asal, 
            -- Tambahkan kolom 'deskripsi' di sini
            deskripsi,
            LOWER(REPLACE(nama_pakaian, ' ', '-')) AS slug
        FROM pakaianadatlengkap_minahasa
        ORDER BY nama_pakaian ASC
    ";
    
    $result = $db->query($sql);

    if (!$result) {
        // Tampilkan error kueri jika gagal
        displaySqlError($db, "getAllPakaianAdatMinahasa (Query Gagal)", $sql);
    } elseif ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            
            // Tentukan sumber deskripsi singkat: gunakan 'deskripsi' jika ada, atau fallback ke 'makna_filosofis'
            $source_text = !empty($row['deskripsi']) ? $row['deskripsi'] : $row['makna_filosofis'];
            
            $pakaian_list[] = [
                'nama' => htmlspecialchars($row['nama_pakaian']), 
                'deskripsi_lengkap' => htmlspecialchars($row['deskripsi']),
                'gender' => htmlspecialchars($row['gender']),
                'acara' => htmlspecialchars($row['acara_penggunaan']),
                'lokasi' => htmlspecialchars($row['lokasi_asal']),
                // Gunakan kolom 'deskripsi' untuk ringkasan
                'makna_singkat' => substr(strip_tags($source_text), 0, 100) . '...', 
                'image_url' => 'uploads/pakaian/' . htmlspecialchars($row['nama_file_foto']), 
                'slug' => $row['slug']
            ];
        }
    }

    $db->close(); 
    return $pakaian_list;
}

/**
 * Mengambil detail lengkap satu Pakaian Adat Minahasa berdasarkan slug.
 * Kolom 'lokasi_asal' dan 'deskripsi' sekarang disertakan.
 */
function getPakaianAdatBySlug($slug) {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        // Tampilkan error koneksi jika gagal
        displaySqlError($db, "getPakaianAdatBySlug (Koneksi Gagal)");
        return null;
    }
    $pakaian = null;

    $sql = "
        SELECT 
            id_pakaian, nama_pakaian, gender, acara_penggunaan, makna_filosofis, bahan_dasar, daftar_aksesoris, nama_file_foto, lokasi_asal,
            -- Tambahkan kolom 'deskripsi' di sini
            deskripsi
        FROM pakaianadatlengkap_minahasa
        WHERE LOWER(REPLACE(nama_pakaian, ' ', '-')) = ? 
    ";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $pakaian = [
                'nama' => htmlspecialchars($row['nama_pakaian']),
                'gender' => htmlspecialchars($row['gender']),
                'acara_penggunaan' => htmlspecialchars($row['acara_penggunaan']),
                'makna_filosofis' => $row['makna_filosofis'],
                'bahan_dasar' => htmlspecialchars($row['bahan_dasar']),
                'daftar_aksesoris' => $row['daftar_aksesoris'],
                'lokasi' => htmlspecialchars($row['lokasi_asal']),
                // Ambil dan simpan kolom 'deskripsi'
                'deskripsi_lengkap' => $row['deskripsi'], 
                'gambar_url' => 'uploads/pakaian/' . htmlspecialchars($row['nama_file_foto']), 
            ];
        }
        $stmt->close();
    } else {
        // Tampilkan error prepared statement jika gagal
        displaySqlError($db, "getPakaianAdatBySlug (Prepared Statement)", $sql);
    }

    $db->close();
    return $pakaian;
}

/**
 * Mengambil daftar Event Budaya yang akan datang atau sedang berlangsung (Terkini) dari tabel EVENT.
 * @return array Daftar event (dibatasi 3 item)
 */
function getEventBudayaTerkini() {
    $db = connectDB(); 
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getEventBudayaTerkini (Koneksi Gagal)");
        return [];
    }
    $events = [];

    $sql = "
        SELECT 
            e.nama_event, e.tanggal_mulai, e.lokasi, e.gambar_utama,
            LOWER(REPLACE(e.nama_event, ' ', '-')) AS slug, 
            b.nama_bidang AS bidang
        FROM event e
        JOIN bidang b ON e.id_bidang = b.id_bidang
        WHERE e.tanggal_selesai >= CURDATE() AND e.status_publikasi = 'Published'
        ORDER BY e.tanggal_mulai ASC
        LIMIT 3
    ";

    $result = $db->query($sql);

    if (!$result) {
        // Tampilkan error SQL jika query gagal
        displaySqlError($db, "getEventBudayaTerkini (Query Gagal)", $sql);
    } elseif ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'nama_event' => htmlspecialchars($row['nama_event']),
                'tanggal_mulai' => $row['tanggal_mulai'],
                'lokasi' => htmlspecialchars($row['lokasi']),
                'bidang' => htmlspecialchars($row['bidang']),
                // MENGGUNAKAN NAMA FILE DENGAN PATH RELATIF
                'image' => 'assets/images/event/' . htmlspecialchars($row['gambar_utama']), 
                'slug' => $row['slug']
            ];
        }
    }

    $db->close();
    return $events;
}


/**
 * Mengambil detail satu Event Budaya berdasarkan slug dari tabel EVENT.
 */
function getEventBySlug($slug) {
    $db = connectDB();
    if (!$db || $db->connect_error) {
        displaySqlError($db, "getEventBySlug (Koneksi Gagal)");
        return null;
    }
    $event = null;

    // KETENTUAN: Menggunakan nama kolom 'deskipsi' sesuai konfirmasi
    $sql = "
        SELECT 
            e.id_event, e.id_bidang, e.nama_event, e.deskipsi, e.lokasi, e.tanggal_mulai, e.tanggal_selesai, e.gambar_utama, e.status_publikasi, e.tanggal_input,
            b.nama_bidang AS bidang
        FROM event e
        JOIN bidang b ON e.id_bidang = b.id_bidang
        WHERE LOWER(REPLACE(e.nama_event, ' ', '-')) = ? 
        AND e.status_publikasi = 'Published'
    ";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $event = [
                'nama' => htmlspecialchars($row['nama_event']),
                // Mengambil dari 'deskipsi' (DB) dan menamainya 'deskripsi' (Output)
                'deskripsi' => $row['deskipsi'], 
                'lokasi' => htmlspecialchars($row['lokasi']),
                'bidang' => htmlspecialchars($row['bidang']),
                'tanggal_mulai' => $row['tanggal_mulai'],
                'tanggal_selesai' => $row['tanggal_selesai'],
                // MENGGUNAKAN NAMA FILE DENGAN PATH RELATIF
                'gambar_url' => 'assets/images/event/' . htmlspecialchars($row['gambar_utama']), 
            ];
        }
        $stmt->close();
    } else {
        displaySqlError($db, "getEventBySlug (Prepared Statement)", $sql);
    }

    $db->close();
    return $event;
}