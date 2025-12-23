<?php
session_start();

// =======================================================
// KONFIGURASI DAN KONEKSI DATABASE
// =======================================================

// Direktori untuk menyimpan file gambar yang diunggah.
// PASTIKAN DIREKTORI INI ADA DAN DAPAT DITULISI (CHMOD 777 atau 755)!
$upload_dir = '../uploads/tradisi/'; 

function getDBConnection() {
    $host = 'localhost'; 
    $db   = 'DISBUDPAR'; 
    $user = 'admi'; // HARUS DIGANTI
    $pass = 'DISBUDPAR123'; // HARUS DIGANTI
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Dalam lingkungan nyata, gunakan halaman error, bukan die()
        die("Koneksi Database Gagal: " . $e->getMessage()); 
    }
}
$pdo = getDBConnection(); 

// =======================================================
// FUNGSI UNGGAH FILE
// =======================================================

/**
 * Menangani proses unggah file gambar.
 * @param array $file_data Data dari $_FILES['GAMBAR_UTAMA'].
 * @param string $old_file_name Nama file lama (untuk dipertahankan atau dihapus).
 * @param string $upload_dir Direktori target.
 * @return string Nama file baru yang disimpan atau nama file lama jika tidak ada unggahan baru.
 * @throws Exception Jika terjadi kesalahan unggah.
 */
function handleFileUpload($file_data, $old_file_name, $upload_dir) {
    // 1. Cek apakah ada file baru yang diunggah
    if ($file_data['error'] === UPLOAD_ERR_NO_FILE) {
        // Jika tidak ada file baru, kembalikan nama file lama
        return $old_file_name;
    }
    
    // 2. Cek error unggahan
    if ($file_data['error'] !== UPLOAD_ERR_OK) {
        // Untuk error yang lebih spesifik, bisa di-switch case error code
        throw new Exception("Error unggah file (Code: {$file_data['error']}).");
    }

    // 3. Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file_data['type'], $allowed_types)) {
        throw new Exception("Tipe file tidak didukung. Hanya JPEG, PNG, GIF, atau WebP.");
    }
    
    // 4. Buat direktori jika belum ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 5. Generate nama file unik
    $ext = pathinfo($file_data['name'], PATHINFO_EXTENSION);
    $new_file_name = uniqid('tradisi_', true) . '.' . $ext;
    $target_file = $upload_dir . $new_file_name;

    // 6. Pindahkan file
    if (move_uploaded_file($file_data['tmp_name'], $target_file)) {
        // 7. Hapus file lama jika ada (dan bukan file default/kosong)
        if ($old_file_name && $old_file_name !== '') {
            $old_file_path = $upload_dir . $old_file_name;
            if (file_exists($old_file_path)) {
                @unlink($old_file_path);
            }
        }
        return $new_file_name; 
    } else {
        throw new Exception("Gagal memindahkan file yang diunggah. Cek izin direktori.");
    }
}


// =======================================================
// FUNGSI UTAMA (BIDANG dan TRADISI)
// =======================================================

function get_all_bidang($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_bidang, nama_bidang FROM bidang ORDER BY nama_bidang ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function get_all_tradisi($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                t.ID_TRADISI, t.ID_BIDANG, t.NAMA_TRADISI, t.DESKRIPSI, t.GAMBAR_UTAMA, 
                t.STATUS_PUBLIKASI, t.JENIS_TRADISI, t.LOKASI_ASAL, 
                b.nama_bidang
            FROM tradisi t
            JOIN bidang b ON t.ID_BIDANG = b.id_bidang
            ORDER BY t.TANGGAL_UPLOAD DESC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching tradisi: " . $e->getMessage());
        return [];
    }
}

function get_tradisi_by_id($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tradisi WHERE ID_TRADISI = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching single tradisi: " . $e->getMessage());
        return false;
    }
}


/**
 * Menyimpan atau memperbarui data tradisi.
 * @param PDO $pdo Koneksi database.
 * @param array $data Data tradisi dari form (sudah termasuk filename baru/lama).
 * @return array Hasil operasi.
 */
function save_tradisi($pdo, $data) {
    $data = array_map('trim', $data);
    $data['STATUS_PUBLIKASI'] = $data['STATUS_PUBLIKASI'] ?? 'draft';

    if (empty($data['ID_TRADISI'])) {
        // CREATE
        $sql = "INSERT INTO tradisi (ID_BIDANG, NAMA_TRADISI, DESKRIPSI, GAMBAR_UTAMA, VIEW_COUNT, STATUS_PUBLIKASI, TANGGAL_UPLOAD, JENIS_TRADISI, LOKASI_ASAL) 
                VALUES (:ID_BIDANG, :NAMA_TRADISI, :DESKRIPSI, :GAMBAR_UTAMA, 0, :STATUS_PUBLIKASI, NOW(), :JENIS_TRADISI, :LOKASI_ASAL)";
    } else {
        // UPDATE
        $sql = "UPDATE tradisi SET ID_BIDANG = :ID_BIDANG, NAMA_TRADISI = :NAMA_TRADISI, DESKRIPSI = :DESKRIPSI, GAMBAR_UTAMA = :GAMBAR_UTAMA, STATUS_PUBLIKASI = :STATUS_PUBLIKASI, JENIS_TRADISI = :JENIS_TRADISI, LOKASI_ASAL = :LOKASI_ASAL WHERE ID_TRADISI = :ID_TRADISI";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ID_BIDANG' => $data['ID_BIDANG'],
            ':NAMA_TRADISI' => $data['NAMA_TRADISI'],
            ':DESKRIPSI' => $data['DESKRIPSI'],
            ':GAMBAR_UTAMA' => $data['GAMBAR_UTAMA'], // Nama file (sudah diproses oleh handler)
            ':STATUS_PUBLIKASI' => $data['STATUS_PUBLIKASI'],
            ':JENIS_TRADISI' => $data['JENIS_TRADISI'],
            ':LOKASI_ASAL' => $data['LOKASI_ASAL'],
            ...(!empty($data['ID_TRADISI']) ? [':ID_TRADISI' => $data['ID_TRADISI']] : [])
        ]);
        return ['success' => true, 'message' => empty($data['ID_TRADISI']) ? "Data Tradisi berhasil ditambahkan." : "Data Tradisi berhasil diperbarui."];
    } catch (PDOException $e) {
        error_log("Error saving tradisi: " . $e->getMessage());
        return ['success' => false, 'message' => "Gagal menyimpan data: " . $e->getMessage()];
    }
}

function delete_tradisi($pdo, $id) {
    $item = get_tradisi_by_id($pdo, $id);
    if (!$item) {
        return ['success' => false, 'message' => "Data tidak ditemukan."];
    }

    try {
        // Hapus file gambar terkait sebelum menghapus record di DB
        global $upload_dir;
        $file_to_delete = $upload_dir . $item['GAMBAR_UTAMA'];
        if ($item['GAMBAR_UTAMA'] && file_exists($file_to_delete)) {
            @unlink($file_to_delete);
        }

        $stmt = $pdo->prepare("DELETE FROM tradisi WHERE ID_TRADISI = ?");
        $stmt->execute([$id]);
        return ['success' => true, 'message' => "Data Tradisi ID $id berhasil dihapus."];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "Gagal menghapus data: " . $e->getMessage()];
    }
}

// =======================================================
// HANDLER FORM (Telah Diperbarui untuk Unggahan File)
// =======================================================
$status = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'ID_TRADISI', FILTER_VALIDATE_INT);
        if ($id) { $status = delete_tradisi($pdo, $id); } else { $status = ['success' => false, 'message' => "ID Tradisi tidak valid."]; }
    } elseif ($action === 'save') {
        
        $tradisi_id = filter_input(INPUT_POST, 'ID_TRADISI', FILTER_VALIDATE_INT);
        $old_gambar_utama = filter_input(INPUT_POST, 'OLD_GAMBAR_UTAMA', FILTER_SANITIZE_STRING) ?? '';
        $new_filename = $old_gambar_utama; // Default: keep the old file name

        try {
            // 1. Tangani Unggahan File Gambar Utama
            if (isset($_FILES['GAMBAR_UTAMA']) && $_FILES['GAMBAR_UTAMA']['error'] !== UPLOAD_ERR_NO_FILE) {
                $new_filename = handleFileUpload($_FILES['GAMBAR_UTAMA'], $old_gambar_utama, $upload_dir);
            } elseif ($tradisi_id && empty($old_gambar_utama)) {
                // Kasus edit, tapi old_gambar_utama tidak terkirim (hanya untuk jaga-jaga)
                $existing_data = get_tradisi_by_id($pdo, $tradisi_id);
                $new_filename = $existing_data['GAMBAR_UTAMA'] ?? '';
            }

            // 2. Kumpulkan Data Lainnya
            $data = [
                'ID_TRADISI' => $tradisi_id,
                'ID_BIDANG' => filter_input(INPUT_POST, 'ID_BIDANG', FILTER_VALIDATE_INT),
                'NAMA_TRADISI' => filter_input(INPUT_POST, 'NAMA_TRADISI', FILTER_SANITIZE_STRING),
                'DESKRIPSI' => filter_input(INPUT_POST, 'DESKRIPSI', FILTER_SANITIZE_STRING),
                'GAMBAR_UTAMA' => $new_filename, // Gunakan nama file yang telah diproses
                'STATUS_PUBLIKASI' => filter_input(INPUT_POST, 'STATUS_PUBLIKASI', FILTER_SANITIZE_STRING),
                'JENIS_TRADISI' => filter_input(INPUT_POST, 'JENIS_TRADISI', FILTER_SANITIZE_STRING),
                'LOKASI_ASAL' => filter_input(INPUT_POST, 'LOKASI_ASAL', FILTER_SANITIZE_STRING),
            ];
            
            // 3. Validasi & Simpan
            if (empty($data['NAMA_TRADISI']) || empty($data['ID_BIDANG'])) {
                $status = ['success' => false, 'message' => "Nama Tradisi dan Bidang harus diisi."];
            } else {
                $status = save_tradisi($pdo, $data);
            }

        } catch (Exception $e) {
            $status = ['success' => false, 'message' => "Gagal Unggah Gambar: " . $e->getMessage()];
        }
    }
}

$tradisi_data = get_all_tradisi($pdo);
$bidang_options = get_all_bidang($pdo);

// =======================================================
// TAMPILAN HTML
// =======================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Tradisi & Tarian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .description-cell { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .table-responsive { max-height: 80vh; overflow-y: auto; }
        /* Style untuk gambar thumbnail di tabel */
        .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        .form-file-info { font-size: 0.85em; color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary"><i class="bi bi-people-fill me-2"></i> Kelola Tradisi & Tarian Adat</h1>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#tradisiModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Tradisi Baru
                </button>
                <a href="manage_budaya.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
                </a>
            </div>
        </div>

        <?php if (!empty($status)): ?>
            <div class="alert alert-<?= $status['success'] ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($status['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 15%;">Nama Tradisi</th>
                                <th style="width: 10%;">Bidang</th>
                                <th style="width: 25%;">Deskripsi</th>
                                <th style="width: 10%;">Gambar</th>
                                <th style="width: 5%;">Status</th>
                                <th style="width: 30%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tradisi_data)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Belum ada data tradisi/tarian adat.</td></tr>
                            <?php else: ?>
                                <?php foreach ($tradisi_data as $item): 
                                    $image_path = $upload_dir . htmlspecialchars($item['GAMBAR_UTAMA']);
                                    $image_url = file_exists($image_path) ? $image_path : 'https://placehold.co/50x50/cccccc/000000?text=No+Img';
                                ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($item['ID_TRADISI']) ?></th>
                                        <td><?= htmlspecialchars($item['NAMA_TRADISI']) ?></td>
                                        <td><?= htmlspecialchars($item['nama_bidang']) ?></td>
                                        <td class="description-cell" title="<?= htmlspecialchars($item['DESKRIPSI']) ?>"><?= htmlspecialchars($item['DESKRIPSI']) ?></td>
                                        <td>
                                            <img src="<?= $image_url ?>" class="img-thumb" alt="Thumbnail" onerror="this.onerror=null;this.src='https://placehold.co/50x50/cccccc/000000?text=Err';" />
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $item['STATUS_PUBLIKASI'] == 'published' ? 'success' : 'warning' ?>"><?= ucfirst($item['STATUS_PUBLIKASI']) ?></span>
                                        </td>
                                        <td>
                                            <button 
                                                class="btn btn-sm btn-info text-white me-1 mb-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#tradisiModal" 
                                                onclick='editTradisi(<?= json_encode($item) ?>)'>
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="ID_TRADISI" value="<?= htmlspecialchars($item['ID_TRADISI']) ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus Tradisi: <?= htmlspecialchars($item['NAMA_TRADISI']) ?>? Tindakan ini juga akan menghapus gambar terkait.');">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH/EDIT TRADISI -->
    <div class="modal fade" id="tradisiModal" tabindex="-1" aria-labelledby="tradisiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Tambahkan enctype="multipart/form-data" di sini -->
                <form id="tradisiForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="tradisiModalLabel">Tambah Tradisi & Tarian Adat</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="ID_TRADISI" id="ID_TRADISI">
                        <!-- Input tersembunyi untuk menyimpan nama file gambar lama saat edit -->
                        <input type="hidden" name="OLD_GAMBAR_UTAMA" id="OLD_GAMBAR_UTAMA">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="NAMA_TRADISI" class="form-label">Nama Tradisi/Tarian</label>
                                <input type="text" class="form-control" id="NAMA_TRADISI" name="NAMA_TRADISI" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ID_BIDANG" class="form-label">Bidang Kebudayaan</label>
                                <select class="form-select" id="ID_BIDANG" name="ID_BIDANG" required>
                                    <option value="">Pilih Bidang</option>
                                    <?php foreach ($bidang_options as $bidang): ?>
                                        <option value="<?= htmlspecialchars($bidang['id_bidang']) ?>"><?= htmlspecialchars($bidang['nama_bidang']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="DESKRIPSI" class="form-label">Deskripsi Lengkap</label>
                            <textarea class="form-control" id="DESKRIPSI" name="DESKRIPSI" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="LOKASI_ASAL" class="form-label">Lokasi Asal</label>
                                <input type="text" class="form-control" id="LOKASI_ASAL" name="LOKASI_ASAL">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="JENIS_TRADISI" class="form-label">Jenis Tradisi</label>
                                <input type="text" class="form-control" id="JENIS_TRADISI" name="JENIS_TRADISI" placeholder="Contoh: Tari Sakral, Upacara Adat">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="GAMBAR_UTAMA" class="form-label">Gambar Utama</label>
                                <input type="file" class="form-control" id="GAMBAR_UTAMA" name="GAMBAR_UTAMA" accept="image/*">
                                <div id="currentImageInfo" class="form-file-info mt-1"></div>
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Publikasi</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="STATUS_PUBLIKASI" id="status_draft_t" value="draft" checked>
                                    <label class="form-check-label" for="status_draft_t">Draft</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="STATUS_PUBLIKASI" id="status_published_t" value="published">
                                    <label class="form-check-label" for="status_published_t">Publikasi</label>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitButton" name="action" value="save">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uploadDir = "<?= $upload_dir ?>"; // Digunakan untuk menampilkan URL gambar
        
        function resetForm() {
            document.getElementById('tradisiForm').reset();
            document.getElementById('ID_TRADISI').value = '';
            document.getElementById('OLD_GAMBAR_UTAMA').value = ''; // Reset hidden field
            document.getElementById('tradisiModalLabel').innerText = 'Tambah Tradisi & Tarian Adat Baru';
            document.getElementById('submitButton').innerText = 'Simpan Data';
            document.getElementById('status_draft_t').checked = true;
            document.getElementById('currentImageInfo').innerHTML = ''; // Clear info
            document.getElementById('GAMBAR_UTAMA').removeAttribute('required'); // File not required on create
        }

        function editTradisi(item) {
            document.getElementById('ID_TRADISI').value = item.ID_TRADISI;
            document.getElementById('NAMA_TRADISI').value = item.NAMA_TRADISI;
            document.getElementById('ID_BIDANG').value = item.ID_BIDANG; 
            document.getElementById('DESKRIPSI').value = item.DESKRIPSI;
            document.getElementById('LOKASI_ASAL').value = item.LOKASI_ASAL;
            document.getElementById('JENIS_TRADISI').value = item.JENIS_TRADISI;
            
            // 1. Simpan nama file lama ke hidden input
            document.getElementById('OLD_GAMBAR_UTAMA').value = item.GAMBAR_UTAMA;
            
            // 2. Tampilkan info gambar saat ini
            const imageInfo = document.getElementById('currentImageInfo');
            if (item.GAMBAR_UTAMA) {
                imageInfo.innerHTML = `Gambar Saat Ini: <a href="${uploadDir}${item.GAMBAR_UTAMA}" target="_blank">${item.GAMBAR_UTAMA}</a>`;
            } else {
                imageInfo.innerHTML = 'Tidak ada gambar utama saat ini.';
            }

            // 3. Reset input file (tidak bisa diisi karena alasan keamanan browser)
            document.getElementById('GAMBAR_UTAMA').value = ''; 
            document.getElementById('GAMBAR_UTAMA').removeAttribute('required');
            
            // 4. Set status publikasi
            if (item.STATUS_PUBLIKASI === 'published') {
                document.getElementById('status_published_t').checked = true;
            } else {
                document.getElementById('status_draft_t').checked = true;
            }
            
            document.getElementById('tradisiModalLabel').innerText = 'Edit Tradisi: ' + item.NAMA_TRADISI;
            document.getElementById('submitButton').innerText = 'Perbarui Data';
        }
    </script>
</body>
</html>