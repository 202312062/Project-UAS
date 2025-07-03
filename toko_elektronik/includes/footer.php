<?php
// includes/footer.php

// Tentukan path relatif ke folder assets/js berdasarkan lokasi file saat ini
$js_path = '';
if (str_contains($_SERVER['REQUEST_URI'], '/admin/')) {
    $js_path = '../../assets/js/script.js';
} else if (str_contains($_SERVER['REQUEST_URI'], '/pages/')) {
    $js_path = '../assets/js/script.js';
} else {
    $js_path = 'assets/js/script.js'; // Untuk root seperti index.php, login.php
}
?>

    <footer class="mt-auto">
        <div class="container text-center">
            <p>&copy; <?php echo date("Y"); ?> E-GadgetStore. All rights reserved.</p>
            <p>
                <a href="#">Kebijakan Privasi</a> | 
                <a href="#">Syarat & Ketentuan</a> | 
                <a href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/pages/')) ? 'contact.php' : 'pages/contact.php'; ?>">Kontak Kami</a>
            </p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        feather.replace(); // Inisialisasi Feather Icons
    </script>
    <script src="<?php echo $js_path; ?>"></script>
</body>
</html>