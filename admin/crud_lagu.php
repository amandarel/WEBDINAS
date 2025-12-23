<?php
session_start();

// =======================================================
// KONFIGURASI FILE UPLOAD
// =======================================================
// PERINGATAN: Pastikan direktori ini ada dan memiliki izin tulis (misalnya 777)
const UPLOAD_DIR = '../uploads/lagu/'; 

// Utility: Pastikan direktori upload ada. Jika tidak, coba buat.
if (!is_dir(UPLOAD_DIR)) {
    // Coba buat direktori. Opsi 'true' mengizinkan pembuatan direktori secara rekursif
    if (!mkdir(UPLOAD_DIR, 0777, true)) {
        // Jika gagal membuat, hentikan eksekusi dan berikan pesan error
        die("Gagal membuat direktori upload: " . UPLOAD_DIR . ". Cek izin folder induk.");
    }
}

// =======================================================
// KONEKSI DATABASE (HARUS DIGANTI)
// =======================================================
function getDBConnection() {
    // HARAP GANTI NILAI BERIKUT DENGAN KREDENSIAL DATABASE ANDA YANG SEBENARNYA
    $host = 'localhost'; 
    $db   = 'DISBUDPAR'; 
    $user = 'root'; // GANTI INI
    $pass = ''; // GANTI INI (kosongkan jika tidak ada password)
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE             => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES    => false,
    ];
    try {
        // Coba buat koneksi
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // Hentikan eksekusi jika koneksi gagal
        die("Koneksi Database Gagal: " . $e->getMessage()); 
    }
}
$pdo = getDBConnection(); 

// =======================================================
// FUNGSI UTILITY FILE
// =======================================================

function get_old_foto_utama($pdo, $id) {
    // Mengambil nama file foto lama berdasarkan ID_Lagu
    $stmt = $pdo->prepare("SELECT Foto_Utama FROM lagudaerah WHERE ID_Lagu = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result['Foto_Utama'] ?? null;
}

// Fungsi untuk mendapatkan pesan kesalahan upload yang lebih deskriptif
function getUploadErrorMessage($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return "File terlalu besar (melebihi batas upload PHP). Cek php.ini.";
        case UPLOAD_ERR_FORM_SIZE:
            return "File terlalu besar (melebihi batas form HTML).";
        case UPLOAD_ERR_PARTIAL:
            return "File hanya terupload sebagian.";
        case UPLOAD_ERR_NO_FILE:
            return "Tidak ada file yang diupload.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Folder sementara server hilang.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Gagal menulis file ke disk (Izin server salah).";
        case UPLOAD_ERR_EXTENSION:
            return "Ekstensi PHP menghentikan upload file.";
        default:
            return "Kesalahan upload yang tidak diketahui.";
    }
}


// =======================================================
// FUNGSI CRUD LAGU DAERAH
// =======================================================

function get_all_lagu($pdo) {
    try {
        $stmt = $pdo->query("SELECT ID_Lagu, Judul_Lagu, Foto_Utama, Deskripsi, link_youtube, link_audio, view_count FROM lagudaerah ORDER BY ID_Lagu DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching lagu: " . $e->getMessage());
        return [];
    }
}

function save_lagu($pdo, $data, $file_input) {
    // Sanitasi data
    $judul_lagu = filter_var(trim($data['Judul_Lagu']), FILTER_SANITIZE_STRING);
    $deskripsi = filter_var(trim($data['Deskripsi']), FILTER_SANITIZE_STRING);
    $link_youtube = filter_var(trim($data['link_youtube']), FILTER_SANITIZE_URL);
    $link_audio = filter_var(trim($data['link_audio']), FILTER_SANITIZE_URL);
    $id_lagu = filter_var($data['ID_Lagu'], FILTER_VALIDATE_INT);
    
    // Default nama foto adalah nama foto yang sudah ada (jika update)
    $foto_utama = $data['Foto_Utama_Existing'] ?? ''; 

    // --- LOGIKA UPLOAD FILE BARU ---
    // Cek apakah ada file yang diupload
    if (isset($file_input) && $file_input['error'] !== UPLOAD_ERR_NO_FILE) {
        
        // Cek jika terjadi error upload selain 'tidak ada file'
        if ($file_input['error'] !== UPLOAD_ERR_OK) {
             // Langsung kembalikan pesan error spesifik dari PHP
             return ['success' => false, 'message' => "Gagal Upload File: " . getUploadErrorMessage($file_input['error'])];
        }

        $file_tmp_name = $file_input['tmp_name'];
        $file_extension = strtolower(pathinfo($file_input['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            return ['success' => false, 'message' => "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF."];
        }

        // Generate nama file unik
        $new_file_name = uniqid('lagu_', true) . '.' . $file_extension;
        $upload_path = UPLOAD_DIR . $new_file_name;

        // Pindahkan file dari folder sementara ke folder tujuan
        if (move_uploaded_file($file_tmp_name, $upload_path)) {
            $foto_utama = $new_file_name; // Menggunakan nama file baru
            
            // Jika ini operasi update dan ada file lama, hapus file lama
            if (!empty($id_lagu) && !empty($data['Foto_Utama_Existing'])) {
                $old_file_path = UPLOAD_DIR . $data['Foto_Utama_Existing'];
                // Pastikan file lama berbeda dengan yang baru dan ada di server
                if (file_exists($old_file_path) && $data['Foto_Utama_Existing'] !== $new_file_name) {
                    unlink($old_file_path);
                }
            }
        } else {
            // Ini adalah titik kegagalan yang paling mungkin: masalah izin folder
            return ['success' => false, 'message' => "Gagal memindahkan file gambar ke folder tujuan. Pastikan folder " . UPLOAD_DIR . " memiliki izin tulis (seperti 777) atau dicek secara manual."];
        }
    }
    // --- AKHIR LOGIKA UPLOAD FILE BARU ---
    
    if (empty($id_lagu)) {
        // CREATE
        $sql = "INSERT INTO lagudaerah (Judul_Lagu, Foto_Utama, Deskripsi, link_youtube, link_audio, view_count) 
                 VALUES (:Judul_Lagu, :Foto_Utama, :Deskripsi, :link_youtube, :link_audio, 0)";
    } else {
        // UPDATE
        $sql = "UPDATE lagudaerah SET Judul_Lagu = :Judul_Lagu, Foto_Utama = :Foto_Utama, Deskripsi = :Deskripsi, link_youtube = :link_youtube, link_audio = :link_audio WHERE ID_Lagu = :ID_Lagu";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $params = [
            ':Judul_Lagu' => $judul_lagu,
            ':Foto_Utama' => $foto_utama, // Nama file di database
            ':Deskripsi' => $deskripsi,
            ':link_youtube' => $link_youtube,
            ':link_audio' => $link_audio,
        ];
        
        if (!empty($id_lagu)) {
            $params[':ID_Lagu'] = $id_lagu;
        }

        $stmt->execute($params);
        return ['success' => true, 'message' => empty($id_lagu) ? "Data Lagu Daerah berhasil ditambahkan." : "Data Lagu Daerah berhasil diperbarui."];
    } catch (PDOException $e) {
        // Jika gagal menyimpan, dan ada file baru yang terupload, hapus file baru tersebut agar tidak menjadi 'sampah'
        if (isset($new_file_name) && !empty($new_file_name) && file_exists(UPLOAD_DIR . $new_file_name)) {
            unlink(UPLOAD_DIR . $new_file_name);
        }
        error_log("Error saving lagu: " . $e->getMessage());
        return ['success' => false, 'message' => "Gagal menyimpan data ke database: " . $e->getMessage()];
    }
}

function delete_lagu($pdo, $id) {
    try {
        // 1. Ambil nama file foto lama
        $old_foto_name = get_old_foto_utama($pdo, $id);

        // 2. Hapus record dari database
        $stmt = $pdo->prepare("DELETE FROM lagudaerah WHERE ID_Lagu = ?");
        $stmt->execute([$id]);
        
        // 3. Hapus file fisik jika ada
        if ($old_foto_name) {
            $file_path = UPLOAD_DIR . $old_foto_name;
            if (file_exists($file_path)) {
                // Hapus file dari server
                unlink($file_path);
            }
        }

        return ['success' => true, 'message' => "Data Lagu Daerah ID $id berhasil dihapus."];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "Gagal menghapus data: " . $e->getMessage()];
    }
}

// =======================================================
// HANDLER FORM
// =======================================================
$status = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'ID_Lagu', FILTER_VALIDATE_INT);
        if ($id) { $status = delete_lagu($pdo, $id); } else { $status = ['success' => false, 'message' => "ID Lagu tidak valid."]; }
    } elseif ($action === 'save') {
        // Ambil data dari POST
        $data = [
            'ID_Lagu' => filter_input(INPUT_POST, 'ID_Lagu', FILTER_VALIDATE_INT),
            'Judul_Lagu' => filter_input(INPUT_POST, 'Judul_Lagu', FILTER_SANITIZE_STRING),
            // Input tersembunyi untuk nama file yang sudah ada (saat edit)
            'Foto_Utama_Existing' => filter_input(INPUT_POST, 'Foto_Utama_Existing', FILTER_SANITIZE_STRING), 
            'Deskripsi' => filter_input(INPUT_POST, 'Deskripsi', FILTER_SANITIZE_STRING),
            'link_youtube' => filter_input(INPUT_POST, 'link_youtube', FILTER_SANITIZE_URL),
            'link_audio' => filter_input(INPUT_POST, 'link_audio', FILTER_SANITIZE_URL),
        ];
        
        // Ambil data file yang diupload (gunakan $_FILES)
        $file_input = $_FILES['Foto_Utama'] ?? null; 

        if (empty($data['Judul_Lagu'])) {
             $status = ['success' => false, 'message' => "Judul Lagu harus diisi."];
        } else {
             // Panggil save_lagu dengan data POST dan data FILE
             $status = save_lagu($pdo, $data, $file_input);
        }
    }
}

$lagu_data = get_all_lagu($pdo);

// =======================================================
// TAMPILAN HTML
// =======================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Lagu Daerah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .description-cell { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .table-responsive-custom { max-height: 70vh; overflow-y: auto; }
        .card { border-radius: 12px; }
        .modal-content { border-radius: 12px; }
        .current-image-preview { font-size: 0.9em; margin-top: 5px; }
        .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary"><i class="bi bi-mic-fill me-2"></i> Kelola Lagu Daerah</h1>
            <div>
                <button class="btn btn-success me-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#laguModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Lagu Baru
                </button>
                <!-- Ganti manage_budaya.php dengan link yang sesuai -->
                <a href="manage_budaya.php" class="btn btn-secondary rounded-pill">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <?php if (!empty($status)): ?>
            <div class="alert alert-<?= $status['success'] ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($status['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-lg">
            <div class="card-body">
                <div class="table-responsive-custom">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary sticky-top">
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 15%;">Foto</th>
                                <th style="width: 20%;">Judul Lagu</th>
                                <th style="width: 30%;">Deskripsi</th>
                                <th style="width: 10%;">Media</th>
                                <th style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lagu_data)): ?>
                                <tr><td colspan="6" class="text-center text-muted p-4">Belum ada data lagu daerah. Klik 'Tambah Lagu Baru' untuk memulai.</td></tr>
                            <?php else: ?>
                                <?php foreach ($lagu_data as $item): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($item['ID_Lagu']) ?></th>
                                        <td>
                                            <?php if ($item['Foto_Utama']): ?>
                                                <!-- Pastikan path ke file upload sudah benar relatif terhadap file PHP ini -->
                                                <img src="<?= UPLOAD_DIR . htmlspecialchars($item['Foto_Utama']) ?>" 
                                                    alt="Foto <?= htmlspecialchars($item['Judul_Lagu']) ?>" 
                                                    class="img-thumb"
                                                    onerror="this.onerror=null; this.src='https://placehold.co/50x50/ccc/white?text=No+Img';">
                                            <?php else: ?>
                                                <i class="bi bi-image-fill text-muted" title="Tidak ada foto"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['Judul_Lagu']) ?></td>
                                        <td class="description-cell" title="<?= htmlspecialchars($item['Deskripsi']) ?>"><?= htmlspecialchars($item['Deskripsi']) ?></td>
                                        <td>
                                            <?php if ($item['link_audio']): ?><i class="bi bi-mic-fill text-primary me-2" title="Ada Audio"></i><?php endif; ?>
                                            <?php if ($item['link_youtube']): ?><i class="bi bi-youtube text-danger" title="Ada Video YouTube"></i><?php endif; ?>
                                        </td>
                                        <td>
                                            <button 
                                                class="btn btn-sm btn-info text-white me-1 mb-1 rounded-pill" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#laguModal" 
                                                onclick='editLagu(<?= json_encode($item) ?>)'>
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-danger mb-1 rounded-pill delete-btn"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteConfirmModal"
                                                data-lagu-id="<?= htmlspecialchars($item['ID_Lagu']) ?>"
                                                data-lagu-judul="<?= htmlspecialchars($item['Judul_Lagu']) ?>">
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

    <!-- MODAL TAMBAH/EDIT LAGU -->
    <div class="modal fade" id="laguModal" tabindex="-1" aria-labelledby="laguModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- PENTING: Pastikan ada enctype="multipart/form-data" untuk upload file -->
                <form id="laguForm" method="POST" enctype="multipart/form-data"> 
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="laguModalLabel">Tambah Lagu Daerah Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="ID_Lagu" id="ID_Lagu">
                        <!-- Input tersembunyi untuk menyimpan nama file yang sudah ada saat update -->
                        <input type="hidden" name="Foto_Utama_Existing" id="Foto_Utama_Existing"> 
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="Judul_Lagu" class="form-label">Judul Lagu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Judul_Lagu" name="Judul_Lagu" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <!-- PENTING: Pastikan name="Foto_Utama" -->
                                <label for="Foto_Utama" class="form-label">Upload Foto Utama (JPG, PNG, GIF)</label>
                                <input type="file" class="form-control" id="Foto_Utama" name="Foto_Utama">
                                <!-- Preview untuk file yang sudah ada -->
                                <div id="currentFotoUtama" class="current-image-preview"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="Deskripsi" class="form-label">Deskripsi/Lirik (Opsional)</label>
                            <textarea class="form-control" id="Deskripsi" name="Deskripsi" rows="4"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="link_audio" class="form-label">Link Audio (URL MP3)</label>
                                <input type="url" class="form-control" id="link_audio" name="link_audio" placeholder="https://example.com/audio.mp3">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="link_youtube" class="form-label">Link YouTube (URL Video)</label>
                                <input type="url" class="form-control" id="link_youtube" name="link_youtube" placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill" id="submitButton" name="action" value="save">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- MODAL KONFIRMASI HAPUS (Pengganti confirm()) -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Anda yakin ingin menghapus data Lagu Daerah:</p>
                        <p class="fw-bold" id="laguJudulPlaceholder"></p>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="ID_Lagu" id="delete_ID_Lagu">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-pill">Ya, Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Direktori upload untuk ditampilkan di browser
        const UPLOAD_DIR_JS = '<?= UPLOAD_DIR ?>';

        // Fungsi untuk mereset form tambah/edit
        function resetForm() {
            // Reset formulir ke kondisi awal
            document.getElementById('laguForm').reset();
            
            // Hapus nilai ID dan foto yang tersimpan
            document.getElementById('ID_Lagu').value = '';
            document.getElementById('Foto_Utama_Existing').value = '';
            
            // Hapus preview foto yang ada
            document.getElementById('currentFotoUtama').innerHTML = ''; 
            
            // Set judul modal dan tombol untuk operasi tambah
            document.getElementById('laguModalLabel').innerText = 'Tambah Lagu Daerah Baru';
            document.getElementById('submitButton').innerText = 'Simpan Data';
        }

        // Fungsi untuk mengisi form saat mengedit
        function editLagu(item) {
            // Panggil resetForm terlebih dahulu untuk membersihkan sisa data sebelumnya
            resetForm(); 

            // Mengisi field input dengan data dari item
            document.getElementById('ID_Lagu').value = item.ID_Lagu || '';
            document.getElementById('Judul_Lagu').value = item.Judul_Lagu || '';
            document.getElementById('Deskripsi').value = item.Deskripsi || '';
            document.getElementById('link_youtube').value = item.link_youtube || '';
            document.getElementById('link_audio').value = item.link_audio || '';
            
            // Mengisi field tersembunyi dengan nama file yang sudah ada
            document.getElementById('Foto_Utama_Existing').value = item.Foto_Utama || ''; 
            
            // Menampilkan preview foto yang sudah ada
            const currentFotoUtamaDiv = document.getElementById('currentFotoUtama');
            if (item.Foto_Utama) {
                currentFotoUtamaDiv.innerHTML = `
                    <p class="text-muted mb-0">Foto saat ini: 
                        <a href="${UPLOAD_DIR_JS}${item.Foto_Utama}" target="_blank" class="fw-bold text-decoration-none">${item.Foto_Utama}</a>
                        <br><span class="text-warning">Kosongkan field di atas jika Anda tidak ingin mengganti foto ini.</span>
                    </p>`;
            } else {
                currentFotoUtamaDiv.innerHTML = '<span class="text-warning">Belum ada foto yang terunggah.</span>';
            }

            // Update judul modal dan tombol
            document.getElementById('laguModalLabel').innerText = 'Edit Lagu: ' + (item.Judul_Lagu || 'ID ' + item.ID_Lagu);
            document.getElementById('submitButton').innerText = 'Perbarui Data';
        }

        // Script untuk menangani modal konfirmasi hapus
        document.addEventListener('DOMContentLoaded', function () {
            const deleteConfirmModal = document.getElementById('deleteConfirmModal');
            deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
                // Button that triggered the modal
                const button = event.relatedTarget;
                
                // Extract info from data-* attributes
                const laguId = button.getAttribute('data-lagu-id');
                const laguJudul = button.getAttribute('data-lagu-judul');

                // Update the modal's content
                const modalIdInput = deleteConfirmModal.querySelector('#delete_ID_Lagu');
                const modalJudulPlaceholder = deleteConfirmModal.querySelector('#laguJudulPlaceholder');
                
                modalIdInput.value = laguId;
                modalJudulPlaceholder.textContent = laguJudul;
            });
        });
    </script>
</body>
</html>