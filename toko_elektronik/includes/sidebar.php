<?php
// includes/sidebar.php (untuk Admin)

// Path untuk ikon Font Awesome
// Fungsi untuk menentukan apakah tautan aktif
function isActive($current_page_uri, $target_segment) {
    return str_contains($current_page_uri, $target_segment) ? 'active' : '';
}

// Tentukan base path untuk link sidebar
$base_admin_path = '';
// Ini sedikit kompleks karena perlu menangani level folder.
// Contoh: dari admin/users/index.php ke admin/dashboard.php
// Jika URI mengandung '/admin/' diikuti oleh subfolder, maka pathnya adalah '../../admin/'
// Jika URI langsung di '/admin/' (misal dashboard.php), maka pathnya '../admin/'
if (str_contains($_SERVER['REQUEST_URI'], '/admin/') && count(explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'))) > 2) {
    $base_admin_path = '../../admin/';
} else {
    $base_admin_path = '../admin/';
}

?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'dashboard.php'); ?>" href="<?php echo $base_admin_path; ?>dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    Dasbor
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'users/'); ?>" href="<?php echo $base_admin_path; ?>users/index.php">
                    <i class="fas fa-fw fa-users"></i>
                    Manajemen Pengguna
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'products/'); ?>" href="<?php echo $base_admin_path; ?>products/index.php">
                    <i class="fas fa-fw fa-box-open"></i>
                    Manajemen Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'categories/'); ?>" href="<?php echo $base_admin_path; ?>categories/index.php">
                    <i class="fas fa-fw fa-tags"></i>
                    Manajemen Kategori
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'brands/'); ?>" href="<?php echo $base_admin_path; ?>brands/index.php">
                    <i class="fas fa-fw fa-industry"></i>
                    Manajemen Merek
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'orders/'); ?>" href="<?php echo $base_admin_path; ?>orders/index.php">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    Manajemen Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'customers/'); ?>" href="<?php echo $base_admin_path; ?>customers/index.php">
                    <i class="fas fa-fw fa-user-tie"></i>
                    Manajemen Pelanggan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'reports/'); ?>" href="<?php echo $base_admin_path; ?>reports/index.php">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    Laporan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'settings/'); ?>" href="<?php echo $base_admin_path; ?>settings/index.php">
                    <i class="fas fa-fw fa-cogs"></i>
                    Pengaturan Sistem
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isActive($_SERVER['REQUEST_URI'], 'activity_log/'); ?>" href="<?php echo $base_admin_path; ?>activity_log/index.php">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    Log Aktivitas
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Akun</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo (str_contains($_SERVER['REQUEST_URI'], '/admin/')) ? '../../logout.php' : '../logout.php'; ?>">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    Keluar
                </a>
            </li>
        </ul>
    </div>
</nav>