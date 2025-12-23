<?php 
// =======================================================
// TEMPLATES/HEADER.PHP - LAYOUT ATAS & NAVBAR (Fokus Budaya)
// =======================================================

// Tentukan halaman yang sedang aktif dari URL (default 'home')
// Asumsi: $current_page akan diset oleh file utama (misalnya index.php) sebelum header dipanggil
// Jika tidak diset, gunakan nilai default.
$current_page = $current_page ?? ($_GET['page'] ?? 'home'); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Minahasa - Warisan Budaya Sulawesi'; ?></title>

    <!-- Load Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Load Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css"> 

</head>
<body>

<!-- NAVBAR DENGAN DESAIN MODERN -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark-minahasa fixed-top shadow-lg">
    <div class="container py-1">
        <!-- Logo dan Nama Brand -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <!-- Ganti dengan SVG atau placeholder yang lebih baik jika memungkinkan -->
            <img src="assets/images/logominahasa.png" alt="Logo Minahasa" height="35" class="me-2 rounded-circle border border-1 border-white"> 
            <span class="d-none d-sm-inline fs-5">Minahasa Budaya</span>
        </a>
        
        <!-- Toggle Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Nav Links -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'home' || $current_page == 'beranda' ? 'active' : ''); ?>" 
                        aria-current="page" href="index.php">Beranda</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'tradisi' || $current_page == 'situs' ? 'active' : ''); ?>" 
                        href="index.php?page=tradisi">Kebudayaan</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'kontak' ? 'active' : ''); ?>" 
                        href="index.php?page=kontak">Kontak</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'about' || $current_page == 'tentang' ? 'active' : ''); ?>" 
                        href="index.php?page=about">Tentang Kami</a>
                </li>
            </ul>
            
            <!-- Aksi Kanan (Search & Login) -->
                
                <!-- Login Button -->
                <a class="btn btn-sm btn-merah-aksi rounded-pill" href="admin/login.php">
                    Login Admin
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Tambahkan modal pencarian jika diperlukan -->