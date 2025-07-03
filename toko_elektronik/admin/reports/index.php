<?php
// admin/reports/index.php

session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit;
}

include_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../../includes/sidebar.php'; ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Laporan Analitik</h1>
            </div>

            <p>Pilih jenis laporan yang ingin Anda lihat:</p>

            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-line"></i> Laporan Penjualan Produk</h5>
                            <p class="card-text">Lihat performa penjualan produk secara detail.</p>
                            <a href="product_sales_report.php" class="btn btn-primary btn-sm">Lihat Laporan</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-boxes"></i> Laporan Stok Produk</h5>
                            <p class="card-text">Pantau ketersediaan stok produk.</p>
                            <a href="stock_report.php" class="btn btn-primary btn-sm">Lihat Laporan</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-receipt"></i> Laporan Pesanan</h5>
                            <p class="card-text">Detail semua pesanan masuk dan statusnya.</p>
                            <a href="order_report.php" class="btn btn-primary btn-sm">Lihat Laporan</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-users"></i> Laporan Pengguna</h5>
                            <p class="card-text">Daftar pengguna terdaftar (admin & customer).</p>
                            <a href="user_report.php" class="btn btn-primary btn-sm">Lihat Laporan</a>
                        </div>
                    </div>
                </div>

                </div>
        </main>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>