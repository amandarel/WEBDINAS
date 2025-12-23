<?php
session_start();

// =======================================================
// KONFIGURASI PATH DAN KONEKSI DATABASE
// =======================================================

// --- KONSTANTA JALUR UPLOAD ---
// Lokasi server untuk menyimpan file:
// Asumsi: File ini ada di [ROOT_PROYEK]/admin/crud_warisan.php
// Target folder: [ROOT_PROYEK]/assets/images/uploads/warisan/
const UPLOAD_DIR_SERVER = '../uploads/warisan/'; 
// Jalur web (URL) yang disimpan di database dan digunakan oleh browser:
// HARUS dimulai dengan root URL proyek Anda: /WEBDINAS/
const UPLOAD_DIR_WEB = '../uploads/warisan/';


function getDBConnection() {
    $host = 'localhost'; 
    $db   = 'DISBUDPAR'; // Ganti nama database Anda
    // !!! WAJIB GANTI DENGAN KREDENSIAL DATABASE ASLI ANDA !!!
    $user = 'root'; 
    $pass = ''; 
    // !!! END KREDENSIAL !!!
    
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        error_log("Koneksi Database Gagal: " . $e->getMessage());
        die("Koneksi Database Gagal. Silakan periksa konfigurasi database Anda (host, nama database, username, password)."); 
    }
}
$pdo = getDBConnection(); 

// =======================================================
// FUNGSI UTAMA (BIDANG dan WARISAN)
// =======================================================

function get_all_bidang($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_bidang, nama_bidang FROM bidang ORDER BY nama_bidang ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching bidang: " . $e->getMessage());
        return [];
    }
}

function get_all_warisan($pdo) {
    try {
        // Query disesuaikan untuk mengambil data warisan dan nama bidang
        $stmt = $pdo->query("
            SELECT 
                w.ID_WARISAN, w.ID_BIDANG, w.NAMA_WARISAN, w.DESKRIPSI, w.LOKASI_FISIK, w.KOORDINAT,
                w.GAMBAR_UTAMA, w.STATUS_PUBLIKASI, 
                b.nama_bidang
            FROM warisan w
            JOIN bidang b ON w.ID_BIDANG = b.id_bidang
            ORDER BY w.TANGGAL_UPLOAD DESC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching warisan: " . $e->getMessage());
        return [];
    }
}

/**
 * Fungsi untuk mengunggah file gambar.
 * @param array $file_data Data dari $_FILES
 * @return array Hasil upload (success, message, filename)
 */
function upload_file($file_data) {
    // 1. Cek error upload
    if ($file_data['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas PHP.',
            UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas form.',
            UPLOAD_ERR_PARTIAL    => 'File terupload sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diupload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temp hilang.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk. (Error Izin Server)',
            UPLOAD_ERR_EXTENSION  => 'Ekstensi PHP menghentikan upload file.',
        ];
        $message = $error_messages[$file_data['error']] ?? 'Error upload file tidak diketahui: Code ' . $file_data['error'];
        return ['success' => false, 'message' => $message];
    }

    // 2. Cek tipe file (keamanan dasar)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file_data['type'], $allowed_types)) {
        return ['success' => false, 'message' => "Tipe file tidak didukung. Hanya JPEG, PNG, dan GIF yang diizinkan."];
    }
    
    // 3. Generate nama file unik
    $ext = pathinfo($file_data['name'], PATHINFO_EXTENSION);
    $new_file_name = uniqid('warisan_', true) . '.' . $ext;
    // PENTING: Menggunakan path server relatif (../assets/...)
    $target_path = UPLOAD_DIR_SERVER . $new_file_name; 

    // 4. Pastikan direktori upload ada dan berizin yang benar
    if (!is_dir(UPLOAD_DIR_SERVER)) {
        // Menggunakan 0755 untuk keamanan default yang lebih baik.
        // Jika gagal, coba 0777 (khusus shared hosting/Windows)
        if (!mkdir(UPLOAD_DIR_SERVER, 0755, true)) { 
            // Coba dengan izin yang lebih longgar (jika 0755 gagal)
            if (!mkdir(UPLOAD_DIR_SERVER, 0777, true)) {
                return ['success' => false, 'message' => "Gagal membuat direktori upload: " . UPLOAD_DIR_SERVER . ". Cek izin folder server."];
            }
        }
    }

    // 5. Pindahkan file
    if (move_uploaded_file($file_data['tmp_name'], $target_path)) {
        // HANYA NAMA FILE YANG DISIMPAN KE DATABASE
        return ['success' => true, 'filename' => $new_file_name];
    } else {
        return ['success' => false, 'message' => "Gagal memindahkan file yang diupload. Pastikan folder: " . UPLOAD_DIR_SERVER . " memiliki izin Tulis (Write Permission)."];
    }
}

/**
 * Menyimpan data warisan ke database, termasuk mengurus upload gambar.
 * @param PDO $pdo Koneksi database.
 * @param array $data Data form POST.
 * @param array $file_data Data file dari $_FILES.
 * @return array Hasil operasi (success, message).
 */
function save_warisan($pdo, $data, $file_data = null) {
    $data = array_map('trim', $data);
    $data['STATUS_PUBLIKASI'] = $data['STATUS_PUBLIKASI'] ?? 'draft';

    $is_update = !empty($data['ID_WARISAN']);
    $old_image_name = null;

    try {
        $pdo->beginTransaction();

        // 1. Tentukan nama gambar saat ini (jika ada update)
        if ($is_update) {
            $stmt = $pdo->prepare("SELECT GAMBAR_UTAMA FROM warisan WHERE ID_WARISAN = ?");
            $stmt->execute([$data['ID_WARISAN']]);
            $old_image_name = $stmt->fetchColumn();
            $data['GAMBAR_UTAMA'] = $old_image_name; // Default: pertahankan gambar lama
        } else {
            $data['GAMBAR_UTAMA'] = ''; // Default: tidak ada gambar untuk create
        }

        // 2. Handle upload gambar baru
        $is_file_uploaded = ($file_data && isset($file_data['GAMBAR_UTAMA']) && $file_data['GAMBAR_UTAMA']['error'] !== UPLOAD_ERR_NO_FILE);
        
        if ($is_file_uploaded) {
            $upload_result = upload_file($file_data['GAMBAR_UTAMA']);
            
            if (!$upload_result['success']) {
                $pdo->rollBack(); 
                return $upload_result; // Mengembalikan error upload
            }
            
            $data['GAMBAR_UTAMA'] = $upload_result['filename']; // Simpan NAMA FILE BARU
        } elseif (!$is_update && empty($data['GAMBAR_UTAMA'])) {
            // Jika Anda ingin mewajibkan gambar saat create, aktifkan kode ini
            /*
            $pdo->rollBack();
            return ['success' => false, 'message' => "Gambar Utama wajib diisi untuk entri baru."];
            */
        }


        // 3. Siapkan SQL
        if (!$is_update) {
            // CREATE
            $sql = "INSERT INTO warisan (ID_BIDANG, NAMA_WARISAN, DESKRIPSI, LOKASI_FISIK, KOORDINAT, GAMBAR_UTAMA, STATUS_PUBLIKASI, TANGGAL_UPLOAD, VIEW_COUNT) 
                    VALUES (:ID_BIDANG, :NAMA_WARISAN, :DESKRIPSI, :LOKASI_FISIK, :KOORDINAT, :GAMBAR_UTAMA, :STATUS_PUBLIKASI, NOW(), 0)";
        } else {
            // UPDATE
            $sql = "UPDATE warisan SET ID_BIDANG = :ID_BIDANG, NAMA_WARISAN = :NAMA_WARISAN, DESKRIPSI = :DESKRIPSI, LOKASI_FISIK = :LOKASI_FISIK, KOORDINAT = :KOORDINAT, GAMBAR_UTAMA = :GAMBAR_UTAMA, STATUS_PUBLIKASI = :STATUS_PUBLIKASI WHERE ID_WARISAN = :ID_WARISAN";
        }

        // 4. Eksekusi SQL
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ID_BIDANG' => $data['ID_BIDANG'],
            ':NAMA_WARISAN' => $data['NAMA_WARISAN'],
            ':DESKRIPSI' => $data['DESKRIPSI'],
            ':LOKASI_FISIK' => $data['LOKASI_FISIK'],
            ':KOORDINAT' => $data['KOORDINAT'],
            ':GAMBAR_UTAMA' => $data['GAMBAR_UTAMA'], // Berisi nama file
            ':STATUS_PUBLIKASI' => $data['STATUS_PUBLIKASI'],
            // Parameter ID_WARISAN hanya untuk operasi UPDATE
            ...($is_update ? [':ID_WARISAN' => $data['ID_WARISAN']] : [])
        ]);

        $pdo->commit(); // Komit transaksi

        // 5. Jika upload berhasil saat update, hapus gambar lama
        if ($is_update && $is_file_uploaded && $old_image_name && $old_image_name !== $data['GAMBAR_UTAMA']) {
             if (file_exists(UPLOAD_DIR_SERVER . $old_image_name)) {
                 // Hapus file fisik lama di folder server
                 unlink(UPLOAD_DIR_SERVER . $old_image_name);
             }
        }
        
        return ['success' => true, 'message' => $is_update ? "Data Warisan berhasil diperbarui." : "Data Warisan berhasil ditambahkan."];
    } catch (PDOException $e) {
        $pdo->rollBack(); // Batalkan transaksi jika ada error DB
        
        // Jika gagal simpan ke DB, dan file baru sudah terupload, hapus file baru tersebut (Clean up)
        if ($is_file_uploaded && isset($data['GAMBAR_UTAMA']) && file_exists(UPLOAD_DIR_SERVER . $data['GAMBAR_UTAMA'])) {
             unlink(UPLOAD_DIR_SERVER . $data['GAMBAR_UTAMA']);
        }
        // Jika error terkait UNIQUE constraint (misalnya NAMA_WARISAN), berikan pesan yang lebih spesifik
        if (strpos($e->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') !== false) {
             return ['success' => false, 'message' => "Gagal menyimpan data: Nama Warisan sudah ada."];
        }
        error_log("Error saving warisan: " . $e->getMessage());
        return ['success' => false, 'message' => "Gagal menyimpan data ke database: " . $e->getMessage()];
    }
}

function delete_warisan($pdo, $id) {
    // Ambil nama file gambar sebelum menghapus data dari DB
    $stmt = $pdo->prepare("SELECT GAMBAR_UTAMA FROM warisan WHERE ID_WARISAN = ?");
    $stmt->execute([$id]);
    $image_to_delete = $stmt->fetchColumn();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM warisan WHERE ID_WARISAN = ?");
        $stmt->execute([$id]);

        $pdo->commit();

        // Hapus file fisik setelah berhasil dihapus dari DB
        if ($image_to_delete && file_exists(UPLOAD_DIR_SERVER . $image_to_delete)) {
             unlink(UPLOAD_DIR_SERVER . $image_to_delete);
        }

        return ['success' => true, 'message' => "Data Warisan ID $id berhasil dihapus."];
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting warisan: " . $e->getMessage());
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
        $id = filter_input(INPUT_POST, 'ID_WARISAN', FILTER_VALIDATE_INT);
        if ($id) { $status = delete_warisan($pdo, $id); } else { $status = ['success' => false, 'message' => "ID Warisan tidak valid."]; }
    } elseif ($action === 'save') {
        // Ambil data POST
        $data = [
            'ID_WARISAN' => filter_input(INPUT_POST, 'ID_WARISAN', FILTER_VALIDATE_INT),
            'ID_BIDANG' => filter_input(INPUT_POST, 'ID_BIDANG', FILTER_VALIDATE_INT),
            // Menggunakan FILTER_UNSAFE_RAW untuk deskripsi agar HTML diperbolehkan jika dibutuhkan,
            // namun outputnya di HTML sudah di-escape dengan htmlspecialchars
            'NAMA_WARISAN' => filter_input(INPUT_POST, 'NAMA_WARISAN', FILTER_SANITIZE_SPECIAL_CHARS),
            'DESKRIPSI' => filter_input(INPUT_POST, 'DESKRIPSI', FILTER_UNSAFE_RAW), 
            'LOKASI_FISIK' => filter_input(INPUT_POST, 'LOKASI_FISIK', FILTER_SANITIZE_SPECIAL_CHARS),
            'KOORDINAT' => filter_input(INPUT_POST, 'KOORDINAT', FILTER_SANITIZE_SPECIAL_CHARS),
            'STATUS_PUBLIKASI' => filter_input(INPUT_POST, 'STATUS_PUBLIKASI', FILTER_SANITIZE_SPECIAL_CHARS),
        ];
        
        // Validasi wajib
        if (empty($data['NAMA_WARISAN']) || empty($data['ID_BIDANG']) || empty($data['DESKRIPSI'])) {
             $status = ['success' => false, 'message' => "Nama Warisan, Bidang, dan Deskripsi harus diisi."];
        } else {
             // Panggil save_warisan dan kirimkan juga data $_FILES
             $status = save_warisan($pdo, $data, $_FILES);
        }
    }
}

$warisan_data = get_all_warisan($pdo);
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
    <title>Admin - Kelola Warisan Budaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .description-cell { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .table-responsive { max-height: 80vh; overflow-y: auto; }
        .table-responsive thead tr th { 
            position: sticky; 
            top: 0; 
            background-color: #f8f9fa;
            z-index: 10;
        }
        .image-preview { 
            max-width: 100px; 
            max-height: 100px; 
            object-fit: cover; 
            border-radius: 4px;
        }
        /* Style untuk modal custom konfirmasi */
        .custom-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        .custom-modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary"><i class="bi bi-building-fill me-2"></i> Kelola Warisan Budaya</h1>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#warisanModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Warisan Baru
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
                                <th style="width: 15%;">Nama Warisan</th>
                                <th style="width: 10%;">Bidang</th>
                                <th style="width: 15%;">Gambar Utama</th>
                                <th style="width: 20%;">Deskripsi</th>
                                <th style="width: 10%;">Lokasi Fisik</th>
                                <th style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($warisan_data)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Belum ada data warisan budaya.</td></tr>
                            <?php else: ?>
                                <?php foreach ($warisan_data as $item): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($item['ID_WARISAN']) ?></th>
                                        <td><?= htmlspecialchars($item['NAMA_WARISAN']) ?></td>
                                        <td><?= htmlspecialchars($item['nama_bidang']) ?></td>
                                        <td>
                                            <?php if ($item['GAMBAR_UTAMA']): ?>
                                                <!-- PENTING: Menggunakan UPLOAD_DIR_WEB untuk membentuk URL yang dapat diakses oleh browser -->
                                                <img 
                                                    src="<?= UPLOAD_DIR_WEB . htmlspecialchars($item['GAMBAR_UTAMA']) ?>" 
                                                    alt="Gambar <?= htmlspecialchars($item['NAMA_WARISAN']) ?>" 
                                                    class="image-preview"
                                                    onerror="this.onerror=null;this.src='https://placehold.co/100x100/CCCCCC/000000?text=No+Img';"
                                                >
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td class="description-cell" title="<?= htmlspecialchars($item['DESKRIPSI']) ?>"><?= htmlspecialchars($item['DESKRIPSI']) ?></td>
                                        <td><?= htmlspecialchars($item['LOKASI_FISIK']) ?></td>
                                        <td>
                                            <button 
                                                class="btn btn-sm btn-info text-white me-1 mb-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#warisanModal" 
                                                onclick='editWarisan(<?= json_encode($item) ?>)'>
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-danger delete-btn" 
                                                data-id="<?= htmlspecialchars($item['ID_WARISAN']) ?>"
                                                data-name="<?= htmlspecialchars($item['NAMA_WARISAN']) ?>"
                                                onclick="showDeleteConfirmation(this)">
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

    <!-- Modal Tambah/Edit Warisan -->
    <div class="modal fade" id="warisanModal" tabindex="-1" aria-labelledby="warisanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Form mengirimkan data kembali ke file ini (self-processing) -->
                <form id="warisanForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="warisanModalLabel">Tambah Warisan Budaya Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="ID_WARISAN" id="ID_WARISAN">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="NAMA_WARISAN" class="form-label">Nama Warisan Budaya</label>
                                <input type="text" class="form-control" id="NAMA_WARISAN" name="NAMA_WARISAN" required>
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
                            <div class="col-md-6 mb-3">
                                <label for="LOKASI_FISIK" class="form-label">Lokasi Fisik/Alamat</label>
                                <input type="text" class="form-control" id="LOKASI_FISIK" name="LOKASI_FISIK" placeholder="Contoh: Jl. Merdeka No. 12, Kota X">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="KOORDINAT" class="form-label">Koordinat Lokasi (Lat, Lon)</label>
                                <input type="text" class="form-control" id="KOORDINAT" name="KOORDINAT" placeholder="Contoh: -6.2000, 106.8167">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="GAMBAR_UTAMA" class="form-label">Upload Gambar Utama (JPG, PNG, GIF)</label>
                                <input type="file" class="form-control" id="GAMBAR_UTAMA" name="GAMBAR_UTAMA" accept="image/jpeg,image/png,image/gif">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar saat edit.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Publikasi</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="STATUS_PUBLIKASI" id="status_draft_w" value="draft" checked>
                                        <label class="form-check-label" for="status_draft_w">Draft</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="STATUS_PUBLIKASI" id="status_published_w" value="published">
                                        <label class="form-check-label" for="status_published_w">Publikasi</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="currentImageInfo" class="mt-3 alert alert-warning d-none">
                            <strong>Gambar Lama:</strong> <span id="currentImageName"></span>. Abaikan kolom upload di atas jika tidak ingin mengubah gambar.
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <!-- action="save" akan diproses di handler form di PHP di file ini -->
                        <button type="submit" class="btn btn-primary" id="submitButton" name="action" value="save">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Custom Modal Konfirmasi Hapus (Menggantikan alert/confirm) -->
    <div class="custom-modal-backdrop" id="deleteConfirmationModal">
        <div class="custom-modal-content">
            <h5 class="mb-3">Konfirmasi Hapus</h5>
            <p id="deleteMessage"></p>
            <form method="POST" id="deleteForm" class="mt-4">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="ID_WARISAN" id="delete_ID_WARISAN">
                <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('deleteConfirmationModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus Permanen</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variabel global untuk path gambar (digunakan di function editWarisan jika diperlukan)
        const UPLOAD_DIR_WEB = '<?= UPLOAD_DIR_WEB ?>';

        function resetForm() {
            document.getElementById('warisanForm').reset();
            document.getElementById('ID_WARISAN').value = '';
            document.getElementById('warisanModalLabel').innerText = 'Tambah Warisan Budaya Baru';
            document.getElementById('submitButton').innerText = 'Simpan Data';
            document.getElementById('status_draft_w').checked = true;
            document.getElementById('currentImageInfo').classList.add('d-none'); // Sembunyikan info gambar lama
        }

        function editWarisan(item) {
            document.getElementById('ID_WARISAN').value = item.ID_WARISAN;
            document.getElementById('NAMA_WARISAN').value = item.NAMA_WARISAN;
            document.getElementById('ID_BIDANG').value = item.ID_BIDANG; 
            document.getElementById('DESKRIPSI').value = item.DESKRIPSI;
            document.getElementById('LOKASI_FISIK').value = item.LOKASI_FISIK;
            document.getElementById('KOORDINAT').value = item.KOORDINAT;

            // File input tidak bisa di-set nilainya, jadi kosongkan dan beri info gambar lama:
            document.getElementById('GAMBAR_UTAMA').value = null; 

            // Tampilkan info gambar lama
            if (item.GAMBAR_UTAMA) {
                document.getElementById('currentImageName').innerText = item.GAMBAR_UTAMA;
                document.getElementById('currentImageInfo').classList.remove('d-none');
            } else {
                document.getElementById('currentImageInfo').classList.add('d-none');
            }

            if (item.STATUS_PUBLIKASI === 'published') {
                document.getElementById('status_published_w').checked = true;
            } else {
                document.getElementById('status_draft_w').checked = true;
            }
            
            document.getElementById('warisanModalLabel').innerText = 'Edit Warisan: ' + item.NAMA_WARISAN;
            document.getElementById('submitButton').innerText = 'Perbarui Data';
        }
        
        // Fungsi untuk menampilkan modal konfirmasi hapus kustom
        function showDeleteConfirmation(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            
            document.getElementById('deleteMessage').innerHTML = `Yakin hapus Warisan: <strong>${name}</strong>? Semua file terkait juga akan dihapus.`;
            document.getElementById('delete_ID_WARISAN').value = id;
            document.getElementById('deleteConfirmationModal').style.display = 'flex'; // Tampilkan modal
        }
    </script>
</body>
</html>