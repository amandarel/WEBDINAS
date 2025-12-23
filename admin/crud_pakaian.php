<?php
// Tentukan direktori tempat file gambar akan disimpan
// PASTIKAN DIREKTORI INI SUDAH ADA dan BISA DITULIS (izin 775 atau 777)
const UPLOAD_DIR = '../uploads/pakaian/'; 

// Pastikan direktori UPLOAD_DIR ada.
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0775, true);
}


// =======================================================
// KONEKSI DATABASE (HARUS DIGANTI)
// =======================================================
function getDBConnection() {
    $host = 'localhost'; 
    $db   = 'DISBUDPAR'; 
    $user = 'username_db'; 
    $pass = 'password_db'; 
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
        die("Koneksi Database Gagal: " . $e->getMessage()); 
    }
}
$pdo = getDBConnection(); 

// =======================================================
// FUNGSI UTILITY (UPLOAD DAN HAPUS FILE)
// =======================================================

/**
 * Mengunggah file gambar dan mengembalikan nama file yang disimpan di server.
 * @param array $fileData Data dari $_FILES['file_foto']
 * @return string|false Nama file unik di server, atau false jika gagal.
 */
function handle_upload($fileData) {
    if ($fileData['error'] !== UPLOAD_ERR_OK) {
        return false; // Tidak ada file atau error upload
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $fileData['tmp_name']);
    finfo_close($file_info);

    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception("Jenis file tidak diizinkan. Hanya JPG, PNG, GIF, atau WEBP.");
    }
    
    // Memberikan nama file unik untuk mencegah konflik
    $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
    $new_file_name = uniqid('pakaian_', true) . '.' . $extension;
    $target_file = UPLOAD_DIR . $new_file_name;

    if (move_uploaded_file($fileData['tmp_name'], $target_file)) {
        return $new_file_name;
    } else {
        throw new Exception("Gagal memindahkan file yang diunggah.");
    }
}

/**
 * Menghapus file gambar dari server.
 * @param string $fileName Nama file di database.
 */
function delete_file_from_server($fileName) {
    $file_path = UPLOAD_DIR . $fileName;
    if (!empty($fileName) && file_exists($file_path)) {
        unlink($file_path);
    }
}

// =======================================================
// FUNGSI CRUD PAKAIAN ADAT
// =======================================================

function get_pakaian_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT nama_file_foto FROM pakaianadatlengkap_minahasa WHERE id_pakaian = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_all_pakaian($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM pakaianadatlengkap_minahasa ORDER BY id_pakaian DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching pakaian: " . $e->getMessage());
        return [];
    }
}

function save_pakaian($pdo, $data, $newFileName = null) {
    $data = array_map('trim', $data);

    $is_update = !empty($data['id_pakaian']);

    // Tentukan nama file foto yang akan disimpan (prioritas: file baru > file lama)
    $final_file_name = $newFileName !== null ? $newFileName : $data['nama_file_foto_lama'];


    if (!$is_update) {
        // CREATE
        $sql = "INSERT INTO pakaianadatlengkap_minahasa (nama_pakaian, deskripsi, gender, acara_penggunaan, makna_filosofis, bahan_dasar, daftar_aksesoris, nama_file_foto, lokasi_asal) 
                 VALUES (:nama_pakaian, :deskripsi, :gender, :acara_penggunaan, :makna_filosofis, :bahan_dasar, :daftar_aksesoris, :nama_file_foto, :lokasi_asal)";
    } else {
        // UPDATE
        $sql = "UPDATE pakaianadatlengkap_minahasa SET nama_pakaian = :nama_pakaian, deskripsi = :deskripsi, gender = :gender, acara_penggunaan = :acara_penggunaan, makna_filosofis = :makna_filosofis, bahan_dasar = :bahan_dasar, daftar_aksesoris = :daftar_aksesoris, nama_file_foto = :nama_file_foto, lokasi_asal = :lokasi_asal WHERE id_pakaian = :id_pakaian";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $params = [
            ':nama_pakaian' => $data['nama_pakaian'],
            ':deskripsi' => $data['deskripsi'],
            ':gender' => $data['gender'],
            ':acara_penggunaan' => $data['acara_penggunaan'],
            ':makna_filosofis' => $data['makna_filosofis'],
            ':bahan_dasar' => $data['bahan_dasar'],
            ':daftar_aksesoris' => $data['daftar_aksesoris'],
            ':nama_file_foto' => $final_file_name, // Menggunakan nama file yang sudah diproses
            ':lokasi_asal' => $data['lokasi_asal'],
        ];

        if ($is_update) {
            $params[':id_pakaian'] = $data['id_pakaian'];
        }

        $stmt->execute($params);
        return ['success' => true, 'message' => $is_update ? "Data Pakaian Adat berhasil diperbarui." : "Data Pakaian Adat berhasil ditambahkan."];
    } catch (PDOException $e) {
        error_log("Error saving pakaian: " . $e->getMessage());
        // Jika ada file baru yang diunggah tapi penyimpanan DB gagal, hapus file tersebut
        if ($newFileName !== null) {
            delete_file_from_server($newFileName);
        }
        return ['success' => false, 'message' => "Gagal menyimpan data: " . $e->getMessage()];
    }
}

function delete_pakaian($pdo, $id) {
    try {
        // 1. Ambil nama file foto lama sebelum dihapus
        $pakaian = get_pakaian_by_id($pdo, $id);
        $oldFileName = $pakaian ? $pakaian['nama_file_foto'] : null;

        // 2. Hapus data dari database
        $stmt = $pdo->prepare("DELETE FROM pakaianadatlengkap_minahasa WHERE id_pakaian = ?");
        $stmt->execute([$id]);

        // 3. Hapus file dari server
        if ($stmt->rowCount() > 0) {
            delete_file_from_server($oldFileName);
        }
        
        return ['success' => true, 'message' => "Data Pakaian Adat ID $id berhasil dihapus."];
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
        $id = filter_input(INPUT_POST, 'id_pakaian', FILTER_VALIDATE_INT);
        if ($id) { $status = delete_pakaian($pdo, $id); } else { $status = ['success' => false, 'message' => "ID Pakaian tidak valid."]; }
    } elseif ($action === 'save') {
        
        $newFileName = null;
        $fileUploadSuccess = true;

        try {
            // Cek apakah ada file baru yang diunggah
            if (isset($_FILES['file_foto']) && $_FILES['file_foto']['error'] === UPLOAD_ERR_OK) {
                // Proses upload file
                $newFileName = handle_upload($_FILES['file_foto']);
                // Jika ini adalah update dan file baru diunggah, hapus file lama
                if (!empty($_POST['id_pakaian']) && !empty($_POST['nama_file_foto_lama'])) {
                    delete_file_from_server($_POST['nama_file_foto_lama']);
                }
            } elseif (!empty($_POST['id_pakaian']) && empty($_POST['nama_file_foto_lama']) && $_FILES['file_foto']['error'] === UPLOAD_ERR_NO_FILE) {
                // Kondisi: Update, tidak ada file lama di DB, dan tidak ada file baru diunggah
                $status = ['success' => false, 'message' => "Harap unggah file foto untuk item baru atau perbarui file yang sudah ada."];
                $fileUploadSuccess = false;
            }

            if ($fileUploadSuccess) {
                $data = [
                    'id_pakaian' => filter_input(INPUT_POST, 'id_pakaian', FILTER_VALIDATE_INT),
                    'nama_pakaian' => filter_input(INPUT_POST, 'nama_pakaian', FILTER_SANITIZE_STRING),
                    'deskripsi' => filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING),
                    'gender' => filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING),
                    'acara_penggunaan' => filter_input(INPUT_POST, 'acara_penggunaan', FILTER_SANITIZE_STRING),
                    'makna_filosofis' => filter_input(INPUT_POST, 'makna_filosofis', FILTER_SANITIZE_STRING),
                    'bahan_dasar' => filter_input(INPUT_POST, 'bahan_dasar', FILTER_SANITIZE_STRING),
                    'daftar_aksesoris' => filter_input(INPUT_POST, 'daftar_aksesoris', FILTER_SANITIZE_STRING),
                    // nama_file_foto_lama digunakan untuk rujukan nama file saat update tanpa upload file baru
                    'nama_file_foto_lama' => filter_input(INPUT_POST, 'nama_file_foto_lama', FILTER_SANITIZE_STRING),
                    'lokasi_asal' => filter_input(INPUT_POST, 'lokasi_asal', FILTER_SANITIZE_STRING),
                ];
                
                if (empty($data['nama_pakaian'])) {
                    $status = ['success' => false, 'message' => "Nama Pakaian harus diisi."];
                } else {
                    // Panggil fungsi save dengan nama file baru (jika ada)
                    $status = save_pakaian($pdo, $data, $newFileName);
                }
            }

        } catch (Exception $e) {
            $status = ['success' => false, 'message' => "Kesalahan Upload: " . $e->getMessage()];
        }
    }
}

$pakaian_data = get_all_pakaian($pdo);

// =======================================================
// TAMPILAN HTML
// =======================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Pakaian Adat Minahasa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .description-cell { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .table-responsive { max-height: 80vh; overflow-y: auto; }
        .pakaian-img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary"><i class="bi bi-sunglasses me-2"></i> Kelola Pakaian Adat Minahasa</h1>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#pakaianModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Pakaian Baru
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
                                <th style="width: 15%;">Nama Pakaian</th>
                                <th style="width: 8%;">Foto</th>
                                <th style="width: 8%;">Gender</th>
                                <th style="width: 20%;">Makna Filosofis</th>
                                <th style="width: 15%;">Aksesoris</th>
                                <th style="width: 10%;">Acara</th>
                                <th style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pakaian_data)): ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada data pakaian adat.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pakaian_data as $item): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($item['id_pakaian']) ?></th>
                                        <td><?= htmlspecialchars($item['nama_pakaian']) ?></td>
                                        <td>
                                            <?php if (!empty($item['nama_file_foto'])): ?>
                                                <img src="<?= UPLOAD_DIR . htmlspecialchars($item['nama_file_foto']) ?>" 
                                                     alt="<?= htmlspecialchars($item['nama_pakaian']) ?>" 
                                                     class="pakaian-img" 
                                                     onerror="this.onerror=null; this.src='https://placehold.co/50x50/f5f5f5/888?text=No+Img'">
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['gender']) ?></td>
                                        <td class="description-cell" title="<?= htmlspecialchars($item['makna_filosofis']) ?>"><?= htmlspecialchars($item['makna_filosofis']) ?></td>
                                        <td class="description-cell" title="<?= htmlspecialchars($item['daftar_aksesoris']) ?>"><?= htmlspecialchars($item['daftar_aksesoris']) ?></td>
                                        <td><?= htmlspecialchars($item['acara_penggunaan']) ?></td>
                                        <td>
                                            <button 
                                                class="btn btn-sm btn-info text-white me-1 mb-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#pakaianModal" 
                                                onclick='editPakaian(<?= json_encode($item) ?>)'>
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <!-- Menggunakan modal custom untuk konfirmasi hapus karena larangan alert() -->
                                            <button 
                                                class="btn btn-sm btn-danger mb-1" 
                                                onclick="showDeleteConfirmation(<?= htmlspecialchars($item['id_pakaian']) ?>, '<?= htmlspecialchars($item['nama_pakaian'], ENT_QUOTES) ?>')">
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

    <!-- MODAL TAMBAH/EDIT PAKAIAN -->
    <!-- TAMBAH enctype="multipart/form-data" -->
    <div class="modal fade" id="pakaianModal" tabindex="-1" aria-labelledby="pakaianModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="pakaianForm" method="POST" enctype="multipart/form-data"> 
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="pakaianModalLabel">Tambah Pakaian Adat Minahasa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_pakaian" id="id_pakaian">
                        <input type="hidden" name="nama_file_foto_lama" id="nama_file_foto_lama"> <!-- Input untuk menyimpan nama file lama -->
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_pakaian" class="form-label">Nama Pakaian</label>
                                <input type="text" class="form-control" id="nama_pakaian" name="nama_pakaian" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Pilih Gender</option>
                                    <option value="Pria">Pria</option>
                                    <option value="Wanita">Wanita</option>
                                    <option value="Unisex">Unisex</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi Lengkap</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="acara_penggunaan" class="form-label">Acara Penggunaan</label>
                                <input type="text" class="form-control" id="acara_penggunaan" name="acara_penggunaan" placeholder="Contoh: Pernikahan, Upacara Adat">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bahan_dasar" class="form-label">Bahan Dasar</label>
                                <input type="text" class="form-control" id="bahan_dasar" name="bahan_dasar" placeholder="Contoh: Kain tenun, serat Nenas">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="makna_filosofis" class="form-label">Makna Filosofis</label>
                            <textarea class="form-control" id="makna_filosofis" name="makna_filosofis" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="daftar_aksesoris" class="form-label">Daftar Aksesoris</label>
                                <input type="text" class="form-control" id="daftar_aksesoris" name="daftar_aksesoris" placeholder="Contoh: Kalung, mahkota, selendang">
                            </div>
                            <!-- Mengganti input text dengan input file -->
                            <div class="col-md-6 mb-3">
                                <label for="file_foto" class="form-label">Unggah File Foto (.jpg, .png, .gif, .webp)</label>
                                <input type="file" class="form-control" id="file_foto" name="file_foto" accept="image/jpeg,image/png,image/gif,image/webp">
                                <small id="current_foto" class="form-text text-muted mt-2 d-block"></small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="lokasi_asal" class="form-label">Lokasi Asal</label>
                            <input type="text" class="form-control" id="lokasi_asal" name="lokasi_asal" value="Minahasa" required>
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

    <!-- MODAL KONFIRMASI HAPUS (Pengganti confirm()) -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus Data</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data pakaian adat: <strong id="pakaianNameToDelete"></strong>?</p>
                    <small class="text-danger">Aksi ini juga akan menghapus file foto dari server.</small>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_pakaian" id="id_pakaian_delete">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus Permanen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const UPLOAD_DIR = '<?= UPLOAD_DIR ?>'; // Ambil path UPLOAD_DIR dari PHP

        function resetForm() {
            document.getElementById('pakaianForm').reset();
            document.getElementById('id_pakaian').value = '';
            document.getElementById('nama_file_foto_lama').value = ''; 
            document.getElementById('current_foto').innerHTML = ''; // Kosongkan info foto
            document.getElementById('file_foto').required = true; // File required untuk CREATE
            document.getElementById('pakaianModalLabel').innerText = 'Tambah Pakaian Adat Minahasa Baru';
            document.getElementById('submitButton').innerText = 'Simpan Data';
        }

        function editPakaian(item) {
            document.getElementById('id_pakaian').value = item.id_pakaian;
            document.getElementById('nama_pakaian').value = item.nama_pakaian;
            document.getElementById('deskripsi').value = item.deskripsi;
            document.getElementById('gender').value = item.gender; 
            document.getElementById('acara_penggunaan').value = item.acara_penggunaan;
            document.getElementById('makna_filosofis').value = item.makna_filosofis;
            document.getElementById('bahan_dasar').value = item.bahan_dasar;
            document.getElementById('daftar_aksesoris').value = item.daftar_aksesoris;
            document.getElementById('lokasi_asal').value = item.lokasi_asal;
            
            // Simpan nama file foto yang sudah ada ke input hidden
            document.getElementById('nama_file_foto_lama').value = item.nama_file_foto; 

            // Atur status input file
            document.getElementById('file_foto').required = false; // File tidak wajib diisi saat edit
            document.getElementById('file_foto').value = ''; // Kosongkan input file

            // Tampilkan info foto saat ini
            const fotoInfo = document.getElementById('current_foto');
            if (item.nama_file_foto) {
                fotoInfo.innerHTML = `Foto saat ini: <a href="${UPLOAD_DIR}${item.nama_file_foto}" target="_blank">${item.nama_file_foto}</a> <br> (Unggah baru untuk mengganti)`;
            } else {
                fotoInfo.innerHTML = 'Belum ada foto. (Unggah foto baru)';
            }
            
            document.getElementById('pakaianModalLabel').innerText = 'Edit Pakaian Adat: ' + item.nama_pakaian;
            document.getElementById('submitButton').innerText = 'Perbarui Data';
        }
        
        // Fungsi untuk menampilkan modal konfirmasi hapus
        function showDeleteConfirmation(id, name) {
            document.getElementById('pakaianNameToDelete').innerText = name;
            document.getElementById('id_pakaian_delete').value = id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>