<?php
// Skrip ini berfungsi untuk menghapus sesi pengguna dan mengarahkannya kembali ke halaman login.

// 1. Memulai sesi
// Harus dipanggil di awal untuk mengakses variabel sesi ($_SESSION).
session_start();

// 2. Hapus semua variabel sesi yang terdaftar
$_SESSION = array();

// 3. Hancurkan cookie sesi (jika ada)
// Ini adalah langkah penting untuk menghapus ID sesi dari sisi klien (browser).
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan sesi di sisi server
session_destroy();

// 5. Alihkan pengguna kembali ke halaman login.php
header("Location: login.php");
exit; // Selalu panggil exit setelah header redirect untuk menghentikan eksekusi skrip
?>