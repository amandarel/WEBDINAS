<?php
// =======================================================
// TEMPLATES/FOOTER.PHP - LAYOUT BAWAH & SCRIPT (Fokus Budaya)
// =======================================================
?>

<footer class="bg-dark-minahasa text-white pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">
            
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold text-merah-aksi mb-3">Dinas Kebudayaan dan Pariwisata</h5>
                <p>Kabupaten Minahasa, Sulawesi Utara</p>
                <p><i class="bi bi-geo-alt me-2"></i> 8W77+G96, Kembuan, Kec. Tondano Utara, Kabupaten Minahasa, Sulawesi Utara</p>
                <p><i class="bi bi-envelope me-2"></i> disbudpar.minahasa@email.go.id</p>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="fw-bold text-merah-aksi mb-3">Link Cepat</h5>
                <ul class="list-unstyled">
                    <!-- FIX: Tambahkan text-decoration-none untuk menghilangkan garis bawah -->
                    <li><a href="index.php?page=home" class="text-white text-decoration-none">Beranda</a></li>
                    <li><a href="index.php?page=tradisi" class="text-white text-decoration-none">Adat & Tradisi</a></li>
                    <li><a href="index.php?page=kontak" class="text-white text-decoration-none">Kontak</a></li>
                    <li><a href="index.php?page=tentang" class="text-white text-decoration-none">Tentang Kami</a></li>
                </ul>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="fw-bold text-merah-aksi mb-3">Ikuti Kami</h5>
                
                <!-- FIX: Tambahkan text-decoration-none ke ikon media sosial juga -->
                <a href="https://www.facebook.com/disbudpar.kabupaten.minahasa?mibextid=ZbWKwL" class="text-white me-3 social-icon text-decoration-none" target="_blank" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-1.723c0-2.005 1.194-3.277 3.881-3.277h2.119v3z"/></svg>
                </a>
                
                <a href="https://www.instagram.com/disbudpar_minahasa?igsh=MWNjMzFva3ppZjc4Yg==" class="text-white me-3 social-icon text-decoration-none" target="_blank" aria-label="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.068 1.645.068 4.85s-.012 3.584-.068 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.068-1.644-.068-4.85s.012-3.584.068-4.85c.148-3.227 1.664-4.77 4.919-4.919 1.266-.058 1.644-.07 4.85-.07zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.981 6.981-.059 1.281-.073 1.689-.073 4.948s.014 3.668.072 4.948c.2 4.358 2.618 6.78 6.981 6.981 1.281.058 1.689.072 4.948.072s3.668-.014 4.948-.072c4.354-.2 6.782-2.618 6.98-6.981.059-1.28.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.196-4.354-2.617-6.78-6.981-6.981-1.29-.058-1.697-.072-4.956-.072zM12 6.587c-3.313 0-6 2.687-6 6s2.687 6 6 6 6-2.687 6-6-2.687-6-6-6zm0 10c-2.209 0-4-1.791-4-4s1.791-4 4-4 4 1.791 4 4-1.791 4-4 4zm6.406-11.845c-.796 0-1.444.647-1.444 1.443s.647 1.443 1.444 1.443 1.444-.647 1.444-1.443-.647-1.443-1.444-1.443z"/></svg>
                </a>
                
                <a href="https://www.tiktok.com/@disbudpar.minahasa?_t=ZS-90mSyoz8i4O&_r=1" class="text-white me-3 social-icon text-decoration-none" target="_blank" aria-label="TikTok">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 2.002c-.06 0-.12 0-.18.002 9.382 0 11.968 1.483 11.968 10.952 0 10.469-2.586 11.952-11.968 11.952-1.053 0-2.08-.081-3.054-.241v-5.26c.974.16 2.001.241 3.054.241 6.027 0 6.027-4.944 6.027-5.759 0-2.188-3.978-2.646-6.027-2.646-.974 0-2.001.081-3.054.241v-5.26c.974.16 2.001.241 3.054.241z"/></svg>
                </a>

            </div>

        </div>
        
        <hr class="bg-secondary">
        
        <div class="text-center pt-2">
            <p class="small mb-0">&copy; <?php echo date("Y"); ?> Dinas Kebudayaan dan Pariwisata Kabupaten Minahasa. Dibuat Oleh Kia.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script src="assets/js/main.js"></script>

</body>
</html>