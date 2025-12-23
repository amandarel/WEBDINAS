<?php
// BARIS KRUSIAL: Memulai sesi
session_start();

// --- PENGAMANAN HALAMAN (ADMIN-ONLY) ---

// 1. Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan ke halaman login
    header("Location: login.php");
    exit();
}

// ----------------------------------------------------------
// BAGIAN 1: KONFIGURASI DAN KONEKSI DATABASE (PDO)
// ----------------------------------------------------------
$host = 'localhost';
$db   = 'DISBUDPAR'; // <<< TELAH DISESUAIKAN DENGAN NAMA DATABASE ANDA
$user = 'root'; // <<< GANTI: Username Database Anda
$pass = ''; // <<< GANTI: Password Database Anda
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
$stats = [
    'total_budaya' => 0,
    'total_users' => 0,
    'unique_categories' => 0
];
$db_error = '';

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
    // ----------------------------------------------------------
    // BAGIAN 2: MENGAMBIL DATA STATISTIK
    // ----------------------------------------------------------
    
    // 1. Total Data Budaya (Jumlah total entri dari 5 tabel konten utama)
    $total_warisan = $pdo->query("SELECT COUNT(*) FROM warisan")->fetchColumn();
    $total_tradisi = $pdo->query("SELECT COUNT(*) FROM tradisi")->fetchColumn();
    $total_pakaian = $pdo->query("SELECT COUNT(*) FROM pakaianadatlengkap_minahasa")->fetchColumn();
    $total_musik = $pdo->query("SELECT COUNT(*) FROM musik_tradisional")->fetchColumn();
    $total_lagu = $pdo->query("SELECT COUNT(*) FROM lagudaerah")->fetchColumn();
    
    // Jumlahkan semua total
    $stats['total_budaya'] = $total_warisan + $total_tradisi + $total_pakaian + $total_musik + $total_lagu;

    // 2. Total Pengguna (Asumsi tabel: users. Jika tidak ada, query ini akan gagal dan menunjukkan error)
    try {
        $stmt_users = $pdo->query("SELECT COUNT(*) AS total FROM users");
        $stats['total_users'] = $stmt_users->fetchColumn();
    } catch (\PDOException $e) {
        $stats['total_users'] = 'N/A'; // Tunjukkan jika tabel users tidak ditemukan
    }


    // 3. Kategori Budaya Unik (Menggunakan kolom id_bidang dari tabel warisan sebagai contoh kategori)
    // Nilai ini bisa merepresentasikan jumlah Bidang/Jenis Warisan yang ada
    $stmt_categories = $pdo->query("SELECT COUNT(DISTINCT id_bidang) AS total FROM warisan");
    $stats['unique_categories'] = $stmt_categories->fetchColumn();

} catch (\PDOException $e) {
    // Tangani error koneksi atau query
    $db_error = "Kesalahan Database: " . $e->getMessage();
    // Jika terjadi error, nilai statistik tetap 0 (default)
}

// Data admin untuk tampilan
$username_admin = htmlspecialchars($_SESSION['username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
        }
    </style>
</head>
<body>

    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-xl flex flex-col">
            <div class="p-6 border-b-2 border-gray-100">
                <h1 class="text-2xl font-extrabold text-blue-600 tracking-wider">Admin Panel</h1>
            </div>
            <nav class="flex-grow p-4 space-y-2">
                <a href="admin_dashboard.php" class="flex items-center p-3 text-sm font-semibold text-blue-600 bg-blue-50 rounded-lg shadow-inner hover:bg-blue-100 transition duration-150">
                    <i class="fas fa-chart-line mr-3"></i> Dashboard
                </a>
                <a href="manage_budaya.php" class="flex items-center p-3 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition duration-150">
                    <i class="fas fa-palette mr-3"></i> Kelola Data Budaya
                </a>
            </nav>
            <div class="p-6 border-t-2 border-gray-100">
                <a href="logout.php" class="w-full flex items-center justify-center p-3 text-sm font-semibold text-white bg-red-500 rounded-lg shadow-md hover:bg-red-600 transition duration-150">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout (<?= $username_admin ?>)
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex items-center justify-between p-6 bg-white shadow-md">
                <h2 class="text-3xl font-bold text-gray-800">Dashboard Admin</h2>
                <div class="text-gray-600">
                    Selamat datang, <span class="font-semibold text-blue-600"><?= $username_admin ?></span>!
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-8">
                
                <!-- Notifikasi Error Database -->
                <?php if ($db_error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Gagal Koneksi!</strong>
                        <span class="block sm:inline"><?= htmlspecialchars($db_error) ?></span>
                        <p class="text-sm mt-1">Pastikan kredensial database di baris 20-22 file ini sudah benar.</p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Kartu Statistik 1: Total Data Budaya -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                        <div class="flex items-center">
                            <div class="p-3 mr-4 bg-blue-100 rounded-full">
                                <i class="fas fa-palette text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Data Budaya (Semua Jenis)</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $stats['total_budaya'] ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kartu Statistik 2: Total Pengguna -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                        <div class="flex items-center">
                            <div class="p-3 mr-4 bg-green-100 rounded-full">
                                <i class="fas fa-users text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Pengguna (Asumsi: Tabel users)</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $stats['total_users'] ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Statistik 3: Kategori Budaya Unik -->
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                        <div class="flex items-center">
                            <div class="p-3 mr-4 bg-yellow-100 rounded-full">
                                <i class="fas fa-chart-bar text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Jumlah Bidang Warisan Unik</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $stats['unique_categories'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Kontrol Cepat -->
                <div class="mt-8 bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Kontrol Cepat</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="manage_budaya.php?action=add" class="flex items-center px-6 py-3 bg-indigo-500 text-white font-medium rounded-lg shadow-md hover:bg-indigo-600 transition duration-150">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Budaya Baru
                        </a>
                    </div>
                </div>
                
                <div class="mt-8">
                    <p class="text-gray-600">Statistik di atas kini diambil secara dinamis dari database DISBUDPAR Anda.</p>
                </div>
            </main>
        </div>
    </div>

</body>
</html>