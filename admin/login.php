<?php
// BARIS KRUSIAL: Memulai sesi
session_start();

// Cek apakah admin sudah login
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Jika sudah login, alihkan langsung ke dashboard
    header('Location: admin_dashboard.php');
    exit;
}

// Ambil pesan error dari URL
$error_message = '';
if (isset($_GET['error']) && $_GET['error'] == 1) {
    $error_message = "Username atau Password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <!-- Memuat Tailwind CSS CDN untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <!-- Kontainer Kartu Login -->
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-2xl border border-gray-100">
        <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-6">
            Login Admin
        </h2>

        <!-- Tampilkan Pesan Error (jika ada) -->
        <?php if (!empty($error_message)): ?>
            <div role="alert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative mb-6">
                <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>

        <!-- Formulir Login diarahkan ke authenticate.php -->
        <form action="authenticate.php" method="POST" class="space-y-6">
            
            <!-- Bidang Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                    Username
                </label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out sm:text-sm"
                    placeholder="Masukkan Username"
                >
            </div>

            <!-- Bidang Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Password
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out sm:text-sm"
                    placeholder="Masukkan Password"
                >
            </div>
            
            <!-- Tombol Submit -->
            <button
                type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-lg text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300 ease-in-out transform hover:scale-[1.01]"
            >
                Log In
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Pastikan Anda memiliki hak akses Admin.
        </p>
    </div>

</body>
</html>