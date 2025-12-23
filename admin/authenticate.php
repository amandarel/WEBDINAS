<?php
session_start();

// JALUR KRUSIAL: Memastikan koneksi ke DB tersedia
require_once '../includes/db_connect.php';

// Fungsi untuk menangani error dan mengarahkan kembali ke login
function redirectToLogin($errorCode = 1) {
    header("Location: login.php?error=" . $errorCode);
    exit;
}

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectToLogin();
}

// Ambil dan bersihkan input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Cek apakah ada input kosong
if (empty($username) || empty(trim($password))) {
    redirectToLogin(2); 
}

// --- PENTING: INISIALISASI KONEKSI ---
// Panggil fungsi connectDB() yang didefinisikan di db_connect.php
// Ini akan membuat variabel $conn yang berisi objek koneksi database.
$conn = connectDB();
// ------------------------------------


// --- LOGIKA OTENTIKASI DATABASE ---

// 1. Query menggunakan Prepared Statement untuk keamanan SQL Injection.
$sql = "SELECT id_pengguna, username, password_hash, role FROM pengguna WHERE username = ? AND role = 'admin'";

// 2. Inisialisasi prepared statement
// Baris ini sekarang aman karena $conn sudah diinisialisasi di atas.
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
     
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['user_id'] = $admin['id_pengguna'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['role'] = $admin['role'];
        $_SESSION['loggedin'] = true; // Tambahkan penanda login

        header('Location: admin_dashboard.php');
         exit;
        
        } else {
            redirectToLogin(1); 
         }

} else {
     error_log("Gagal menyiapkan query: " . $conn->error); 
      redirectToLogin(3); 
}

// Tutup koneksi setelah semua operasi database selesai
if (isset($conn)) {
    $conn->close();
}

// Fallback redirect
redirectToLogin();
?>