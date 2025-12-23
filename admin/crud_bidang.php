<?php
session_start();

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
// FUNGSI CRUD BIDANG
// =======================================================

function get_all_bidang($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_bidang, nama_bidang, slug FROM bidang ORDER BY id_bidang DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching bidang: " . $e->getMessage());
        return [];
    }
}

function save_bidang($pdo, $data) {
    $nama_bidang = trim($data['nama_bidang']);
    $slug = trim($data['slug']);

    if (empty($data['id_bidang'])) {
        // CREATE
        $sql = "INSERT INTO bidang (nama_bidang, slug) VALUES (?, ?)";
        $params = [$nama_bidang, $slug];
    } else {
        // UPDATE
        $sql = "UPDATE bidang SET nama_bidang = ?, slug = ? WHERE id_bidang = ?";
        $params = [$nama_bidang, $slug, $data['id_bidang']];
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return ['success' => true, 'message' => empty($data['id_bidang']) ? "Bidang berhasil ditambahkan." : "Bidang berhasil diperbarui."];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "Gagal menyimpan: " . $e->getMessage()];
    }
}

function delete_bidang($pdo, $id) {
    try {
        // PENTING: Periksa Foreign Key Constraint di sini sebelum delete
        $stmt = $pdo->prepare("DELETE FROM bidang WHERE id_bidang = ?");
        $stmt->execute([$id]);
        return ['success' => true, 'message' => "Bidang ID $id berhasil dihapus."];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "Gagal menghapus. Pastikan tidak ada data Warisan/Tradisi yang menggunakan Bidang ini."];
    }
}

// =======================================================
// HANDLER FORM
// =======================================================
$status = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id_bidang', FILTER_VALIDATE_INT);
        if ($id) { $status = delete_bidang($pdo, $id); } else { $status = ['success' => false, 'message' => "ID tidak valid."]; }
    } elseif ($action === 'save') {
        $data = [
            'id_bidang' => filter_input(INPUT_POST, 'id_bidang', FILTER_VALIDATE_INT),
            'nama_bidang' => filter_input(INPUT_POST, 'nama_bidang', FILTER_SANITIZE_STRING),
            'slug' => filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING),
        ];
        if (empty($data['nama_bidang'])) {
            $status = ['success' => false, 'message' => "Nama Bidang tidak boleh kosong."];
        } else {
            $status = save_bidang($pdo, $data);
        }
    }
}

$bidang_data = get_all_bidang($pdo);

// =======================================================
// TAMPILAN HTML
// =======================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Bidang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary"><i class="bi bi-tag-fill me-2"></i> Manajemen Bidang Kebudayaan</h1>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#bidangModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Bidang Baru
                </button>
                <a href="manage_budaya.php" class="btn btn-secondary">
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

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th style="width: 40%;">Nama Bidang</th>
                                <th style="width: 30%;">Slug</th>
                                <th style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bidang_data as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id_bidang']) ?></td>
                                    <td><?= htmlspecialchars($item['nama_bidang']) ?></td>
                                    <td><?= htmlspecialchars($item['slug']) ?></td>
                                    <td>
                                        <button 
                                            class="btn btn-sm btn-info text-white me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#bidangModal" 
                                            onclick='editBidang(<?= json_encode($item) ?>)'>
                                            Edit
                                        </button>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_bidang" value="<?= htmlspecialchars($item['id_bidang']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus Bidang: <?= htmlspecialchars($item['nama_bidang']) ?>?');">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH/EDIT BIDANG -->
    <div class="modal fade" id="bidangModal" tabindex="-1" aria-labelledby="bidangModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="bidangForm" method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="bidangModalLabel">Tambah Bidang Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_bidang" id="id_bidang">
                        
                        <div class="mb-3">
                            <label for="nama_bidang" class="form-label">Nama Bidang</label>
                            <input type="text" class="form-control" id="nama_bidang" name="nama_bidang" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug (URL Friendly Name)</label>
                            <input type="text" class="form-control" id="slug" name="slug" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitButton" name="action" value="save">Simpan Bidang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('bidangForm').reset();
            document.getElementById('id_bidang').value = '';
            document.getElementById('bidangModalLabel').innerText = 'Tambah Bidang Baru';
            document.getElementById('submitButton').innerText = 'Simpan Bidang';
        }

        function editBidang(item) {
            document.getElementById('id_bidang').value = item.id_bidang;
            document.getElementById('nama_bidang').value = item.nama_bidang;
            document.getElementById('slug').value = item.slug;
            
            document.getElementById('bidangModalLabel').innerText = 'Edit Bidang: ' + item.nama_bidang;
            document.getElementById('submitButton').innerText = 'Perbarui Bidang';
        }
    </script>
</body>
</html>