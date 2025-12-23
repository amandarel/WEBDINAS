<?php
session_start();

// =======================================================
// KONFIGURASI UPLOAD
// =======================================================
$upload_dir = '../uploads/musik/';
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$max_file_size = 5 * 1024 * 1024; // 5MB

// Pastikan direktori upload ada
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        die("Gagal membuat direktori upload: $upload_dir. Harap buat secara manual dan atur izin.");
    }
}

// =======================================================
// KONEKSI DATABASE (HARUS DIGANTI)
// =======================================================
function getDBConnection() {
    $host = 'localhost'; 
    $db   = 'DISBUDPAR'; 
    $user = 'username_db'; // GANTI INI
    $pass = 'password_db'; // GANTI INI
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES      => false,
    ];
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Hentikan eksekusi dan tampilkan pesan error koneksi
        die("Koneksi Database Gagal: " . $e->getMessage()); 
    }
}
$pdo = getDBConnection(); 

// =======================================================
// FUNGSI CRUD MUSIK TRADISIONAL
// =======================================================

/**
 * Mengambil nama gambar utama berdasarkan ID. Digunakan untuk proses Update dan Delete.
 * @param PDO $pdo Koneksi PDO.
 * @param int $id ID musik.
 * @return string|null Nama file gambar atau null jika tidak ditemukan.
 */
function get_musik_gambar_utama($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT gambar_utama FROM musik_tradisional WHERE id_musik = ?");
        $stmt->execute([$id]);
        // Menggunakan fetchColumn untuk mendapatkan nilai kolom pertama
        return $stmt->fetchColumn(); 
    } catch (PDOException $e) {
        error_log("Error fetching gambar_utama: " . $e->getMessage());
        return null;
    }
}


function get_all_musik($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_musik, nama_musik, asal_daerah, jenis_alat, Deskripsi, link_audio, link_youtube, gambar_utama, tanggal_input FROM musik_tradisional ORDER BY id_musik DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching musik: " . $e->getMessage());
        return [];
    }
}

/**
 * Menyimpan atau memperbarui data musik ke database.
 * @param PDO $pdo Koneksi PDO.
 * @param array $data Data form.
 * @return array Status operasi.
 */
function save_musik($pdo, $data) {
    $data = array_map('trim', $data);

    if (empty($data['id_musik'])) {
        // CREATE
        $sql = "INSERT INTO musik_tradisional (nama_musik, asal_daerah, jenis_alat, Deskripsi, link_audio, link_youtube, gambar_utama, tanggal_input) 
                VALUES (:nama_musik, :asal_daerah, :jenis_alat, :Deskripsi, :link_audio, :link_youtube, :gambar_utama, NOW())";
        $message = "Data Musik Tradisional berhasil ditambahkan.";
    } else {
        // UPDATE
        // Cek apakah ada file baru yang diupload ($data['gambar_utama'] akan berisi nama file baru atau null jika tidak ada perubahan gambar)
        $update_gambar = !is_null($data['gambar_utama']) ? ", gambar_utama = :gambar_utama" : "";
        $sql = "UPDATE musik_tradisional SET nama_musik = :nama_musik, asal_daerah = :asal_daerah, jenis_alat = :jenis_alat, Deskripsi = :Deskripsi, link_audio = :link_audio, link_youtube = :link_youtube $update_gambar WHERE id_musik = :id_musik";
        $message = "Data Musik Tradisional berhasil diperbarui.";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $params = [
            ':nama_musik' => $data['nama_musik'],
            ':asal_daerah' => $data['asal_daerah'],
            ':jenis_alat' => $data['jenis_alat'],
            ':Deskripsi' => $data['Deskripsi'],
            ':link_audio' => $data['link_audio'],
            ':link_youtube' => $data['link_youtube'],
        ];

        if (!empty($data['id_musik'])) {
            $params[':id_musik'] = $data['id_musik'];
        }
        
        // Hanya tambahkan parameter gambar_utama jika ada perubahan (CREATE atau UPDATE dengan file baru)
        if (!is_null($data['gambar_utama'])) {
            $params[':gambar_utama'] = $data['gambar_utama'];
        }

        $stmt->execute($params);
        return ['success' => true, 'message' => $message];
    } catch (PDOException $e) {
        error_log("Error saving musik: " . $e->getMessage());
        return ['success' => false, 'message' => "Gagal menyimpan data: " . $e->getMessage()];
    }
}

/**
 * Menghapus data musik dan file gambar terkait.
 * NOTE: Nama file gambar diambil dari database, tidak dari POST request.
 * @param PDO $pdo Koneksi PDO.
 * @param int $id ID musik.
 * @return array Status operasi.
 */
function delete_musik($pdo, $id) {
    global $upload_dir;

    try {
        // 0. Ambil nama file gambar lama dari database
        $gambar_lama = get_musik_gambar_utama($pdo, $id);

        // 1. Hapus data dari database
        $stmt = $pdo->prepare("DELETE FROM musik_tradisional WHERE id_musik = ?");
        $stmt->execute([$id]);

        // 2. Hapus file gambar terkait jika ada
        if (!empty($gambar_lama) && file_exists($upload_dir . $gambar_lama)) {
            unlink($upload_dir . $gambar_lama);
        }

        return ['success' => true, 'message' => "Data Musik Tradisional ID $id berhasil dihapus."];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "Gagal menghapus data: " . $e->getMessage()];
    }
}

/**
 * Menangani proses upload file gambar.
 * @param array $file_data Data dari $_FILES['gambar_utama'].
 * @param string|null $old_file Nama file lama (diambil dari DB, untuk dihapus jika upload sukses).
 * @return array Hasil upload (success: boolean, filename: string|null, message: string|null).
 */
function handle_upload($file_data, $old_file) {
    global $upload_dir, $allowed_extensions, $max_file_size;

    // Jika tidak ada file diupload
    if (!isset($file_data['error']) || $file_data['error'] === UPLOAD_ERR_NO_FILE) {
        // Kembalikan null jika ini CREATE (tidak ada gambar) atau nama file lama jika ini UPDATE
        // NOTE: Untuk UPDATE yang tidak mengganti gambar, kita kembalikan NULL agar save_musik tidak mengupdate kolom gambar_utama.
        return ['success' => true, 'filename' => null, 'message' => null];
    }

    // Cek error upload
    if ($file_data['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => null, 'message' => "Error upload file: " . $file_data['error']];
    }

    // Validasi Tipe dan Ukuran
    $file_extension = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
    
    // Pastikan file_data['tmp_name'] ada sebelum dicek
    if (!file_exists($file_data['tmp_name'])) {
        return ['success' => false, 'filename' => null, 'message' => "File sementara tidak ditemukan."];
    }
    
    // Cek ekstensi
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'filename' => null, 'message' => "Ekstensi file tidak diizinkan. Hanya: " . implode(', ', $allowed_extensions)];
    }

    // Cek ukuran
    if ($file_data['size'] > $max_file_size) {
        return ['success' => false, 'filename' => null, 'message' => "Ukuran file terlalu besar (Max " . ($max_file_size / 1024 / 1024) . "MB)."];
    }

    // Buat nama file unik
    $new_file_name = uniqid('musik_', true) . '.' . $file_extension;
    $target_file = $upload_dir . $new_file_name;

    // Pindahkan file
    if (move_uploaded_file($file_data['tmp_name'], $target_file)) {
        // Hapus file lama jika ini adalah update (old_file dari DB) dan file lama valid
        if (!empty($old_file) && $old_file !== $new_file_name && file_exists($upload_dir . $old_file)) {
            unlink($upload_dir . $old_file);
        }
        return ['success' => true, 'filename' => $new_file_name, 'message' => "File berhasil diupload."];
    } else {
        return ['success' => false, 'filename' => null, 'message' => "Gagal memindahkan file yang diupload."];
    }
}


// =======================================================
// HANDLER FORM
// =======================================================
$status = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id_musik', FILTER_VALIDATE_INT);
        
        if ($id) { 
            // delete_musik sekarang mengambil nama file dari DB, tidak perlu gambar_lama_delete
            $status = delete_musik($pdo, $id); 
        } else { 
            $status = ['success' => false, 'message' => "ID Musik tidak valid."]; 
        }

    } elseif ($action === 'save') {
        $id_musik = filter_input(INPUT_POST, 'id_musik', FILTER_VALIDATE_INT);
        $old_gambar_utama = null; 

        // JIKA UPDATE, ambil nama file lama dari DB
        if ($id_musik) {
            $old_gambar_utama = get_musik_gambar_utama($pdo, $id_musik);
        }

        // --- 1. Handle File Upload ---
        // Gunakan $old_gambar_utama dari DB untuk proses hapus file lama (jika ada file baru)
        $upload_result = handle_upload($_FILES['gambar_utama'] ?? ['error' => UPLOAD_ERR_NO_FILE], $old_gambar_utama);
        
        if (!$upload_result['success']) {
            $status = ['success' => false, 'message' => "Gagal Upload: " . $upload_result['message']];
            // Lanjutkan ke tampilan agar pesan error muncul
        } else {
            // --- 2. Siapkan Data untuk DB ---
            $data = [
                'id_musik' => $id_musik,
                'nama_musik' => filter_input(INPUT_POST, 'nama_musik', FILTER_SANITIZE_STRING),
                'asal_daerah' => filter_input(INPUT_POST, 'asal_daerah', FILTER_SANITIZE_STRING),
                'jenis_alat' => filter_input(INPUT_POST, 'jenis_alat', FILTER_SANITIZE_STRING),
                'Deskripsi' => filter_input(INPUT_POST, 'Deskripsi', FILTER_SANITIZE_STRING),
                'link_audio' => filter_input(INPUT_POST, 'link_audio', FILTER_SANITIZE_URL),
                'link_youtube' => filter_input(INPUT_POST, 'link_youtube', FILTER_SANITIZE_URL),
                // Gunakan nama file baru dari hasil upload (null jika tidak ada file baru diupload)
                'gambar_utama' => $upload_result['filename'], 
            ];
            
            // Jika ini UPDATE dan tidak ada file baru diupload, pastikan data gambar_utama tidak dikirim ke save_musik
            if ($id_musik && is_null($data['gambar_utama'])) {
                // Di sini kita tidak menghapus key gambar_utama, tapi logic save_musik
                // sudah disesuaikan untuk mengabaikannya jika null.
                // Jika ingin lebih eksplisit, bisa diubah:
                // unset($data['gambar_utama']); // Hapus key ini jika UPDATE dan tidak ada upload baru.
            }

            if (empty($data['nama_musik']) || empty($data['asal_daerah'])) {
                $status = ['success' => false, 'message' => "Nama Musik dan Asal Daerah harus diisi."];
            } else {
                $status = save_musik($pdo, $data);
            }
        }
    }
}

$musik_data = get_all_musik($pdo);

// =======================================================
// TAMPILAN HTML
// =======================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Musik Tradisional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .description-cell { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .table-responsive { max-height: 80vh; overflow-y: auto; }
        .table thead th { position: sticky; top: 0; background-color: #f8f9fa; z-index: 10; }
        .image-preview-container { width: 100px; height: 100px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-top: 10px; }
        .image-preview { max-width: 100%; max-height: 100%; object-fit: cover; display: none; }
        .no-image { font-size: 0.8rem; color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary"><i class="bi bi-boombox me-2"></i> Kelola Musik Tradisional</h1>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#musikModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Musik Baru
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
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 15%;">Nama Musik</th>
                                <th style="width: 10%;">Asal Daerah</th>
                                <th style="width: 15%;">Jenis Alat</th>
                                <th style="width: 25%;">Deskripsi</th>
                                <th style="width: 15%;">Gambar Utama</th>
                                <th style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($musik_data)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Belum ada data musik tradisional.</td></tr>
                            <?php else: ?>
                                <?php foreach ($musik_data as $item): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($item['id_musik']) ?></th>
                                        <td><?= htmlspecialchars($item['nama_musik']) ?></td>
                                        <td><?= htmlspecialchars($item['asal_daerah']) ?></td>
                                        <td><?= htmlspecialchars($item['jenis_alat']) ?></td>
                                        <td class="description-cell" title="<?= htmlspecialchars($item['Deskripsi']) ?>"><?= htmlspecialchars($item['Deskripsi']) ?></td>
                                        <td>
                                            <?php if ($item['gambar_utama']): ?>
                                                <img src="<?= $upload_dir . htmlspecialchars($item['gambar_utama']) ?>" alt="Gambar <?= htmlspecialchars($item['nama_musik']) ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <span class="text-muted">Tidak ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button 
                                                class="btn btn-sm btn-info text-white me-1 mb-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#musikModal" 
                                                onclick='editMusik(<?= json_encode($item) ?>)'>
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger mb-1" onclick="showDeleteConfirm(<?= htmlspecialchars($item['id_musik']) ?>, '<?= htmlspecialchars($item['nama_musik']) ?>')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
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

    <div class="modal fade" id="musikModal" tabindex="-1" aria-labelledby="musikModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="musikForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="musikModalLabel">Tambah Musik Tradisional</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_musik" id="id_musik">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_musik" class="form-label">Nama Musik/Lagu</label>
                                <input type="text" class="form-control" id="nama_musik" name="nama_musik" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="asal_daerah" class="form-label">Asal Daerah</label>
                                <input type="text" class="form-control" id="asal_daerah" name="asal_daerah" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jenis_alat" class="form-label">Jenis Alat</label>
                                <input type="text" class="form-control" id="jenis_alat" name="jenis_alat" placeholder="Contoh: Kolintang, Sasando">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gambar_utama" class="form-label">Gambar Utama</label>
                                <input type="file" class="form-control" id="gambar_utama" name="gambar_utama" accept="image/*">
                                <div id="gambarPreviewBlock" style="display:none;">
                                    <small class="text-muted mt-1">Gambar saat ini:</small>
                                    <div class="image-preview-container">
                                        <img id="gambarPreview" class="image-preview" src="" alt="Preview Gambar">
                                        <span id="noImageLabel" class="no-image">Tidak Ada Gambar</span>
                                    </div>
                                    <small id="currentImageName" class="text-muted"></small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="Deskripsi" class="form-label">Deskripsi Lengkap</label>
                            <textarea class="form-control" id="Deskripsi" name="Deskripsi" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="link_audio" class="form-label">Link Audio (URL)</label>
                                <input type="url" class="form-control" id="link_audio" name="link_audio" placeholder="https://example.com/audio.mp3">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="link_youtube" class="form-label">Link Video (URL)</label>
                                <input type="url" class="form-control" id="link_youtube" name="link_youtube" placeholder="https://www.youtube.com/watch?v=...">
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
    
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_musik" id="delete_id_musik">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Anda yakin ingin menghapus data musik tradisional **<span id="deleteMusikName" class="fw-bold"></span>**?</p>
                        <p class="text-danger small">Aksi ini akan menghapus data dari database dan juga file gambar terkait.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus Permanen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gunakan nama direktori upload dari konfigurasi PHP
        const uploadDir = '<?= $upload_dir ?>';
        
        function resetForm() {
            document.getElementById('musikForm').reset();
            document.getElementById('id_musik').value = '';
            // current_gambar_utama sudah dihapus dari HTML
            document.getElementById('musikModalLabel').innerText = 'Tambah Musik Tradisional Baru';
            document.getElementById('submitButton').innerText = 'Simpan Data';
            
            // Sembunyikan preview saat mode tambah
            document.getElementById('gambarPreviewBlock').style.display = 'none';
        }

        function editMusik(item) {
            document.getElementById('id_musik').value = item.id_musik;
            document.getElementById('nama_musik').value = item.nama_musik;
            document.getElementById('asal_daerah').value = item.asal_daerah;
            document.getElementById('jenis_alat').value = item.jenis_alat;
            document.getElementById('Deskripsi').value = item.Deskripsi;
            document.getElementById('link_audio').value = item.link_audio;
            document.getElementById('link_youtube').value = item.link_youtube;
            
            // Hapus nilai input file agar tidak terkirim secara default saat update
            document.getElementById('gambar_utama').value = '';
            
            const previewBlock = document.getElementById('gambarPreviewBlock');
            const previewImg = document.getElementById('gambarPreview');
            const noImageLabel = document.getElementById('noImageLabel');
            const currentImageName = document.getElementById('currentImageName');
            
            previewBlock.style.display = 'block';

            if (item.gambar_utama) {
                previewImg.src = uploadDir + item.gambar_utama;
                previewImg.style.display = 'block';
                noImageLabel.style.display = 'none';
                currentImageName.innerText = 'File: ' + item.gambar_utama;
            } else {
                previewImg.style.display = 'none';
                noImageLabel.style.display = 'block';
                currentImageName.innerText = 'Belum ada gambar';
            }
            
            document.getElementById('musikModalLabel').innerText = 'Edit Musik: ' + item.nama_musik;
            document.getElementById('submitButton').innerText = 'Perbarui Data';
        }
        
        // Fungsi untuk menampilkan modal konfirmasi hapus
        function showDeleteConfirm(id, nama) {
            document.getElementById('delete_id_musik').value = id;
            document.getElementById('deleteMusikName').innerText = nama;
            // gambar_lama_delete sudah dihapus dari HTML dan JS
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        }

        // Opsional: Preview gambar saat memilih file baru
        document.getElementById('gambar_utama').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const previewImg = document.getElementById('gambarPreview');
            const noImageLabel = document.getElementById('noImageLabel');
            const previewBlock = document.getElementById('gambarPreviewBlock');
            
            previewBlock.style.display = 'block';

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    noImageLabel.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                // Saat file dibatalkan atau dihapus dari input file, kembalikan ke preview lama (jika ada)
                // Jika tidak, tampilkan "Tidak Ada Gambar"
                // Saat editMusik dipanggil, ia akan mengisi preview dengan gambar lama jika ada.
                previewImg.style.display = 'none';
                noImageLabel.style.display = 'block';
            }
        });

    </script>
</body>
</html>