<?php
// =======================================================
// DB_CONNECT.PHP - Pengaturan Koneksi Database
// File ini mendefinisikan fungsi yang menghasilkan objek koneksi.
// =======================================================

/**
 * Mendapatkan objek koneksi baru ke database.
 * Fungsi ini harus dipanggil di setiap skrip atau fungsi yang membutuhkan koneksi.
 * @return mysqli Objek koneksi MySQLi.
 */
function connectDB() {
    // --- SESUAIKAN PENGATURAN INI DENGAN SERVER DATABASE ANDA ---
    $host = 'localhost'; 
    $user = 'root'; 
    $pass = ''; 
    $db_name = 'DISBUDPAR'; // Pastikan nama database ini benar
    // -----------------------------------------------------------

    // Buat koneksi menggunakan MySQLi
    $conn = new mysqli($host, $user, $pass, $db_name);

    // Cek koneksi
    if ($conn->connect_error) {
        // Hentikan skrip dan tampilkan pesan error yang jelas jika koneksi gagal
        // Di lingkungan produksi, ini harus diganti dengan logging.
        die("Koneksi Database GAGAL: " . $conn->connect_error);
    }

    // Set encoding (disarankan)
    $conn->set_charset("utf8");

    // Kembalikan objek koneksi yang siap digunakan
    return $conn;
}
?>