<?php
// PASTIKAN session_start() ADA DI SINI
session_start();

// =======================================================
// AUTORISASI: Pastikan hanya Admin yang bisa mengakses halaman ini
// =======================================================


// Data Tipe Kebudayaan yang akan dikelola
$management_modules = [
    ['title' => 'Warisan Budaya', 'description' => 'Situs, Candi, Peninggalan Sejarah', 'target' => 'crud_warisan.php', 'icon' => 'bi-building-up'],
    ['title' => 'Tradisi & Tarian Adat', 'description' => 'Data Tarian Adat dan Pertunjukan', 'target' => 'crud_tradisi.php', 'icon' => 'bi-people-fill'],
    ['title' => 'Pakaian Adat Minahasa', 'description' => 'Detail Pakaian Adat, aksesoris, dan filosofi', 'target' => 'crud_pakaian.php', 'icon' => 'bi- 옷'], // Placeholder untuk Pakaian Adat
    ['title' => 'Musik Tradisional', 'description' => 'Alat musik dan deskripsi singkat', 'target' => 'crud_musik.php', 'icon' => 'bi-boombox'],
    ['title' => 'Lagu Daerah', 'description' => 'Judul, lirik, dan tautan audio/video', 'target' => 'crud_lagu.php', 'icon' => 'bi-mic-fill'],
    ['title' => 'Bidang / Kategori', 'description' => 'Kelola Kategori Bidang Kebudayaan', 'target' => 'crud_bidang.php', 'icon' => 'bi-tag-fill'],
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pilih Manajemen Data</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .card-management {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .card-management:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .icon-large {
            font-size: 3rem;
            color: #0d6efd; /* Warna primary Bootstrap */
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4 text-primary">Pilih Data Kebudayaan yang Akan Dikelola</h1>
        <p class="text-center text-muted mb-5">Selamat datang, Admin. Silakan pilih modul untuk mengelola, menambah, mengedit, atau menghapus data.</p>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($management_modules as $module): ?>
                <div class="col">
                    <a href="<?= htmlspecialchars($module['target']) ?>" class="text-decoration-none">
                        <div class="card h-100 card-management shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi <?= htmlspecialchars($module['icon']) ?> icon-large mb-3"></i>
                                <h5 class="card-title text-dark"><?= htmlspecialchars($module['title']) ?></h5>
                                <p class="card-text text-muted small"><?= htmlspecialchars($module['description']) ?></p>
                                <span class="btn btn-outline-primary btn-sm mt-2">Kelola Data</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="admin_dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard Utama
            </a>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>