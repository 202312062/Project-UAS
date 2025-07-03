<?php
// admin/dashboard.php

// Mulai sesi
session_start();

// Sertakan logika otentikasi
require_once '../includes/auth.php'; // Pastikan path ini benar
require_once '../config/database.php'; // Sertakan koneksi database

// Periksa apakah pengguna sudah login dan memiliki peran 'admin'
if (!isLoggedIn() || !hasRole('admin')) {
    // Jika tidak, arahkan kembali ke halaman login
    header('Location: ../../login.php');
    exit;
}

// --- Ambil Data Statistik dari Database ---
$total_products = 0;
$new_orders_count = 0;
$registered_users = 0;
$low_stock_products = 0;

try {
    // Total Produk
    $stmt_products = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt_products->fetchColumn();

    // Pesanan Baru (misal: status 'pending')
    $stmt_new_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
    $new_orders_count = $stmt_new_orders->fetchColumn();

    // Pengguna Terdaftar (misal: role 'customer')
    $stmt_registered_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $registered_users = $stmt_registered_users->fetchColumn();

    // Stok Rendah (misal: stok <= 10)
    $stmt_low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 10");
    $low_stock_products = $stmt_low_stock->fetchColumn();

} catch (PDOException $e) {
    // Tangani error database, misalnya tampilkan pesan error
    // Untuk produksi, log error ini daripada menampilkannya langsung ke user
    echo '<div class="alert alert-danger" role="alert">Error mengambil statistik: ' . $e->getMessage() . '</div>';
}
// --- Akhir Pengambilan Data Statistik ---


// Sertakan header admin
// Asumsikan header.php berisi tag <head>, link CSS (termasuk Bootstrap), dan pembuka <body>
include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php
        // Sertakan sidebar navigasi admin
        include_once '../includes/sidebar.php';
        ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
            </div>

            <p>Selamat datang, **<?php echo htmlspecialchars($_SESSION['username']); ?>**! Anda masuk sebagai Administrator.</p>
            <p>Dari sini, Anda dapat mengelola berbagai aspek toko elektronik Anda.</p>

            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-users"></i> Manajemen Pengguna</h5>
                            <p class="card-text">Kelola akun admin dan pelanggan.</p>
                            <a href="users/index.php" class="btn btn-primary btn-sm">Lihat Pengguna</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-box-open"></i> Manajemen Produk</h5>
                            <p class="card-text">Tambah, edit, hapus produk elektronik.</p>
                            <a href="products/index.php" class="btn btn-primary btn-sm">Kelola Produk</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-tags"></i> Manajemen Kategori</h5>
                            <p class="card-text">Atur kategori produk (Smartphone, Laptop, dll.).</p>
                            <a href="categories/index.php" class="btn btn-primary btn-sm">Kelola Kategori</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-industry"></i> Manajemen Merek</h5>
                            <p class="card-text">Tambah, edit, hapus merek produk (Samsung, Apple, dll.).</p>
                            <a href="brands/index.php" class="btn btn-primary btn-sm">Kelola Merek</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-shopping-cart"></i> Manajemen Pesanan</h5>
                            <p class="card-text">Lihat dan perbarui status pesanan pelanggan.</p>
                            <a href="orders/index.php" class="btn btn-primary btn-sm">Lihat Pesanan</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-bar"></i> Laporan</h5>
                            <p class="card-text">Lihat laporan penjualan, stok, dan lainnya.</p>
                            <a href="reports/index.php" class="btn btn-primary btn-sm">Lihat Laporan</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-cogs"></i> Pengaturan Sistem</h5>
                            <p class="card-text">Konfigurasi pengaturan aplikasi.</p>
                            <a href="settings/index.php" class="btn btn-secondary btn-sm disabled">Segera Hadir</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-clipboard-list"></i> Log Aktivitas</h5>
                            <p class="card-text">Pantau aktivitas penting di sistem.</p>
                            <a href="activity_log/index.php" class="btn btn-secondary btn-sm disabled">Segera Hadir</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Statistik Cepat</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card text-white bg-info mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Total Produk</h5>
                                    <p class="card-text h3"><?php echo $total_products; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Pesanan Baru</h5>
                                    <p class="card-text h3"><?php echo $new_orders_count; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Pengguna Terdaftar</h5>
                                    <p class="card-text h3"><?php echo $registered_users; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-danger mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Stok Rendah</h5>
                                    <p class="card-text h3"><?php echo $low_stock_products; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php
// Sertakan footer
include_once '../includes/footer.php';
?>