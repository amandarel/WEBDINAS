<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pesan Masuk Real-Time</title>
    <!-- Memuat Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Memuat Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        /* Gaya Kustom */
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            background-color: #f4f6f9; 
        }
        .message-card { 
            transition: all 0.3s ease; 
            border-radius: 0.75rem;
            overflow: hidden;
        }
        /* Style untuk pesan BELUM DIBACA */
        .unread { 
            border-left: 5px solid #dc3545; /* Garis Merah */
            background-color: #fff8f8; /* Background sangat lembut */
        }
        /* Style untuk pesan SUDAH DIBACA */
        .read { 
            border-left: 5px solid #0d6efd; /* Garis Biru */
            background-color: #ffffff; 
        }
        .btn-action { 
            margin-left: 0.5rem; 
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }
        .header-section {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .text-merah-aksi { color: #dc3545 !important; }
    </style>
</head>
<body>

<div class="header-section">
    <div class="container">
        <header class="d-flex justify-content-between align-items-center">
            <h1 class="display-6 fw-bold text-dark mb-0">
                <i class="bi bi-inboxes-fill me-3 text-merah-aksi"></i>Kotak Masuk Kontak
            </h1>
            <p id="auth-status" class="small mt-2 text-danger mb-0">Memuat otentikasi...</p>
        </header>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-white border-bottom-0 p-4">
                    <h5 class="mb-0 fw-bold text-dark">
                        Daftar Pesan Masuk 
                        <span class="badge bg-danger rounded-pill align-middle" id="unread-count">0</span>
                    </h5>
                    <small class="text-muted">Total pesan yang belum direspons atau ditandai selesai.</small>
                </div>
                <div class="card-body p-4">
                    <div id="messages-list" class="space-y-3">
                        <div id="loading-messages" class="text-center p-5">
                             <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Memuat...</span>
                            </div>
                            <p class="mt-3 text-muted">Memuat pesan real-time...</p>
                        </div>
                        <p id="no-messages" class="text-center text-muted p-5 hidden fw-semibold">
                            <i class="bi bi-check-circle-fill me-2 text-success"></i> Semua pesan sudah diurus. Kotak masuk kosong!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modul Firebase -->
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
    import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
    import { getFirestore, doc, onSnapshot, collection, updateDoc, deleteDoc } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";
    import { setLogLevel } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    // Mengatur log level untuk debugging Firestore
    setLogLevel('Debug');

    // --- Konfigurasi dan Inisialisasi Firebase ---
    const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
    // Menggunakan try-catch untuk parsing JSON
    let firebaseConfig = {};
    try {
        firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
    } catch (e) {
        console.error("Kesalahan parsing konfigurasi Firebase:", e);
    }

    const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

    let app, db, auth;
    let userId = null;
    let isAuthReady = false;

    // Inisialisasi Firebase
    try {
        if (Object.keys(firebaseConfig).length > 0) {
            app = initializeApp(firebaseConfig);
            db = getFirestore(app);
            auth = getAuth(app);
        } else {
            console.error("Firebase Config tidak ditemukan atau kosong.");
            document.getElementById('auth-status').textContent = 'Kesalahan: Firebase Config Kosong.';
            // Hentikan eksekusi jika config kosong
            throw new Error("Missing Firebase Config");
        }
    } catch (error) {
        console.error("Kesalahan inisialisasi Firebase:", error);
        document.getElementById('auth-status').textContent = 'Kesalahan Inisialisasi Firebase.';
    }

    // --- Proses Otentikasi Admin (Anonim) ---
    onAuthStateChanged(auth, async (user) => {
        if (user) {
            userId = user.uid;
            document.getElementById('auth-status').textContent = `Admin ID: ${userId}`;
        } else {
            // Mencoba sign in anonim jika belum ada pengguna
            try {
                if (initialAuthToken) {
                    await signInWithCustomToken(auth, initialAuthToken);
                } else {
                    await signInAnonymously(auth);
                }
            } catch (error) {
                console.error("Kesalahan otentikasi:", error);
                document.getElementById('auth-status').textContent = 'Gagal melakukan otentikasi.';
            }
        }
        isAuthReady = true;
        setupMessageListener();
    });

    // --- Logika Dashboard Admin (Real-Time Listener) ---
    function setupMessageListener() {
        if (!isAuthReady || !db || !auth.currentUser) {
            // Guard untuk memastikan otentikasi selesai
            console.warn("Menunggu otentikasi selesai sebelum memuat listener.");
            return;
        }

        const messagesList = document.getElementById('messages-list');
        const loadingMessages = document.getElementById('loading-messages');
        const noMessages = document.getElementById('no-messages');
        const unreadCount = document.getElementById('unread-count');

        // Path koleksi publik: /artifacts/{appId}/public/data/messages
        const messagesCol = collection(db, `artifacts/${appId}/public/data/messages`);

        // onSnapshot untuk mendengarkan perubahan secara real-time
        onSnapshot(messagesCol, (snapshot) => {
            loadingMessages.classList.add('hidden');
            messagesList.innerHTML = ''; // Kosongkan daftar

            if (snapshot.empty) {
                noMessages.classList.remove('hidden');
                unreadCount.textContent = '0';
                return;
            }
            noMessages.classList.add('hidden');

            // Konversi data dan urutkan
            const messages = [];
            let countUnread = 0;

            snapshot.forEach(doc => {
                const msg = { id: doc.id, ...doc.data() };
                messages.push(msg);
                if (!msg.isRead) {
                    countUnread++;
                }
            });

            // Urutkan berdasarkan timestamp, terbaru di atas
            messages.sort((a, b) => (b.timestamp?.seconds || 0) - (a.timestamp?.seconds || 0));

            // Perbarui jumlah pesan belum dibaca
            unreadCount.textContent = countUnread.toString();

            messages.forEach(msg => {
                const messageElement = createMessageElement(msg);
                messagesList.appendChild(messageElement);
            });

        }, (error) => {
            console.error("Gagal mendengarkan perubahan pesan:", error);
            loadingMessages.innerHTML = `<i class="bi bi-x-circle-fill me-2 text-danger"></i>Gagal memuat pesan. Cek konsol untuk detail.`;
            loadingMessages.classList.remove('hidden');
        });
    }

    // Fungsi untuk membuat elemen pesan di dashboard
    function createMessageElement(msg) {
        const timeString = msg.timestamp ? new Date(msg.timestamp.seconds * 1000).toLocaleString('id-ID', {
            year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        }) : 'Tidak diketahui';
        const isRead = msg.isRead || false;
        
        const div = document.createElement('div');
        div.className = `message-card card shadow-sm mb-3 p-3 ${isRead ? 'read' : 'unread'}`;
        div.innerHTML = `
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="fw-bold text-dark-text mb-0">${msg.name || 'Anonim'}</h5>
                        <small class="text-muted"><i class="bi bi-envelope me-1"></i> ${msg.email}</small>
                    </div>
                    <small class="text-muted text-end">
                        <i class="bi bi-clock me-1"></i> ${timeString}
                    </small>
                </div>
                
                <div class="py-2 mb-2">
                    <p class="card-text text-body">${msg.message}</p>
                </div>

                <div class="mt-3 d-flex justify-content-end border-top pt-2">
                    <button data-id="${msg.id}" data-read="${isRead}" class="toggle-read-btn btn btn-action btn-sm fw-bold ${isRead ? 'btn-warning text-dark' : 'btn-success'}">
                        <i class="bi ${isRead ? 'bi-envelope-open' : 'bi-check-circle'} me-1"></i>
                        ${isRead ? 'Tandai Belum Dibaca' : 'Tandai Selesai'}
                    </button>
                    <button data-id="${msg.id}" class="delete-btn btn btn-action btn-sm btn-danger fw-bold">
                        <i class="bi bi-trash me-1"></i>Hapus Permanen
                    </button>
                </div>
            </div>
        `;

        // Tambahkan event listener untuk tombol aksi
        div.querySelector('.toggle-read-btn').addEventListener('click', toggleReadStatus);
        div.querySelector('.delete-btn').addEventListener('click', deleteMessage);

        return div;
    }

    // Fungsi untuk mengubah status baca/belum baca
    async function toggleReadStatus(e) {
        // Cari elemen tombol yang diklik
        let button = e.target.closest('button');
        if (!button) return;

        const id = button.dataset.id;
        const currentReadStatus = button.dataset.read === 'true';

        try {
            const messageRef = doc(db, `artifacts/${appId}/public/data/messages`, id);
            // Nonaktifkan tombol sementara
            button.disabled = true;

            await updateDoc(messageRef, {
                isRead: !currentReadStatus
            });
            // onSnapshot akan me-refresh tampilan secara otomatis
        } catch (error) {
            console.error("Gagal mengubah status baca:", error);
            // Di lingkungan produksi, gunakan modal/notifikasi kustom
            alert('Terjadi kesalahan saat mengubah status pesan. Silakan cek konsol.'); 
        } finally {
             button.disabled = false;
        }
    }

    // Fungsi untuk menghapus pesan
    async function deleteMessage(e) {
        // Cari elemen tombol yang diklik
        let button = e.target.closest('button');
        if (!button) return;

        const id = button.dataset.id;
        
        // Ganti konfirmasi ini dengan modal kustom di lingkungan produksi
        if (!confirm("PERINGATAN! Anda akan menghapus pesan ini secara permanen. Lanjutkan?")) {
            return;
        }

        try {
            const messageRef = doc(db, `artifacts/${appId}/public/data/messages`, id);
            button.disabled = true;
            await deleteDoc(messageRef);
        } catch (error) {
            console.error("Gagal menghapus pesan:", error);
            // Ganti alert dengan modal/notifikasi kustom
            alert('Terjadi kesalahan saat menghapus pesan. Silakan cek konsol.'); 
        } finally {
            button.disabled = false;
        }
    }
</script>
</body>
</html>